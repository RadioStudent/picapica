<?php

namespace RadioStudent\AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use RadioStudent\AppBundle\Entity\Album;
use RadioStudent\AppBundle\Entity\Artist;
use RadioStudent\AppBundle\Entity\Track;
use RadioStudent\AppBundle\Entity\Herkunft;
use RadioStudent\AppBundle\Entity\Label;
use RadioStudent\AppBundle\Entity\Genre;

class AlbumRepository extends EntityRepository {
    protected function setData($album, $data)
    {
        $em = $this->getEntityManager();
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
        //$album->setLabel($data['label']);

        if (!count($data['tracks'])) {
            throw new \Exception('Praznega albuma ne moreš dodati');
        }

        $this->parseTrackData($data['tracks'], $album, $albumArtist, $artistList, $em);

        if (isset($data['herkunft'])) {
            $this->parseHerkunft($data['herkunft'], $album, $em);
        }
        if (isset($data['labels'])) {
            $this->parseLabels($data['labels'], $album, $em);
        }
        if (isset($data['genres'])) {
            $this->parseGenres($data['genres'], $album, $em);
        }
    }

    public function create($data)
    {
        $em = $this->getEntityManager();

        $album = new Album();
        $this->setData($album, $data);

        $em->persist($album);

        return $album;
    }

    public function update($data)
    {
        if (!isset($data['id']) || empty($data['id'])) {
            throw new \Exception('Za urejanje album potrebuje ID');
        }

        $em = $this->getEntityManager();
        $album = $em->getRepository('RadioStudentAppBundle:Album')->find($data['id']);
        $this->setData($album, $data);

        $em->persist($album);

        return $album;
    }

    protected function parseTrackData($trackData, $album, $albumArtist, $artistList, $em)
    {
        $artistRepository = $em->getRepository('RadioStudentAppBundle:Artist');
        $trackRepository = $em->getRepository('RadioStudentAppBundle:Track');

        $albumTracks = [];
        foreach ($trackData as $komad) {
            $track = null;

            if (isset($komad['id']) && !empty($komad['id'])) {
                $track = $trackRepository->find($komad['id']);
            } else {
                $track = new Track();
                $em->persist($track);
                $album->addTrack($track);
            }

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
                $artist = $artistRepository->find($komad['artistModel']['id']);
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

            $track->setDate(new \DateTime());
            $track->setStrDate($album->getStrDate());

            if (!$komad['length']) {
                throw new \Exception('Vsak komad rabi trajanje');
            }
            $track->setDuration($komad['length']);

            $albumTracks[] = $track->getId();
        }

        // Brisi odstranjene komade
        $deletedTracks = array_filter($album->getTracks()->toArray(), function ($track) use ($albumTracks) {
            return !in_array($track->getId(), $albumTracks);
        });

        foreach($deletedTracks as $track) {
            $track->setDeleted(true);
        }
    }

    protected function parseHerkunft($herkunft, $album, $em)
    {
        $hr = $em->getRepository('RadioStudentAppBundle:Herkunft');

        $albumHerkunft = [];
        foreach ($herkunft as $h) {
            $herkunft = null;

            if ($this->tagExists($h)) {
                $herkunft = $hr->find($h['id']);
            } else {
                $herkunft = new Herkunft();
                $herkunft->setName($h['name']);
                $em->persist($herkunft);
            }

            $album->addHerkunft($herkunft);

            $albumHerkunft[] = $herkunft->getId();
        }

        // Brisi odstranjene komade
        foreach ($album->getHerkunft()->toArray() as $h) {
            if (!in_array($h->getId(), $albumHerkunft)) {
                $album->removeHerkunft($h);
            }
        }
    }

    protected function parseLabels($labels, $album, $em)
    {
        $hr = $em->getRepository('RadioStudentAppBundle:Label');

        $albumLabels = [];
        foreach ($labels as $h) {
            $label = null;

            if ($this->tagExists($h)) {
                $label = $hr->find($h['id']);
            } else {
                $label = new Label();
                $label->setName($h['name']);
                $em->persist($label);
            }

            $album->addLabel($label);

            $albumLabels[] = $label->getId();
        }

        // Brisi odstranjene komade
        foreach ($album->getLabels()->toArray() as $h) {
            if (!in_array($h->getId(), $albumLabels)) {
                $album->removeLabel($h);
            }
        }
    }

    protected function parseGenres($genres, $album, $em)
    {
        $hr = $em->getRepository('RadioStudentAppBundle:Genre');

        $albumGenres = [];
        foreach ($genres as $h) {
            $genre = null;

            if ($this->tagExists($h)) {
                $genre = $hr->find($h['id']);
            } else {
                $genre = new Genre();
                $genre->setName($h['name']);
                $em->persist($genre);
            }

            $album->addGenre($genre);

            $albumGenres[] = $genre->getId();
        }

        // Brisi odstranjene komade
        foreach ($album->getGenres()->toArray() as $h) {
            if (!in_array($h->getId(), $albumGenres)) {
                $album->removeGenre($h);
            }
        }
    }

    protected function tagExists($tag) {
        return !isset($tag['new']) && isset($tag['id']) && !empty($tag['id']);
    }
}
