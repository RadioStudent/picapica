<?php

namespace RadioStudent\AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use RadioStudent\AppBundle\Entity\Album;

class AlbumRepository extends EntityRepository {

    public function create($request)
    {
        $em = $this->getEntityManager();

        $data = $this->parseAlbumData($request);

        $album = new Album();

        $album->setDate(new \DateTime());

        if (!$data['fid']) {
            throw new \Exception("Manjka FID albuma");
        }

        $album->setFid($data['fid']);
        $album->setName($data['title']);

        $artistsRepo = $this->getDoctrine()->getRepository('RadioStudentAppBundle:Artist');
        $albumArtist = null;
        if ($data['albumArtistModel'] && $data['albumArtistModel']['id'] && $albumArtist === $data['albumArtistModel']['name']) {
            $albumArtist = $artistRepo->find($data['albumArtistModel']['id']);
            $album->setAlbumArtistName($albumArtist->getName());
        } else {
            $albumArtist = new Artist();
            $albumArtist->setName($data['albumArtistName']);
            $em->persist($albumArtist);
        }
        $album->addArtist($albumArtist);

        if (!$artist || !$artist->getName()) {
            throw new \Exception('Ime izvajalca albuma je neveljavno');
        }

        if (!$data['year']) {
            throw new \Exception('Letnica albuma je neveljavna');
        }

        // Validation
        throw new \Exception("hurrah");

        $em->persist($album);

        return $album;
    }

    protected function parseAlbumData($request)
    {
        var_dump($request->get);die;
        return array_map(function ($param) use ($request) {
            return $request->get($param);
        }, array_keys($request->keys()));
    }

    protected function parseTrackData($tracks)
    {
        var_dump($tracks);die;
    }
}
