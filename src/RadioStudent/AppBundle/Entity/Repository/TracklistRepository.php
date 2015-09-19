<?php

namespace RadioStudent\AppBundle\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use RadioStudent\AppBundle\Entity\Author;
use RadioStudent\AppBundle\Entity\Tracklist;
use RadioStudent\AppBundle\Entity\TracklistTrack;
use Symfony\Component\HttpFoundation\ParameterBag;

class TracklistRepository extends EntityRepository
{
    /**
     * @param Author       $author
     * @param ParameterBag $tracklistData
     */
    public function create($author, $tracklistData)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        $tracklist = new Tracklist(
            $tracklistData->get("name"),
            new \DateTime($tracklistData->get("date")),
            $em->getRepository('RadioStudentAppBundle:Term')->find($tracklistData->get("termId")),
            $author
        );
        $em->persist($tracklist);

        $this->updateTracklistTracks($tracklist, $tracklistData->get("tracks"));

        return $tracklist;
    }

    /**
     * @param Tracklist    $tracklist
     * @param ParameterBag $tracklistData
     */
    public function update($tracklist, $tracklistData)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        $tracklist
            ->setName($tracklistData->get("name"))
            ->setDate(new \DateTime($tracklistData->get("date")))
            ->setTerm($em->getRepository('RadioStudentAppBundle:Term')->find($tracklistData->get("termId")));

        $tracklistTracksData = $tracklistData->get("tracks");

        $this->updateTracklistTracks($tracklist, $tracklistTracksData);

        $em->flush();
    }

    /**
     * @param Tracklist $tracklist
     * @param           $tracklistTracksData
     */
    public function updateTracklistTracks($tracklist, $tracklistTracksData)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        $newTracklistTrackIds = [];
        foreach ($tracklistTracksData as $tracklistTrack) {
            if (isset($tracklistTrack["tracklistTrackId"])) {
                $newTracklistTrackIds[] = $tracklistTrack["tracklistTrackId"];
            }
        }

        $currentTracklistTracks = $tracklist->getTracklistTracks();
        foreach ($currentTracklistTracks as $currentTracklistTrack) {
            if (!in_array($currentTracklistTrack->getId(), $newTracklistTrackIds)) {
                $em->remove($currentTracklistTrack);
//                echo "removing track ".$currentTracklistTrack->getTrack()->getName()."\n";
            }
        }

        $tracklistTrackRepo = $em->getRepository('RadioStudentAppBundle:TracklistTrack');
        $trackRepo = $em->getRepository('RadioStudentAppBundle:Track');
        foreach ($tracklistTracksData as $trackNum=>$newTracklistTrackData) {
            if (isset($newTracklistTrackData["tracklistTrackId"])) {
                $id = $newTracklistTrackData["tracklistTrackId"];
                $oldTracklistTrack = $tracklistTrackRepo->find($id);
                $oldTracklistTrack
                    ->setTrackNum($trackNum)
                    ->setComment($newTracklistTrackData["comment"]);
//                echo "updating track ".$oldTracklistTrack->getTrack()->getName()."\n";
            } else {
                $track = $trackRepo->find($newTracklistTrackData["id"]);
                $newTracklistTrack = new TracklistTrack();
                $newTracklistTrack
                    ->setTrack($track)
                    ->setTracklist($tracklist)
                    ->setComment($newTracklistTrackData["comment"])
                    ->setTrackNum($trackNum);
//                echo "adding track ".$newTracklistTrack->getTrack()->getName()."\n";
                $tracklist->getTracklistTracks()->add($newTracklistTrack);
            }
        }
        $em->flush();
    }
}
