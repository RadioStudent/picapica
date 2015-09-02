<?php

namespace RadioStudent\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tracklist
 *
 * @ORM\Table("tracklists")
 * @ORM\Entity
 */
class Tracklist
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="NAME", type="string", length=255)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DATE", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var Collection|TracklistTrack[]
     *
     * @ORM\OneToMany(targetEntity="TracklistTrack", mappedBy="tracklist")
     * @ORM\OrderBy({"trackNum" = "ASC"})
     */
    private $tracklistTracks;

    /**
     * @var Author
     *
     * @ORM\OneToOne(targetEntity="Author")
     * @ORM\JoinColumn(name="AUTHOR_ID", referencedColumnName="ID")
     */
    private $author;

    /**
     * @var Term
     *
     * @ORM\OneToOne(targetEntity="Term")
     * @ORM\JoinColumn(name="TERM_ID", referencedColumnName="ID")
     */
    private $term;

    public function __construct()
    {
        $this->tracks = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return Collection|TracklistTrack[]
     */
    public function getTracklistTracks()
    {
        return $this->tracklistTracks;
    }

    /**
     * @param Collection|TracklistTrack[] $tracklistTracks
     *
     * @return $this
     */
    public function setTracklistTracks($tracklistTracks)
    {
        $this->tracklistTracks = $tracklistTracks;

        return $this;
    }

    /**
     * @return Author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param Author $author
     *
     * @return $this
     */
    public function setAuthor(Author $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Term
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param Term $term
     *
     * @return $this
     */
    public function setTerm(Term $term)
    {
        $this->term = $term;

        return $this;
    }

    public function getFlat($preset = 'short')
    {
        $result = [
            'id'             => $this->id,
            'name'           => $this->name,
            'date'           => $this->date->format("Y-m-d"),
            'termId'         => $this->term->getId(),
            'authorId'       => $this->author->getId(),
        ];

        if ($preset == 'short') {
            $result['numTracks'] = count($this->tracklistTracks);

            return $result;
        }

        $result['tracks'] = [];
        foreach ($this->tracklistTracks as $t) {
            $track = $t->getTrack();
            $result['tracks'][] = [
                'id'                => $track->getId(),
                'fid'               => $track->getFid(),
                'trackNum'          => $track->getTrackNum(),
                'name'              => $track->getName(),
                'year'              => $track->getDate()? $track->getDate()->format('Y'): null,
                'artistName'        => $track->getArtist()->getCorrectName(),
                'artistId'          => $track->getArtist()->getId(),
                'albumName'         => $track->getAlbum()->getName(),
                'albumArtistName'   => $track->getAlbum()->getAlbumArtistName(),
                'albumId'           => $track->getAlbum()->getId(),
                'duration'          => $track->getDuration(),
                'languages'         => $track->getLanguages(),
                'genres'            => $track->getGenres(),
            ];
        }

        return $result;
    }
}
