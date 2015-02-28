<?php

namespace RadioStudent\AppBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\Connection;
use RadioStudent\AppBundle\Entity\Album;
use RadioStudent\AppBundle\Entity\ArtistRelation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ImportCommand extends ContainerAwareCommand
{

    /**
     * @var OutputInterface
     */
    private $out;

    /** @var Connection */
    private $c;

    private $dbName;

    protected function configure()
    {
        $this
            ->setName('picapica:import')
            ->setDescription('Migrate the old database')
            ->addArgument('dumpFile', InputArgument::OPTIONAL, 'Path to the old database dump (.sql.gz)', 'app/data/FONOTEKA.sql.gz')
            ->addArgument('votefixFile', InputArgument::OPTIONAL, 'Path to the votefix dump (.sql)', 'app/data/fono_votefix_artists.sql')
            ->addOption('noinit', null, InputOption::VALUE_NONE, 'Don\'t init the database.')
            ->addOption('only',   null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '')
            ->addOption('except', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dbName = $this->getContainer()->getParameter('database_name');

        $this->out = $output;

        $this->c = $this->getContainer()->get('doctrine.dbal.db2_connection');

        if (!$input->getOption('noinit')) {
            $this->prepareDb($input->getArgument('dumpFile'));
        }

        if (!in_array('artists', $input->getOption('except')) &&
            (in_array('artists', $input->getOption('only')) || !$input->getOption('only'))) {
            $this->importArtists($input->getArgument('votefixFile'));
        }

        if (!in_array('albums', $input->getOption('except')) &&
            (in_array('albums', $input->getOption('only')) || !$input->getOption('only'))) {
            $this->importAlbums();
        }

        if (!in_array('tracks', $input->getOption('except')) &&
            (in_array('tracks', $input->getOption('only')) || !$input->getOption('only'))) {
            $this->importTracks();
        }
    }

    private function prepareDb($dumpFile)
    {

        $this->out->write('Import old database...');

        $this->c->getSchemaManager()->dropAndCreateDatabase('fonoteka_old');
        $this->c->exec("USE fonoteka_old");

        $p = new Process('zcat '.$dumpFile.' | mysql -u root --password=root fonoteka_old');
        $p->setTimeout(0);
        $p->run();
        $this->out->writeln('OK');

        $this->out->write('Add import fields...');
        $this->c->exec("ALTER TABLE FONO_ALL
            ADD COLUMN IMPORT_ALBUM_ID INT(10) NULL AFTER MEDIA,
            ADD COLUMN IMPORT_ARTIST_ID INT(10) NULL AFTER IMPORT_ALBUM_ID,
            ADD COLUMN IMPORT_TRACK_ID INT(10) NULL AFTER IMPORT_ARTIST_ID,
            ADD COLUMN IMPORT_LABEL_ID INT(10) NULL AFTER IMPORT_TRACK_ID,
            ADD COLUMN IMPORT_ALBUM_FID VARCHAR(30) NULL AFTER IMPORT_LABEL_ID,
            ADD COLUMN IMPORT_TRACK_NO VARCHAR(30) NULL AFTER IMPORT_ALBUM_FID,
            ADD COLUMN IMPORT_TRACK_FID VARCHAR(30) NULL AFTER IMPORT_TRACK_NO,
            ADD INDEX IMPORT (IMPORT_ARTIST_ID, IMPORT_ALBUM_ID, IMPORT_TRACK_ID, IMPORT_LABEL_ID, IMPORT_ALBUM_FID, IMPORT_TRACK_NO, IMPORT_TRACK_FID)");
        $this->out->writeln('OK');

        $this->out->write('Fix encoding (1/2)...');
        $this->c->exec("ALTER TABLE FONO_ALL CONVERT TO CHARSET utf8 COLLATE utf8_unicode_ci");
        $this->out->writeln('OK');


        $this->out->write('Fix encoding (2/2)...');
        $replace = array(
            "È" => "Č",
            "Æ" => "Ć",
        );
        $fields = array("IZVAJALEC", "NASLOV", "ALBUM", "ZALOZBA");
        $fix = array();
        foreach ($fields as $fi=>$fv) {
            $s = $fv;
            foreach ($replace as $ri=>$rv) {
                $s = "REPLACE($s,'$ri','$rv')";
            }
            $fix[] = "$fv=$s";
        }
        $this->c->exec("UPDATE FONO_ALL SET " . implode(',', $fix));
        $this->out->writeln('OK');

        $this->out->write("Extract album FID...");
        $this->c->exec("UPDATE FONO_ALL
          SET IMPORT_ALBUM_FID=
            TRIM(
              IF(
                LOCATE('-', STEVILKA)>0, SUBSTRING_INDEX(STEVILKA,'-',1),
                SUBSTRING_INDEX(STEVILKA,'/',1)
              )
            )"
        );
        $this->out->writeln("OK");

        $this->out->write("Extract track numbers...");
        $this->c->exec("UPDATE FONO_ALL
          SET IMPORT_TRACK_NO=
            TRIM(
              IF(
                LOCATE('-', STEVILKA)>0, SUBSTR(STEVILKA,LOCATE('-', STEVILKA)+1),
                CONCAT('A/', SUBSTR(STEVILKA,LOCATE('/', STEVILKA)+1))
              )
            )"
        );
        $this->out->writeln("OK");
    }

    private function importArtists($votefixFile)
    {
        $this->out->write("Import artists (1/2)...");
        $this->c->exec("INSERT IGNORE INTO $this->dbName.data_artists (NAME) SELECT DISTINCT IZVAJALEC FROM fonoteka_old.FONO_ALL");
        $this->out->writeln("OK");

        $this->out->write("Import artists (2/2)...");
        $this->c->exec("UPDATE fonoteka_old.FONO_ALL INNER JOIN $this->dbName.data_artists ON IZVAJALEC=NAME SET IMPORT_ARTIST_ID=ID");
        $this->out->writeln("OK");

        $this->out->write("Import artists votefix...");
        $p = new Process('cat '.$votefixFile.' | mysql -u root --password=root fonoteka_old');
        $p->setTimeout(0);
        $p->run();
        $this->out->writeln("OK");

        $this->out->write("Apply artists votefix...");
        $this->c->exec("USE fonoteka_old");
        $this->c->exec("INSERT INTO picapica.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
            SELECT DISTINCT
              n1.IMPORT_ARTIST_ID,
              n2.IMPORT_ARTIST_ID,
              '".ArtistRelation::TYPE_CORRECTED."'
            FROM
              fonoteka_old.fono_votefix_artists AS fix
              INNER JOIN FONO_ALL AS n1 on fix.NAME1=n1.IZVAJALEC
              INNER JOIN FONO_ALL AS n2 on fix.NAME2=n2.IZVAJALEC
            WHERE CHOICE=1;"
        );
        $this->c->exec("INSERT INTO picapica.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
            SELECT DISTINCT
              n2.IMPORT_ARTIST_ID,
              n1.IMPORT_ARTIST_ID,
              '".ArtistRelation::TYPE_CORRECTED."'
            FROM
              fonoteka_old.fono_votefix_artists AS fix
              INNER JOIN FONO_ALL AS n1 on fix.NAME1=n1.IZVAJALEC
              INNER JOIN FONO_ALL AS n2 on fix.NAME2=n2.IZVAJALEC
            WHERE CHOICE=0;"
        );
        $this->c->exec("INSERT INTO picapica.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
            SELECT DISTINCT
              n1.IMPORT_ARTIST_ID,
              n2.IMPORT_ARTIST_ID,
              '".ArtistRelation::TYPE_BOTH_CORRECT."'
            FROM
              fonoteka_old.fono_votefix_artists AS fix
              INNER JOIN FONO_ALL AS n1 on fix.NAME1=n1.IZVAJALEC
              INNER JOIN FONO_ALL AS n2 on fix.NAME2=n2.IZVAJALEC
            WHERE CHOICE=3 OR LEVENSHTEIN=0"
        );
        $this->c->exec("INSERT INTO picapica.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
            SELECT DISTINCT
              n1.IMPORT_ARTIST_ID,
              n2.IMPORT_ARTIST_ID,
              '".ArtistRelation::TYPE_DIFFERENT."'
            FROM
              fonoteka_old.fono_votefix_artists AS fix
              INNER JOIN FONO_ALL AS n1 on fix.NAME1=n1.IZVAJALEC
              INNER JOIN FONO_ALL AS n2 on fix.NAME2=n2.IZVAJALEC
            WHERE CHOICE=2;"
        );
        $this->out->writeln("OK");
    }

    private function importAlbums()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $artistRepo = $em->getRepository('RadioStudentAppBundle:Artist');

        $this->out->write("Import albums (1/2)...");
        $this->c->exec("SET SESSION group_concat_max_len = 1000000");

        //TODO: album date needs more love
        $q = $this->c->query("SELECT COALESCE(NULLIF(ALBUM, ''), IMPORT_ALBUM_FID) AS NAME, IF(LETNIK REGEXP '^[0-9]{4}$', MAKEDATE(LETNIK, 1), NULL) AS DATE, LETNIK AS STR_DATE, IMPORT_ALBUM_FID AS FID, GROUP_CONCAT(STEVILKA SEPARATOR '\',\'') AS TRACKS, GROUP_CONCAT(DISTINCT IMPORT_ARTIST_ID) AS ARTISTS FROM fonoteka_old.FONO_ALL GROUP BY COALESCE(NULLIF(ALBUM, ''), IMPORT_ALBUM_FID), IMPORT_ALBUM_FID");

        $res = $q->fetchAll();

        foreach ($res as $i=>$v) {
            $album = new Album();
            $album->setName($v['NAME']);
            $album->setDate($v['DATE'] == null?null:new \DateTime($v['DATE']));
            $album->setStrDate($v['STR_DATE']);
            $album->setFid($v['FID']);

            $artists = new ArrayCollection($artistRepo->findBy(['id' => explode(',', $v['ARTISTS'])]));
            $album->setArtists($artists);

            $em->persist($album);

            $res[$i]['album'] = $album;
        }
        $em->flush();
        $this->out->writeln("OK");

        $this->out->write("Import albums (2/2)...");
        reset($res);
        foreach ($res as $i=>$v) {
            $this->c->exec("UPDATE fonoteka_old.FONO_ALL SET IMPORT_ALBUM_ID=".$v['album']->getId()." WHERE STEVILKA IN ('".$v['TRACKS']."')");
        }
        $this->out->writeln("OK");
    }

    private function importTracks()
    {
        $this->out->write("Import tracks (1/2)...");

        $this->c->exec("ALTER TABLE $this->dbName.data_tracks ADD COLUMN OLD_FID VARCHAR(30) NOT NULL");
        $this->c->exec("ALTER TABLE $this->dbName.data_tracks ADD INDEX IDX_TMP_OLD_FID (OLD_FID)");

        $this->c->exec("INSERT INTO $this->dbName.data_tracks
        (FID, OLD_FID, TRACK_NUM, NAME, ARTIST_ID, DATE, STR_DATE, DURATION, GENRES, LANGUAGES, ALBUM_ID)
        SELECT
        CONCAT(IMPORT_ALBUM_FID, '-', IMPORT_TRACK_NO),
        STEVILKA,
        IMPORT_TRACK_NO,
        NASLOV,
        IMPORT_ARTIST_ID,
        IF(LETNIK REGEXP '^[0-9]{4}$', MAKEDATE(LETNIK, 1), NULL),
        LETNIK,
        IF(MINUTAZA > 0, MINUTAZA/1000, NULL),
        IF(ZVRST <> '', ZVRST, NULL),
        IF(JEZIK <> '', JEZIK, NULL),
        IMPORT_ALBUM_ID
        FROM fonoteka_old.FONO_ALL");

        $this->out->writeln("OK");

        $this->out->write("Import tracks (2/2)...");

        $this->c->exec("UPDATE fonoteka_old.FONO_ALL
        LEFT JOIN $this->dbName.data_tracks ON FONO_ALL.STEVILKA=data_tracks.OLD_FID
        SET FONO_ALL.IMPORT_TRACK_ID=data_tracks.ID, FONO_ALL.IMPORT_TRACK_FID=data_tracks.FID");

        $this->c->exec("ALTER TABLE $this->dbName.data_tracks DROP INDEX IDX_TMP_OLD_FID");
        $this->c->exec("ALTER TABLE $this->dbName.data_tracks DROP COLUMN OLD_FID");

        $this->out->writeln("OK");

    }
}
