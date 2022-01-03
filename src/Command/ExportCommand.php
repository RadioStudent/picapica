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

// Spreadsheet lib!
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

use App\Entity\Album;
use App\Entity\ArtistRelation;

//class ExportCommand extends ContainerAwareCommand
class ExportCommand extends Command
{

    /**
     * @var OutputInterface
     */
    private $out;

    protected function configure()
    {
        $this->setName('picapica:export')
             ->setDescription('Export all album entries');
    }

    // @TODO parametriziramo path?
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->out = $output;

        $this->out->write("Loading albums...\n");
        $albums = $this->loadAlbums();

        $this->out->write("Writing spreadheet...\n");
        $path = 'data/picapica_albums_export.ods';
        $this->writeFile($albums, $path);

        $this->out->writeln("Export done. You can find it in $path");
    }

    private function loadAlbums()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $albumRepo = $em->getRepository('App:Album');
        $allBums = $albumRepo->findBy([], ['fid' => 'ASC']);

        $results = [];

        foreach ($allBums as $album) {
            $fid = $album->getFid();
            $cat = preg_split('/\s/', $fid)[0];

            $albumName = $album->getName();
            $artist = $album->getAlbumArtistName();
            $year = $album->getStrDate();

            if ($albumName && $artist) {
                if (!isset($results[$cat])) {
                    $results[$cat] = [];
                }
                
                $results[$cat][] = [
                    $fid,
                    $album->getName(),
                    $album->getAlbumArtistName(),
                    $album->getStrDate()
                ];
            }
        }

        return $results;
    }

    private function writeFile($albums, $path)
    {
        $writer = WriterEntityFactory::createODSWriter();
        $writer->openToFile($path);


        foreach ($albums as $cat => $albums) {
            $writer->getCurrentSheet()->setName($cat);
                $this->out->write("Writing sheet $cat (" . count($albums) . ")...\n");
            foreach ($albums as $album) {
                $row = WriterEntityFactory::createRowFromArray($album);
                $writer->addRow($row);
            }
            $writer->addNewSheetAndMakeItCurrent();
        }

        $writer->close();
    }
}
