<?php

namespace RadioStudent\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Album
 *
 * @ORM\Table(
 *  name="data_albums",
 *  indexes={@ORM\Index(name="name", columns={"name"})}
 * )
 * @ORM\Entity
 */
class Album extends BaseEntity
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
     *  "album",
     *  "tracks",
     *  "track",
     *  "artists",
     *  "artist",
     * })
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="NAME", type="string", length=255)
     *
     * @JMS\Groups({
     *  "albums",
     *  "album",
     *  "tracks",
     *  "track",
     *  "artists",
     *  "artist",
     * })
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
     *
     * @JMS\Groups({
     *  "albums",
     *  "album",
     *  "tracks",
     *  "track",
     * })
     */
    private $artists;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DATE", type="datetime", nullable=true)
     *
     * @JMS\Groups({
     *  "albums",
     *  "album",
     *  "track",
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
     * @var string
     *
     * @ORM\Column(name="FID", type="string", length=30)
     *
     * @JMS\Groups({"tracks"})
     */
    private $fid;

    /** awef
     * @var Collection|Track[]
     *
     * @ORM\OneToMany(targetEntity="Track", mappedBy="album")
     * @ORM\OrderBy({"trackNum" = "ASC"})
     *
     * @JMS\Groups({
     *  "albums",
     *  "album",
     * })
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

    public function getAlbumArtistName()
    {
        if (count($this->artists) == 1) {
            return $this->artists[0]->getName();

        } else if (count($this->artists) == 2) {
            return $this->artists[0]->getName() . " & " . $this->artists[1]->getName();

        } else {
            return "V/A (" . count($this->artists) . ")";
        }
    }

    public function getFlat($preset = 'short')
    {
        $result = [
            'id' => $this->id,
            'fid' => $this->fid,
            'name' => $this->name,
            'year' => $this->date? $this->date->format('Y'): null,
        ];

        $result['artists'] = [];
        foreach ($this->artists as $a) {
            $result['artists'][] = ['id' => $a->getId(), 'name' => $a->getName()];
        }

        return $result;
    }

    public static function mapFieldsToElastic()
    {
        return [
        ];
    }
}
