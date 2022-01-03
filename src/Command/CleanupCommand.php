<?php

namespace App\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
#use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Album;
use App\Entity\ArtistRelation;

//class CleanupCommand extends ContainerAwareCommand
class CleanupCommand extends Command
{

    /**
     * @var OutputInterface
     */
    private $out;

    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('picapica:cleanup')
            ->setDescription('Clean up album/artist entries')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->out = $output;

        $albumRepo = $this->em->getRepository('App:Album');
        $artistRepo = $this->em->getRepository('App:Artist');

        $qb = $this->em->createQueryBuilder();
        $q = $qb->select('a')
           ->from('App:Album', 'a')
           ->leftJoin('a.tracks', 't')
           ->groupBy('a')
           ->having('COUNT(t) = 0')
            ->getQuery();

        $this->out->write("Cleaning up empty albums:");
        foreach ($q->getResult() as $album) {
            $this->out->write($album->getName() . "\n");
        }

        $this->out->write("\n\nCleaning up empty artists:\n");
        $qb = $this->em->createQueryBuilder();
        $q = $qb->select('a')
           ->from('App:Artist', 'a')
           ->leftJoin('a.albums', 't')
           ->groupBy('a')
           ->having('COUNT(t) = 0')
           ->getQuery();

        foreach ($q->getResult() as $artist) {
            $this->out->write($artist->getName() . "\n");

            //foreach ($artist->getArtistRelations() as $artistRelation) {
                    //$this->em->remove($artistRelation);
                    //}
            $tq = $this->em->createQueryBuilder()->select('t')
                ->from('App:Track', 't')
                ->where('t.artist = :artist')
                ->setParameter('artist', $artist)
                ->getQuery();

            if (!empty($tq->getResult())) {
                continue;
            }

            $rq = $this->em->createQueryBuilder()->select('r')
                ->from('App:ArtistRelation', 'r')
                ->where('r.artist = :artist')
                ->orWhere('r.relatedArtist = :artist')
                ->setParameter('artist', $artist)
                ->getQuery();

            foreach($rq->getResult() as $relation) {
                $this->em->remove($relation);
            }

            $this->em->remove($artist);
        }

        $this->em->flush();
    }

}
