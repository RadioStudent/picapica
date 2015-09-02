<?php

namespace RadioStudent\AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use RadioStudent\AppBundle\Entity\Track;

class TrackRepository extends EntityRepository
{
    /**
     * @return Track
     */
    public function findRandom()
    {
        $em = $this->getEntityManager();

        $count = $em->createQueryBuilder()
            ->select("count(track.id)")
            ->from("RadioStudentAppBundle:Track", "track")
            ->getQuery()
            ->getSingleScalarResult();

        $track = $em->createQueryBuilder()
            ->select("track")
            ->from("RadioStudentAppBundle:Track", "track")
            ->setFirstResult(mt_rand(0, $count-1))
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return $track;
    }
}