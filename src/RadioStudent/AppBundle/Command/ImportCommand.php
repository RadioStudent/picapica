<?php

namespace RadioStudent\AppBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\Connection;
use RadioStudent\AppBundle\Entity\Album;
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
            ->setName('fonoteka2:import')
            ->setDescription('Migrate the old database')
            ->addArgument('dumpfile', InputArgument::OPTIONAL, 'Path to the old database dump (.sql.gz)', 'FONOTEKA.sql.gz')
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
            $this->prepareDb($input->getArgument('dumpfile'));
        }

        if (!in_array('artists', $input->getOption('except')) &&
            (in_array('artists', $input->getOption('only')) || !$input->getOption('only'))) {
            $this->importArtists();
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

    private function prepareDb($dumpfile)
    {

        $this->out->write('Import old database...');

        $this->c->getSchemaManager()->dropAndCreateDatabase('fonoteka_old');
        $this->c->exec("USE fonoteka_old");

        $p = new Process('zcat '.$dumpfile.' | mysql -u root --password=root fonoteka_old');
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

    private function importArtists()
    {
        $this->out->write("Import artists (1/2)...");
        $this->c->exec("INSERT IGNORE INTO $this->dbName.data_artists (NAME) SELECT DISTINCT IZVAJALEC FROM fonoteka_old.FONO_ALL");
        $this->out->writeln("OK");

        $this->out->write("Import artists (2/2)...");
        $this->c->exec("UPDATE fonoteka_old.FONO_ALL INNER JOIN $this->dbName.data_artists ON IZVAJALEC=NAME SET IMPORT_ARTIST_ID=ID");
        $this->out->writeln("OK");

        /*
                 query("insert ignore into rel_artist2artist (ARTIST_ID1, ARTIST_ID2, TYPE)
                    select
                    n1.IMPORT_ARTIST_ID, n2.IMPORT_ARTIST_ID, fix.CHOICE
                    from

                    fono_votefix_artists as fix
                    left join fono_all as n1 on fix.NAME1=n1.IZVAJALEC
                    left join fono_all as n2 on fix.NAME2=n2.IZVAJALEC

                     where CHOICE IS NOT NULL;")
        ;*/
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

        $this->c->exec("INSERT INTO $this->dbName.data_tracks
        (FID, OLD_FID, TRACK_NUM, NAME, DATE, STR_DATE, DURATION, GENRES, LANGUAGES, ALBUM_ID)
        SELECT
        CONCAT(IMPORT_ALBUM_FID, '-', IMPORT_TRACK_NO),
        STEVILKA,
        IMPORT_TRACK_NO,
        NASLOV,
        IF(LETNIK REGEXP '^[0-9]{4}$', MAKEDATE(LETNIK, 1), NULL),
        LETNIK,
        IF(MINUTAZA > 0, SEC_TO_TIME(MINUTAZA/1000), NULL),
        IF(ZVRST <> '', ZVRST, NULL),
        IF(JEZIK <> '', JEZIK, NULL),
        IMPORT_ALBUM_ID
        FROM fonoteka_old.FONO_ALL");

        $this->out->writeln("OK");

        $this->out->write("Import tracks (2/2)...");

        $this->c->exec("ALTER TABLE $this->dbName.data_tracks ADD INDEX IDX_TMP_OLD_FID (OLD_FID)");

        $this->c->exec("UPDATE fonoteka_old.FONO_ALL
        LEFT JOIN $this->dbName.data_tracks ON FONO_ALL.STEVILKA=data_tracks.OLD_FID
        SET FONO_ALL.IMPORT_TRACK_ID=data_tracks.ID, FONO_ALL.IMPORT_TRACK_FID=data_tracks.FID");

        $this->c->exec("ALTER TABLE $this->dbName.data_tracks DROP INDEX IDX_TMP_OLD_FID");

        $this->out->writeln("OK");

    }
}
