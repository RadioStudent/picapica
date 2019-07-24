<?php

namespace RadioStudent\AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use RadioStudent\AppBundle\Entity\Album;
use RadioStudent\AppBundle\Entity\Artist;
use RadioStudent\AppBundle\Entity\Track;

class AlbumRepository extends EntityRepository {

    public function create($data)
    {
        $em = $this->getEntityManager();

        $album = new Album();

        $album->setDate(new \DateTime());

        if (!$data['fid']) {
            throw new \Exception("Manjka FID albuma");
        }

        $album->setFid($data['fid']);
        $album->setName($data['title']);

        $artistList = [];

        $albumArtist = null;
        if ($data['albumArtistModel'] && $data['albumArtistModel']['id'] && $data['albumArtist'] === $data['albumArtistModel']['name']) {
            $artistRepo = $em->getRepository('RadioStudentAppBundle:Artist');
            $albumArtist = $artistRepo->find($data['albumArtistModel']['id']);
        } else {
            $albumArtist = new Artist();
            $albumArtist->setName($data['albumArtist']);
            $em->persist($albumArtist);
        }

        $artistList[$albumArtist->getName()] = $albumArtist;

        if (!$albumArtist || !$albumArtist->getName()) {
            throw new \Exception('Ime izvajalca albuma je neveljavno');
        }

        $album->setAlbumArtistName($albumArtist->getName());
        $album->addArtist($albumArtist);

        $album->setStrDate($data['year']);
        $album->setLabel($data['label']);

        if (!count($data['tracks'])) {
            throw new \Exception('Praznega albuma ne moreš dodati');
        }

        $this->parseTrackData($data['tracks'], $album, $albumArtist, $artistList, $em);

        $em->persist($album);

        return $album;
    }

    protected function parseTrackData($trackData, $album, $albumArtist, $artistList, $em)
    {
        return array_map(function ($komad) use ($album, $albumArtist, $artistList, $em) {
            $track = new Track();

            if (!$komad['fid']) {
                throw new \Exception('Vsak komad rabi fid');
            }
            $track->setTrackNum($komad['fid']);
            $track->setFid($album->getFid() . '-' . $komad['fid']);

            if (!$komad['title']) {
                throw new \Exception('Vsak komad rabi ime');
            }
            $track->setName($komad['title']);
            $track->setFid($album->getFid() . '-' . $komad['fid']);

            $artist = null;
            if (!$komad['artist']) {
                $artist = $albumArtist;
            } elseif (isset($artistList[$komad['artist']])) {
                $artist = $artistList[$komad['artist']];
            } elseif ($komad['artistModel'] && $komad['artistModel']['id'] && $komad['artist'] === $komad['artistModel']['name']) {
                $artistRepo = $em->getRepository('RadioStudentAppBundle:Artist');
                $artist = $artistRepo->find($komad['artistModel']['id']);
                $artistList[$artist->getName()] = $artist;
            } else {
                $artist = new Artist();
                $artist->setName($komad['artist']);
                $em->persist($artist);
                $artistList[$artist->getName()] = $artist;
            }

            if (!$artist || !$artist->getName()) {
                throw new \Exception('Ime izvajalca komada je neveljavno');
            }

            $track->setArtist($artist);
            $album->addArtist($artist);

            $track->setDate($album->getDate());
            $track->setStrDate($album->getStrDate());

            if (!$komad['length']) {
                throw new \Exception('Vsak komad rabi trajanje');
            }
            $trajanje = explode(':', $komad['length']);
            $track->setDuration($trajanje[0] * 60 + $trajanje[1]);

            $em->persist($track);

            $album->addTrack($track);
        }, $trackData);
    }
}
