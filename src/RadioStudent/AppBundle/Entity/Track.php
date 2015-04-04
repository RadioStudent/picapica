<?php

namespace RadioStudent\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Track
 *
 * @ORM\Table("data_tracks")
 * @ORM\Entity
 */
class Track extends BaseEntity
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
     * @ORM\Column(name="FID", type="string", length=30)
     */
    private $fid;

    /**
     * @var string
     *
     * @ORM\Column(name="TRACK_NUM", type="string", length=30)
     */
    private $trackNum;

    /**
     * @var string
     *
     * @ORM\Column(name="NAME", type="string", length=255)
     */
    private $name;

    /**
     * @var Artist
     *
     * @ORM\ManyToOne(targetEntity="Artist", inversedBy="tracks")
     * @ORM\JoinColumn(name="ARTIST_ID", referencedColumnName="ID")
     */
    private $artist;

    /**
     * @var Album
     *
     * @ORM\ManyToOne(targetEntity="Album", inversedBy="tracks")
     * @ORM\JoinColumn(name="ALBUM_ID", referencedColumnName="ID")
     */
    private $album;

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
     * @var integer
     *
     * @ORM\Column(name="DURATION", type="integer", nullable=true)
     */
    private $duration;

    /**
     * @var string
     *
     * @ORM\Column(name="GENRES", type="string", length=255, nullable=true)
     */
    private $genres;

    /**
     * @var string
     *
     * @ORM\Column(name="LANGUAGES", type="string", length=255, nullable=true)
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
     * @param integer $duration
     * @return Track
     */
    public function setDuration($duration = null)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
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

    public function getFlat($preset = 'short')
    {
        $result = [
            'id'                => $this->id,
            'fid'               => $this->fid,
            'trackNum'          => $this->trackNum,
            'name'              => $this->name,
            'year'              => $this->date? $this->date->format('Y'): null,
            'artistName'        => $this->artist->getCorrectName(),
            'artistId'          => $this->artist->getId(),
            'albumName'         => $this->album->getName(),
            'albumArtistName'   => $this->album->getAlbumArtistName(),
            'albumId'           => $this->album->getId(),
            'duration'          => $this->duration,
            'languages'         => $this->languages,
            'genres'            => $this->genres,
        ];

        if ($result['artistName'] != $this->artist->getName()) {
            $result['artistMistake'] = $this->artist->getName();
        }

        return $result;
    }

    public static function mapFieldsToElastic()
    {
        return [
            'artistName' => 'artist.name',
            'artistId'   => 'artist.id',
            'albumName'  => 'album.name',
            'albumId'    => 'album.id',
        ];
    }
}
