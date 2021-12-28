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

class CleanupCommand extends ContainerAwareCommand
{

    /**
     * @var OutputInterface
     */
    private $out;

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

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $albumRepo = $em->getRepository('RadioStudentAppBundle:Album');
        $artistRepo = $em->getRepository('RadioStudentAppBundle:Artist');

        $qb = $em->createQueryBuilder();
        $q = $qb->select('a')
           ->from('RadioStudentAppBundle:Album', 'a')
           ->leftJoin('a.tracks', 't')
           ->groupBy('a')
           ->having('COUNT(t) = 0')
            ->getQuery();

        $this->out->write("Cleaning up empty albums:");
        foreach ($q->getResult() as $album) {
            $this->out->write($album->getName() . "\n");
        }

        $this->out->write("\n\nCleaning up empty artists:\n");
        $qb = $em->createQueryBuilder();
        $q = $qb->select('a')
           ->from('RadioStudentAppBundle:Artist', 'a')
           ->leftJoin('a.albums', 't')
           ->groupBy('a')
           ->having('COUNT(t) = 0')
           ->getQuery();

        foreach ($q->getResult() as $artist) {
            $this->out->write($artist->getName() . "\n");

            //foreach ($artist->getArtistRelations() as $artistRelation) {
                    //$em->remove($artistRelation);
                    //}
            $tq = $em->createQueryBuilder()->select('t')
                ->from('RadioStudentAppBundle:Track', 't')
                ->where('t.artist = :artist')
                ->setParameter('artist', $artist)
                ->getQuery();

            if (!empty($tq->getResult())) {
                continue;
            }

            $rq = $em->createQueryBuilder()->select('r')
                ->from('RadioStudentAppBundle:ArtistRelation', 'r')
                ->where('r.artist = :artist')
                ->orWhere('r.relatedArtist = :artist')
                ->setParameter('artist', $artist)
                ->getQuery();

            foreach($rq->getResult() as $relation) {
                $em->remove($relation);
            }

            $em->remove($artist);
        }

        $em->flush();
    }

}
