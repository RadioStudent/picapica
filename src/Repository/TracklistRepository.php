<?php

namespace App\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\ParameterBag;

use App\Entity\Author;
use App\Entity\Track;
use App\Entity\Tracklist;
use App\Entity\TracklistTrack;

class TracklistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tracklist::class);
    }

    /**
     * @param Author       $author
     * @param ParameterBag $tracklistData
     *
     * @return Tracklist
     */
    public function create($author, $tracklistData)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        $tracklist = new Tracklist(
            $tracklistData->get("name"),
            new \DateTime($tracklistData->get("date")),
            $em->getRepository('App:Term')->find($tracklistData->get("termId")),
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
            ->setTerm($em->getRepository('App:Term')->find($tracklistData->get("termId")));

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

        // Remove cleared tracklist tracks
        $currentTracklistTracks = $tracklist->getTracklistTracks();
        foreach ($currentTracklistTracks as $currentTracklistTrack) {
            $id = $currentTracklistTrack->getId();
            if (!in_array($id, $newTracklistTrackIds)) {
                // If it's an mp3, also remove the track entity
                $track = $currentTracklistTrack->getTrack();

                $em->remove($currentTracklistTrack);
                if ($track->getMp3()) {
                    $em->remove($track);
                }
            }
        }

        $tracklistTrackRepo = $em->getRepository('App:TracklistTrack');
        $trackRepo = $em->getRepository('App:Track');
        foreach ($tracklistTracksData as $trackNum=>$newTracklistTrackData) {
            if (isset($newTracklistTrackData["tracklistTrackId"])) {
                $id = $newTracklistTrackData["tracklistTrackId"];
                $tracklistTrack = $tracklistTrackRepo->find($id);

                $tracklistTrack
                    ->setTrackNum($trackNum)
                    ->setComment($newTracklistTrackData["comment"]);
//                echo "updating track ".$oldTracklistTrack->getTrack()->getName()."\n";

                // Fill mp3 data?
                if (isset($newTracklistTrackData['mp3']) && $newTracklistTrackData['mp3']) {
                    $this->setMp3Data($tracklistTrack->getTrack(), $newTracklistTrackData);
                }
            } else {
                $tracklistTrack = new TracklistTrack();
                $tracklistTrack
                    ->setTracklist($tracklist)
                    ->setComment($newTracklistTrackData["comment"])
                    ->setTrackNum($trackNum);
//                echo "adding track ".$newTracklistTrack->getTrack()->getName()."\n";

                if (isset($newTracklistTrackData['id'])) {
                    $track = $trackRepo->find($newTracklistTrackData["id"]);
                    $tracklistTrack->setTrack($track);
                } elseif (isset($newTracklistTrackData['mp3']) && $newTracklistTrackData['mp3']) {
                    // If it's an mp3, fill remaining fields
                    $track = new Track();
                    $track->setMp3(true);
                    $em->persist($track);

                    $tracklistTrack->setTrack($track);

                    $this->setMp3Data($track, $newTracklistTrackData);
                }

                $tracklist->getTracklistTracks()->add($tracklistTrack);
            }
        }

        $em->flush();
    }

    protected function setMp3Data(Track $track, array $trackData)
    {
        if (!isset($trackData['fid']) || !$trackData['fid']) {
            throw new \Exception('Vsak mp3 potrebuje filename');
        }
        #if (!isset($trackData['albumName']) || !$trackData['albumName']) {
        #    throw new \Exception('Vsak mp3 potrebuje album');
        #}
        if (!isset($trackData['artistName']) || !$trackData['artistName']) {
            throw new \Exception('Vsak mp3 potrebuje artista');
        }
        if (!isset($trackData['name']) || !$trackData['name']) {
            throw new \Exception('Vsak mp3 potrebuje naslov');
        }
        #if (!isset($trackData['year']) || !$trackData['year']) {
        #    throw new \Exception('Vsak mp3 potrebuje leto');
        #}
        if (!isset($trackData['duration']) || !$trackData['duration']) {
            throw new \Exception('Vsak mp3 potrebuje trajanje');
        }

        $track->setFid($trackData['fid']);
        $track->setTrackNum($trackData['fid']);
        $track->setName($trackData['name']);
        $track->setMp3ArtistName($trackData['artistName']);
        if (isset($trackData['albumName'])) {
            $track->setMp3AlbumName($trackData['albumName']);
        }
        if (isset($trackData['year'])) {
            $track->setStrDate($trackData['year']);
        }
        $track->setDuration($trackData['duration']);
        $track->setDate(new \DateTime());
    }
}
