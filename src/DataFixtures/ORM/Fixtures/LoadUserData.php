<?php

namespace App\DataFixtures\ORM\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;

use App\Entity\Tracklist;
use App\Entity\TracklistTrack;

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
        $tracklist->setTerm($manager->getRepository("App:Term")->find(1));
        $tracklist->setDate(new \DateTime("2015-09-02 07:00:00"));
        $tracklist->setAuthor($manager->getRepository("App:Author")->find(1));

        for ($i = 0; $i < 20; $i++) {
            $track = $manager->getRepository("App:Track")->findRandom();
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
