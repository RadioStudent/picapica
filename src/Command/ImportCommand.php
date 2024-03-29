<?php

namespace App\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
//use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;

use \GetId3\GetId3Core as GetId3;

use App\Entity\Album;
use App\Entity\ArtistRelation;

//class ImportCommand extends ContainerAwareCommand
class ImportCommand extends Command
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

        if (!in_array('mp3s', $input->getOption('except')) &&
            (in_array('mp3s', $input->getOption('only')) || !$input->getOption('only'))) {
            $this->importMp3s();
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
            ADD COLUMN STEVILKA_FIXED VARCHAR(30) NOT NULL DEFAULT '' AFTER STEVILKA,
            ADD COLUMN IMPORT_ALBUM_ID INT(10) NULL AFTER MEDIA,
            ADD COLUMN IMPORT_ARTIST_ID INT(10) NULL AFTER IMPORT_ALBUM_ID,
            ADD COLUMN IMPORT_TRACK_ID INT(10) NULL AFTER IMPORT_ARTIST_ID,
            ADD COLUMN IMPORT_LABEL_ID INT(10) NULL AFTER IMPORT_TRACK_ID,
            ADD COLUMN IMPORT_ALBUM_FID VARCHAR(30) NULL AFTER IMPORT_LABEL_ID,
            ADD COLUMN IMPORT_TRACK_NO VARCHAR(30) NULL AFTER IMPORT_ALBUM_FID,
            ADD COLUMN IMPORT_TRACK_FID VARCHAR(30) NULL AFTER IMPORT_TRACK_NO,
            ADD INDEX IMPORT (IMPORT_ARTIST_ID, IMPORT_ALBUM_ID, IMPORT_TRACK_ID, IMPORT_LABEL_ID, IMPORT_ALBUM_FID, IMPORT_TRACK_NO, IMPORT_TRACK_FID),
            ADD INDEX STEVILKA_FIXED (STEVILKA_FIXED)");
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

        $this->out->write("Fix FID...");
        $this->c->exec(
        "UPDATE FONO_ALL set STEVILKA_FIXED=STEVILKA;

        /* CDJ 000054/32 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        CONCAT(
            SUBSTRING(STEVILKA_FIXED, 1, LENGTH(STEVILKA_FIXED) - 3),
            '-A/',
            SUBSTRING(STEVILKA_FIXED, -2)
        )
        WHERE LOWER(STEVILKA_FIXED) REGEXP '[0-9]{6}/[0-9]{2}$';

        /* CDYU 000599A/06 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        CONCAT(
            SUBSTRING(STEVILKA_FIXED, 1, LENGTH(STEVILKA_FIXED) - 4),
            '-',
            SUBSTRING(STEVILKA_FIXED, -4)
        )
        WHERE LOWER(STEVILKA_FIXED) REGEXP '[0-9]{6}[a-z]/[0-9]{2}$';

        /* CDWR000543-A/02 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        IF (LOWER(STEVILKA_FIXED) REGEXP '^[a-zš]{4}[0-9]*', CONCAT(MID(STEVILKA_FIXED,1,4), ' ', REPLACE(MID(STEVILKA_FIXED,5), ' ', '')),
            IF (LOWER(STEVILKA_FIXED) REGEXP '^[a-zš]{3}[0-9]*', CONCAT(MID(STEVILKA_FIXED,1,3), ' ', REPLACE(MID(STEVILKA_FIXED,4), ' ', '')),
                IF (LOWER(STEVILKA_FIXED) REGEXP '^[a-zš]{2}[0-9]*', CONCAT(MID(STEVILKA_FIXED,1,2), ' ', REPLACE(MID(STEVILKA_FIXED,3), ' ', '')),
                    IF (LOWER(STEVILKA_FIXED) REGEXP '^[a-zš]{1}[0-9]*', CONCAT(MID(STEVILKA_FIXED,1,1), ' ', REPLACE(MID(STEVILKA_FIXED,2), ' ', '')),
                        CONCAT('!', STEVILKA_FIXED)
                    )
                )
            )
        )
        WHERE LOWER(STEVILKA_FIXED) REGEXP '^[a-zš]*[0-9]';

        /* LP 000563A-D/02 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        CONCAT(
            SUBSTRING(STEVILKA_FIXED, 1, LENGTH(STEVILKA_FIXED) - 6),
            SUBSTRING(STEVILKA_FIXED, -5)
        )
        WHERE LOWER(STEVILKA_FIXED) REGEXP '[0-9]*[a-z]-[a-z]/[0-9]*$';

        /* CD  005387-A/08 */
        UPDATE FONO_ALL SET STEVILKA_FIXED = REPLACE(STEVILKA_FIXED, '  ', ' ');

        /* CDWR 001066-A/ 06 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        CONCAT(
            SUBSTRING(STEVILKA_FIXED, 1, POSITION(' ' IN STEVILKA_FIXED)),
            REPLACE(SUBSTRING(STEVILKA_FIXED, POSITION(' ' IN STEVILKA_FIXED)), ' ', '')
        )
        WHERE LOWER(STEVILKA_FIXED) REGEXP '^[a-zš]+ [^ ]* ';

        /* CD 005857-A02 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        CONCAT(
            SUBSTRING(STEVILKA_FIXED, 1, LENGTH(STEVILKA_FIXED)-2),
            '/',
            SUBSTRING(STEVILKA_FIXED, -2)
        )
        WHERE LOWER(STEVILKA_FIXED) REGEXP '[a-z][0-9]{2}$';

        /* CD 5928-A/05 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        CONCAT(
            SUBSTRING(STEVILKA_FIXED, 1, POSITION(' ' IN STEVILKA_FIXED)),
            IF (LOWER(STEVILKA_FIXED) REGEXP '[a-z]* [0-9]{5}[^0-9]', CONCAT('0', SUBSTRING(STEVILKA_FIXED, POSITION(' ' IN STEVILKA_FIXED)+1)),
                IF (LOWER(STEVILKA_FIXED) REGEXP '[a-z]* [0-9]{4}[^0-9]', CONCAT('00', SUBSTRING(STEVILKA_FIXED, POSITION(' ' IN STEVILKA_FIXED)+1)),
                    IF (LOWER(STEVILKA_FIXED) REGEXP '[a-z]* [0-9]{3}[^0-9]', CONCAT('000', SUBSTRING(STEVILKA_FIXED, POSITION(' ' IN STEVILKA_FIXED)+1)),
                        IF (LOWER(STEVILKA_FIXED) REGEXP '[a-z]* [0-9]{2}[^0-9]', CONCAT('0000', SUBSTRING(STEVILKA_FIXED, POSITION(' ' IN STEVILKA_FIXED)+1)),
                            IF (LOWER(STEVILKA_FIXED) REGEXP '[a-z]* [0-9]{7}[^0-9]', SUBSTRING(STEVILKA_FIXED, POSITION(' ' IN STEVILKA_FIXED)+2),
                                SUBSTRING(STEVILKA_FIXED, POSITION(' ' IN STEVILKA_FIXED)+1)
                            )
                        )
                    )
                )
            )
        )
        WHERE LOWER(STEVILKA_FIXED) NOT REGEXP '[a-z]* [0-9]{6}[^0-9]';

        /* CD 003853-A/O1 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        CONCAT(
            SUBSTRING(STEVILKA_FIXED, 1, POSITION(' ' IN STEVILKA_FIXED)-1),
            REPLACE(SUBSTRING(STEVILKA_FIXED, POSITION(' ' IN STEVILKA_FIXED)), 'O', '0')
        )
        WHERE LOWER(STEVILKA_FIXED) REGEXP '^[a-zš]+ [^o]*o';

        /* CDYU 001756-/09 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        REPLACE(STEVILKA_FIXED, '-/', '-A/')
        WHERE LOWER(STEVILKA_FIXED) REGEXP '[0-9]{6}-/[0-9]';

        /* CD 008895.A/01 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        REPLACE(REPLACE(STEVILKA_FIXED, '.', '-'), '--', '-')
        WHERE LOWER(STEVILKA_FIXED) REGEXP '[.]';

        /*  CD 008691-A/02 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        SUBSTRING(STEVILKA_FIXED, 2)
        WHERE LOWER(STEVILKA_FIXED) REGEXP '^ ';

        /* CDDE 000165-/A/02 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        REPLACE(STEVILKA_FIXED, '-/', '-')
        WHERE LOWER(STEVILKA_FIXED) REGEXP '-/[a-z]/[0-9]';

        /* CD 004699-A/4 */
        UPDATE FONO_ALL SET STEVILKA_FIXED =
        REPLACE(STEVILKA_FIXED, '/', '/0')
        WHERE LOWER(STEVILKA_FIXED) REGEXP '[a-z]/[0-9]$';
        ");
        $this->out->writeln("OK");

        $this->out->write("Extract album FID...");
        $this->c->exec("UPDATE FONO_ALL
          SET IMPORT_ALBUM_FID=
            TRIM(
              IF(
                LOCATE('-', STEVILKA_FIXED)>0, SUBSTRING_INDEX(STEVILKA_FIXED,'-',1),
                SUBSTRING_INDEX(STEVILKA_FIXED,'/',1)
              )
            )"
        );
        $this->out->writeln("OK");

        $this->out->write("Extract track numbers...");
        $this->c->exec("UPDATE FONO_ALL
          SET IMPORT_TRACK_NO=
            TRIM(
              IF(
                LOCATE('-', STEVILKA_FIXED)>0, SUBSTR(STEVILKA_FIXED,LOCATE('-', STEVILKA_FIXED)+1),
                CONCAT('A/', SUBSTR(STEVILKA_FIXED,LOCATE('/', STEVILKA_FIXED)+1))
              )
            )"
        );
        $this->out->writeln("OK");
    }

    private function importArtists($votefixFile)
    {
        $this->out->write("Import artists (1/2)...");
        $this->c->exec("INSERT IGNORE INTO $this->dbName.data_artists (NAME) SELECT DISTINCT IZVAJALEC collate utf8_bin FROM fonoteka_old.FONO_ALL");
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

        $this->c->exec("INSERT INTO $this->dbName.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
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
        $this->c->exec("INSERT INTO $this->dbName.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
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
        $this->c->exec("INSERT INTO $this->dbName.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
            SELECT DISTINCT
              n1.IMPORT_ARTIST_ID,
              n2.IMPORT_ARTIST_ID,
              '".ArtistRelation::TYPE_MISTAKE."'
            FROM
              fonoteka_old.fono_votefix_artists AS fix
              INNER JOIN FONO_ALL AS n1 on fix.NAME1=n1.IZVAJALEC
              INNER JOIN FONO_ALL AS n2 on fix.NAME2=n2.IZVAJALEC
            WHERE CHOICE=0;"
        );
        $this->c->exec("INSERT INTO $this->dbName.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
            SELECT DISTINCT
              n2.IMPORT_ARTIST_ID,
              n1.IMPORT_ARTIST_ID,
              '".ArtistRelation::TYPE_MISTAKE."'
            FROM
              fonoteka_old.fono_votefix_artists AS fix
              INNER JOIN FONO_ALL AS n1 on fix.NAME1=n1.IZVAJALEC
              INNER JOIN FONO_ALL AS n2 on fix.NAME2=n2.IZVAJALEC
            WHERE CHOICE=1;"
        );
        $this->c->exec("INSERT INTO $this->dbName.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
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
        $this->c->exec("INSERT INTO $this->dbName.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
            SELECT DISTINCT
              n2.IMPORT_ARTIST_ID,
              n1.IMPORT_ARTIST_ID,
              '".ArtistRelation::TYPE_BOTH_CORRECT."'
            FROM
              fonoteka_old.fono_votefix_artists AS fix
              INNER JOIN FONO_ALL AS n1 on fix.NAME1=n1.IZVAJALEC
              INNER JOIN FONO_ALL AS n2 on fix.NAME2=n2.IZVAJALEC
            WHERE CHOICE=3 OR LEVENSHTEIN=0"
        );
/*        $this->c->exec("INSERT INTO picapica.rel_artist2artist (ARTIST_ID, RELATED_ARTIST_ID, RELATION_TYPE)
            SELECT DISTINCT
              n1.IMPORT_ARTIST_ID,
              n2.IMPORT_ARTIST_ID,
              '".ArtistRelation::TYPE_DIFFERENT."'
            FROM
              fonoteka_old.fono_votefix_artists AS fix
              INNER JOIN FONO_ALL AS n1 on fix.NAME1=n1.IZVAJALEC
              INNER JOIN FONO_ALL AS n2 on fix.NAME2=n2.IZVAJALEC
            WHERE CHOICE=2;"
        );*/
        $this->out->writeln("OK");

        $this->out->write("Add UTF artist aliases...");
        $q = $this->c->query("SELECT da.ID AS ID1, db.ID AS ID2 FROM $this->dbName.data_artists da INNER JOIN $this->dbName.data_artists db ON da.NAME = db.NAME AND da.ID <> db.ID");
        $res = $q->fetchAll();
        $added = [];
        foreach ($res as $i => $v) {
            if (!in_array($v["ID1"], $added) || !in_array($v["ID2"], $added)) {
                $added[] = $id1 = $v["ID1"];
                $added[] = $id2 = $v["ID2"];

                $this->c->exec("INSERT INTO $this->dbName.rel_artist2artist (RELATION_TYPE, ARTIST_ID, RELATED_ARTIST_ID) VALUES ('" . ArtistRelation::TYPE_BOTH_CORRECT . "', $id1, $id2)");
            }
        }

        $this->out->writeln("OK");
    }

    private function importAlbums()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $artistRepo = $em->getRepository('App:Artist');

        $this->out->write("Import albums (1/2)...");
        $this->c->exec("SET SESSION group_concat_max_len = 1000000");

        $q = $this->c->query("
          SELECT
            COALESCE(NULLIF(ALBUM, ''), IMPORT_ALBUM_FID) AS NAME,
            LETNIK AS DATE,
            LETNIK AS STR_DATE,
            IMPORT_ALBUM_FID AS FID,
            GROUP_CONCAT(STEVILKA SEPARATOR '\',\'') AS TRACKS,
            GROUP_CONCAT(DISTINCT IMPORT_ARTIST_ID) AS ARTISTS
          FROM
            fonoteka_old.FONO_ALL
          GROUP BY
            COALESCE(NULLIF(ALBUM, ''), IMPORT_ALBUM_FID), IMPORT_ALBUM_FID");

        $res = $q->fetchAll();

        foreach ($res as $i=>$v) {
            preg_match_all("/\d{4}/", str_replace("O", 0, $v['DATE']), $matches);
            if (count($matches[0]) > 0) {
                $date = min($matches[0]);
            } else {
                $date = null;
            }

            $album = new Album();
            $album->setName($v['NAME']);
            $album->setDate($date == null?null:new \DateTime($date));
            $album->setStrDate($v['STR_DATE']);
            $album->setFid($v['FID']);

            $artists = new ArrayCollection($artistRepo->findBy(['id' => explode(',', $v['ARTISTS'])]));
            $album->setArtists($artists);

            if (count($artists) == 1) {
                $albumArtist = $artists[0]->getCorrectName();

            } else if (count($artists) == 2) {
                $albumArtist = $artists[0]->getCorrectName() . ' & ' . $artists[1]->getCorrectName();

            } else {
                $albumArtist = "V/A (" . count($artists) . ")";
            }
            $album->setAlbumArtistName($albumArtist);

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

    private function import_fs_to_db($path)
    {
        $files = $this->dir_crawl($path);
        $id3 = new GetId3();

        foreach ($files as $idx=>$file) {
            $allowed_extensions = ["mp3", "flac"];
            $e = new \SplFileInfo($file);
            $ext = $e->getExtension();
            if (!in_array(strtolower($ext), $allowed_extensions)) {
                continue;
            }
            $id3_data = $id3->analyze($file);
            if (isset($id3_data["tags"])) {
                $tags = $id3_data["tags"];
                $tags = "'".addslashes(json_encode($tags, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR))."'";
            } else {
                $tags = "NULL";
            }

            $duration = null;
            if (isset($id3_data["playtime_seconds"])) {
                $duration = round($id3_data["playtime_seconds"]);
            }

            $this->c->exec("INSERT INTO picapica.digital_import
                (PATH, TAGS, DURATION, MD5, FILECTIME, TRACK_ID, IMPORTED_TS)
                VALUES (
                  '".addslashes($file)."',
                  $tags,
                  $duration,
                  '".md5_file($file)."',
                  FROM_UNIXTIME(".filectime($file)."),
                  NULL,
                  NULL
                )");

            if ($idx % 10 == 0) echo round($idx/count($files)*100)."%\n";
        }

/*        CREATE TABLE `digital_import` (
        `ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`PATH` TEXT NULL COLLATE 'utf8_bin',
	`TAGS` TEXT NULL COLLATE 'utf8_bin',
	`DURATION` INT(11) UNSIGNED NULL,
	`MD5` TEXT NULL COLLATE 'utf8_bin',
	`FILECTIME` TIMESTAMP NULL,
	`TRACK_ID` INT(11) NULL,
	`IMPORTED_TS` TIMESTAMP NULL,
	PRIMARY KEY (`ID`),
	INDEX `PATH` (`PATH`(255)),
	INDEX `TAGS` (`TAGS`(255)),
	INDEX `MD5` (`TAGS`(32))
)
COLLATE='utf8_bin'
ENGINE=InnoDB
;*/

    }

    private function importMp3s()
    {
        $path = "app/data/mp3s";
        $path = "app/data/mp3s/! TUJINA !/Sylvain Chauveau & Ensemble Nocturne - Down to the Bone [Acoustic Tribute to Depeche Mode] (Ici d'ailleurs, 2005)";
        $path = "app/data/mp3s/! TUJINA !/Magic Fades - Push Thru (1080p, 2014)";
//        $path = "app/data/mp3s/! TUJINA !";
        $this->import_fs_to_db($path);
        return;

        $path = "app/data/mp3s";
        $path = "app/data/mp3s/! YUGO !/Ana Never - Small Years (Fluttery Records, 2012)/music";
        $path = "app/data/mp3s/! TUJINA !/Magic Fades - Push Thru (1080p, 2014)";
        $path = "app/data/mp3s/! TUJINA !/Sylvain Chauveau & Ensemble Nocturne - Down to the Bone [Acoustic Tribute to Depeche Mode] (Ici d'ailleurs, 2005)";
        $path = "app/data/mp3s/! TUJINA !/V.A. - An Anthology Of Noise & Electronic Music _ Seventh And Last A-Chronology Volume #7  (Sub Rosa, 2013)";
        $path = "app/data/mp3s/! TUJINA !";

        $files = $this->dir_crawl($path, ["! SINGLI !"]);

        $id3 = new GetId3();

/*        $extensions = [];
        foreach ($files as $file) {
            $e = new \SplFileInfo($file);
            $ext = $e->getExtension();
            if (!isset($extensions[$ext])) {
                $extensions[$ext] = 0;
            }
            $extensions[$ext]++;
        }
        print_r($extensions);*/

        $infos = [];
        foreach ($files as $file) {
            $allowed_extensions = ["mp3", "flac"];
            $e = new \SplFileInfo($file);
            $ext = $e->getExtension();
            if (!in_array(strtolower($ext), $allowed_extensions)) {
                continue;
            }
            $info = $this->extract_track_info($file, $id3);
            $info["path"] = $file;

            $infos[] = $info;
        }

        foreach ($infos as $info) {
            $this->c->exec("INSERT INTO digiteka.import
                (artist, album, title, label, year, track, file)
                VALUES (
                  '".addslashes($info["artist"])."',
                  '".addslashes($info["album"])."',
                  '".addslashes($info["title"])."',
                  '".addslashes($info["label"])."',
                  '".addslashes($info["year"])."',
                  '".addslashes($info["track"])."',
                  '".addslashes($info["path"])."'
                )");
        }

        /*CREATE TABLE `import` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`artist` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`album` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`title` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`label` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`year` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`track` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`file` TEXT NOT NULL COLLATE 'utf8_bin',
	PRIMARY KEY (`id`),
	INDEX `Index 1` (`artist`),
	INDEX `Index 2` (`album`),
	INDEX `Index 3` (`title`),
	INDEX `Index 4` (`label`),
	INDEX `Index 5` (`year`),
	INDEX `Index 6` (`track`),
	INDEX `Index 7` (`file`(255))
)
COLLATE='utf8_bin'
ENGINE=InnoDB
AUTO_INCREMENT=13
;
*/
    }

    private function extract_track_info($file, GetId3 $id3) {
        $info = [
            "artist" => [],
            "album" => [],
            "label" => [],
            "year" => [],
            "track" => [],
            "title" => [],
        ];

        preg_match("#.*\/(.*) - (.*) \((.*), (\d{4})\)\/(?:.*\/)*#", $file, $matches);
        if (count($matches) == 5) {
            $info["artist"]['fs'] = $matches[1];
            $info["album"]['fs'] = $matches[2];
            $info["label"]['fs'] = $matches[3];
            $info["year"]['fs'] = intval($matches[4]);
        }

        preg_match("#.*\/(.*) - (.*) \((.*), (\d{4})\)\/(?:.*\/)*(\d+) - (?:\g1|.*) - (.*)\.[^.]+#", $file, $matches);
        if (count($matches) == 7) {
            $info["artist"]['fs'] = $matches[1];
            $info["album"]['fs'] = $matches[2];
            $info["label"]['fs'] = $matches[3];
            $info["year"]['fs'] = intval($matches[4]);
            $info["track"]['fs'] = intval($matches[5]);
            $info["title"]['fs'] = $matches[6];
        }

        $tag = $id3->analyze($file);

        if (isset($tag["tags"])) {
            $tag = $tag["tags"];

            foreach ($tag as $idx=>$t) {
                if (isset($t["title"])) {
                    $info["title"][$idx] = $t["title"][0];
                }
                if (isset($t["artist"])) {
                    $info["artist"][$idx] = $t["artist"][0];
                }
                if (isset($t["album"])) {
                    $info["album"][$idx] = $t["album"][0];
                }
                if (isset($t["year"])) {
                    $info["year"][$idx] = intval($t["year"][0]);
                }
                if (isset($t["track"])) {
                    $info["track"][$idx] = intval($t["track"][0]);
                }
                if (isset($t["track_num"])) {
                    $info["track"][$idx] = intval($t["track_num"][0]);
                }
            }
        }

        return [
            "artist" => $this->get_best($info["artist"], ["id3v2", "id3v1", "fs"]),
            "album" => $this->get_best($info["album"], ["fs", "id3v2", "id3v1"]),
            "label" => $this->get_best($info["label"], ["fs", "id3v2", "id3v1"]),
            "year" => $this->get_best($info["year"], ["fs", "id3v2", "id3v1"]),
            "track" => $this->get_best($info["track"], ["id3v2", "id3v1", "fs"]),
            "title" => $this->get_best($info["title"], ["id3v2", "id3v1", "fs"]),
        ];
    }

    private function get_best($options, $priorities) {
        $options = $this->eliminate_similar($options);

        foreach ($priorities as $key) {
            if (isset($options[$key])) {
                return $options[$key];
            }
        }

        if (count($options) == 1) {
            return array_pop($options);
        } else {
            return "@@@" . json_encode($options);
        }
    }

    private function eliminate_similar($array) {
        $ret = [];
        foreach ($array as $o_k=>$o_v) {
            foreach ($array as $i_k=>$i_v) {
                if ($o_k === $i_k) continue;
                if ($i_v !== $o_v &&
                    (mb_strpos($i_v, $o_v) === 0 ||  // $o_v is shorter than $i_v but otherwise the same
                    (($p = mb_strpos($o_v, $i_v)) > 0 && (mb_strlen($o_v) - mb_strlen($i_v)) == $p*2))) {
                    continue 2;
                }
            }
            $ret[$o_k] = $o_v;
        }
        return array_unique($ret);
    }

    private function dir_crawl($path, $blacklist = []) {
        $items = scandir($path);
        $ret = [];
        foreach ($items as $i=>$v) {
            if (in_array($v, $blacklist)) continue;
            if (is_file("$path/$v")) {
                $ret[] = "$path/$v";
            } elseif ($v != "." && $v != "..") {
                $ret = array_merge($ret, $this->dir_crawl("$path/$v"));
            }
        }
        return $ret;
    }
}
