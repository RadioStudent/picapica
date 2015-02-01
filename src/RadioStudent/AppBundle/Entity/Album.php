<?php

namespace RadioStudent\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Album
 *
 * @ORM\Table(
 *  name="data_albums",
 *  indexes={@ORM\Index(name="name", columns={"name"})}
 * )
 * @ORM\Entity
 */
class Album
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
     * @var Collection|Artist[]
     *
     * @ORM\ManyToMany(targetEntity="Artist", inversedBy="albums")
     * @ORM\JoinTable(name="rel_artist2album",
     *      joinColumns={@ORM\JoinColumn(name="ALBUM_ID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="ARTIST_ID", referencedColumnName="ID")}
     *  )
     */
    private $artists;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DATE", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="STR_DATE", type="string", length=255)
     */
    private $strDate;

    /**
     * @var string
     *
     * @ORM\Column(name="FID", type="string", length=30)
     */
    private $fid;

    /**
     * @var Collection|Track[]
     *
     * @ORM\OneToMany(targetEntity="Track", mappedBy="album")
     */
    private $tracks;

    public function __construct()
    {
        $this->artists = new ArrayCollection();
    }

    /**
     * @return Collection|Artist[]
     */
    public function getArtists()
    {
        return $this->artists;
    }

    /**
     * @param Collection|Artist[] $artists
     *
     * @return $this
     */
    public function setArtists(Collection $artists)
    {
        $this->artists = $artists;

        return $this;
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
     * Set fid
     *
     * @param string $fid
     * @return Album
     */
    public function setFid($fid)
    {
        $this->fid = $fid;

        return $this;
    }

    /**
     * Get fid
     *
     * @return string 
     */
    public function getFid()
    {
        return $this->fid;
    }

    /**
     * @return string
     */
    public function getStrDate()
    {
        return $this->strDate;
    }

    /**
     * @param string $strDate
     */
    public function setStrDate($strDate)
    {
        $this->strDate = $strDate;
    }

    /**
     * @return Collection|Track[]
     */
    public function getTracks()
    {
        return $this->tracks;
    }

    /**
     * @param Collection|Track[] $tracks
     */
    public function setTracks(Collection $tracks)
    {
        $this->tracks = $tracks;
    }
}
