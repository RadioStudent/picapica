<?php

namespace RadioStudent\AppBundle\DataFixtures\ORM\Seeds;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RadioStudent\AppBundle\Entity\Author;
use RadioStudent\AppBundle\Entity\Term;
use RadioStudent\AppBundle\Entity\User;

class LoadUserData implements FixtureInterface {

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $testUser = new User();
        $testUser->setUsername("testuser");
        $testUser->setPlainPassword("password");
        $testUser->setEmail("testuser@test.com");
        $testUser->setEnabled(true);
        $manager->persist($testUser);

        $testAuthor = new Author();
        $testAuthor->setName("testuser");
        $testAuthor->setUser($testUser);
        $manager->persist($testAuthor);

        $times = [
            "Enka"   => new \DateTime('07:00:00'),
            "Dvojka" => new \DateTime('11:00:00'),
            "Trojka" => new \DateTime('15:00:00'),
        ];

        foreach ($times as $k=>$v) {
            $term = new Term();
            $term->setName($k);
            $term->setTime($v);
            $manager->persist($term);
        }

        $manager->flush();
    }
}