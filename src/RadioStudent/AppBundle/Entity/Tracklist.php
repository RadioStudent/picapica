<?php

namespace RadioStudent\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Tracklist
 *
 * @ORM\Table(
 *  name="tracklists"
 * )
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
     * @return Album
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
     * @return Album
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

}
