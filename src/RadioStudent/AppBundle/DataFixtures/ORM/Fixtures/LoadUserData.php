<?php

namespace RadioStudent\AppBundle\DataFixtures\ORM\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RadioStudent\AppBundle\Entity\Tracklist;
use RadioStudent\AppBundle\Entity\TracklistTrack;
use Symfony\Component\DependencyInjection\Container;

class LoadUserData implements FixtureInterface {

    /**
     * @var Container
     */
    var $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $tracklist = new Tracklist();
        $tracklist->setName("Oprema 1");
        $tracklist->setTerm($manager->getRepository("RadioStudentAppBundle:Term")->find(1));
        $tracklist->setDate(new \DateTime("2015-09-02 07:00:00"));
        $tracklist->setAuthor($manager->getRepository("RadioStudentAppBundle:Author")->find(2));

        for ($i = 0; $i < 20; $i++) {
            $track = $manager->getRepository("RadioStudentAppBundle:Track")->findRandom();
            $tracklistTrack = new TracklistTrack();
            $tracklistTrack->setTrack($track);
            $tracklistTrack->setTrackNum($i);
            $tracklistTrack->setTracklist($tracklist);

            $manager->persist($tracklistTrack);
        }

        $manager->persist($tracklist);

        $manager->flush();
    }
}