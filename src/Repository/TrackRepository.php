<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Track;

class TrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    /**
     * @return Track
     */
    public function findRandom()
    {
        $em = $this->getEntityManager();

        $count = $em->createQueryBuilder()
            ->select("count(track.id)")
            ->from("App:Track", "track")
            ->getQuery()
            ->getSingleScalarResult();

        $track = $em->createQueryBuilder()
            ->select("track")
            ->from("App:Track", "track")
            ->setFirstResult(mt_rand(0, $count-1))
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return $track;
    }
}
