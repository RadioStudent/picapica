<?php

namespace RadioStudent\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Track
 *
 * @ORM\Table("data_tracks")
 * @ORM\Entity
 */
class Track
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JMS\Groups({
     *  "albums",
     *  "tracks",
     *  "track",
     *  "album",
     * })
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="FID", type="string", length=30)
     *
     * @JMS\Groups({
     *  "albums",
     *  "tracks",
     *  "track",
     *  "album",
     * })
     */
    private $fid;

    /**
     * @var string
     *
     * @ORM\Column(name="TRACK_NUM", type="string", length=30)
     *
     * @JMS\Groups({
     *  "albums",
     *  "tracks",
     *  "track",
     *  "album",
     * })
     */
    private $trackNum;

    /**
     * @var string
     *
     * @ORM\Column(name="NAME", type="string", length=255)
     *
     * @JMS\Groups({
     *  "albums",
     *  "tracks",
     *  "track",
     *  "album",
     * })
     */
    private $name;

    /**
     * @var Album
     *
     * @ORM\ManyToOne(targetEntity="Artist", inversedBy="tracks")
     * @ORM\JoinColumn(name="ARTIST_ID", referencedColumnName="ID")
     *
     * @JMS\Groups({
     *  "tracks",
     *  "track",
     *  "album",
     * })
     */
    private $artist;

    /**
     * @var Album
     *
     * @ORM\ManyToOne(targetEntity="Album", inversedBy="tracks")
     * @ORM\JoinColumn(name="ALBUM_ID", referencedColumnName="ID")
     *
     * @JMS\Groups({
     *  "tracks",
     *  "track"
     * })
     */
    private $album;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DATE", type="datetime", nullable=true)
     *
     * @JMS\Groups({
     *  "tracks",
     *  "track"
     * })
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="STR_DATE", type="string", length=255)
     */
    private $strDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DURATION", type="time", nullable=true)
     *
     * @JMS\Groups({
     *  "tracks",
     *  "track",
     *  "album",
     * })
     */
    private $duration;

    /**
     * @var string
     *
     * @ORM\Column(name="GENRES", type="string", length=255, nullable=true)
     *
     * @JMS\Groups({
     *  "track"
     * })
     */
    private $genres;

    /**
     * @var string
     *
     * @ORM\Column(name="LANGUAGES", type="string", length=255, nullable=true)
     *
     * @JMS\Groups({
     *  "track"
     * })
     */
    private $languages;

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
     * Set fid
     *
     * @param string $fid
     * @return Track
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
     * Set trackNum
     *
     * @param string $trackNum
     * @return Track
     */
    public function setTrackNum($trackNum)
    {
        $this->trackNum = $trackNum;

        return $this;
    }

    /**
     * Get trackNum
     *
     * @return string 
     */
    public function getTrackNum()
    {
        return $this->trackNum;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Track
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
     * @return Track
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
     * @return string
     */
    public function getStrDate()
    {
        return $this->strDate;
    }

    /**
     * @param string $strDate
     * @return $this
     */
    public function setStrDate($strDate)
    {
        $this->strDate = $strDate;

        return $this;
    }

    /**
     * Set duration
     *
     * @param \DateInterval $duration
     * @return Track
     */
    public function setDuration(\DateInterval $duration = null)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return \DateInterval
     */
    public function getDuration()
    {
        $r = null;
        if (null !== $this->duration) {
            $r = new \DateInterval($this->duration->format('\P\TG\Hi\Ms\S'));
            $r = $r->format('%H:%I:%S');
        }
        return $r;
    }

    /**
     * Set genres
     *
     * @param string $genres
     * @return Track
     */
    public function setGenres($genres)
    {
        $this->genres = $genres;

        return $this;
    }

    /**
     * Get genres
     *
     * @return string 
     */
    public function getGenres()
    {
        return $this->genres;
    }

    /**
     * Set languages
     *
     * @param string $languages
     * @return Track
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Get languages
     *
     * @return string 
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @return Album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * @param Album $album
     *
     * @return $this
     */
    public function setAlbum(Album $album)
    {
        $this->album = $album;

        return $this;
    }

    /**
     * @return Artist
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * @param Artist $artist
     *
     * @return $this
     */
    public function setArtist(Artist $artist)
    {
        $this->artist = $artist;

        return $this;
    }
}
