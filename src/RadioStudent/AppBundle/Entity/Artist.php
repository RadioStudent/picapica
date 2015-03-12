<?php

namespace RadioStudent\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Artist
 *
 * @ORM\Table(
 *  name="data_artists",
 *  indexes={@ORM\Index(name="name", columns={"name"})}
 * )
 * @ORM\Entity
 *
 * A@JMS\ExclusionPolicy("all") awefawfe
 */
class Artist extends BaseEntity
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
     * @var Collection|Album[]
     *
     * @ORM\ManyToMany(
     *  targetEntity="Album",
     *  mappedBy="artists",
     *  fetch="LAZY"
     * )
     *
     * @JMS\Groups({
     *  "artists",
     *  "artist",
     * })
     */
    private $albums;

    /**
     * @var Collection|ArtistRelation[]
     *
     * @ORM\OneToMany(
     *  targetEntity="ArtistRelation",
     *  mappedBy="artist",
     *  fetch="LAZY"
     * )
     *
     * @JMS\Groups({
     *  "artist",
     *  "artists",
     * })
     * @JMS\MaxDepth(1)
     */
    private $artistRelations;

    public function __construct()
    {
        $this->albums = new ArrayCollection();
        $this->artistRelations = new ArrayCollection();
    }

    /**
     * @return Collection|Album[]
     */
    public function getAlbums()
    {
        return $this->albums;
    }

    /**
     * @param Collection|Album[] $albums
     *
     * @return $this
     */
    public function setAlbums(array $albums)
    {
        $this->albums = $albums;

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
     * @return Artist
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
     * @return Collection|ArtistRelation[]
     */
    public function getArtistRelations()
    {
        return $this->artistRelations;
    }

    /**
     * @param Collection|ArtistRelation[] $artistRelations
     *
     * @return $this
     */
    public function setArtistRelations($artistRelations)
    {
        $this->artistRelations = $artistRelations;

        return $this;
    }

    public function collectRelations(&$visited = [])
    {
        if (in_array($this->id, $visited)) return [];

        $_tm = ArtistRelation::TYPE_MISTAKE;
        $_tc = ArtistRelation::TYPE_CORRECTED;
        $_ta = ArtistRelation::TYPE_BOTH_CORRECT;

        $artists = [
            $this->id => [
                "artist" => $this,
                "counts" => [$_tm => 0, $_tc => 0, $_ta => 0]
            ]
        ];

        $visited = array_unique(array_merge($visited, [$this->id]));

        foreach ($this->artistRelations as $relation) {
            if ($relation->getType() == $_tm) {
                $artists[$this->id]["counts"][$_tc]++;
                $artists += $relation->getRelatedArtist()->collectRelations($visited);
            }
        }

        foreach ($this->artistRelations as $relation) {
            if ($relation->getType() == $_tc) {
                $artists[$this->id]["counts"][$_tm]++;
                $artists += $relation->getRelatedArtist()->collectRelations($visited);
            }
        }

        foreach ($this->artistRelations as $relation) {
            if ($relation->getType() == $_ta) {
                $artists[$this->id]["counts"][$_ta]++;
                $artists += $relation->getRelatedArtist()->collectRelations($visited);
            }
        }

        return $artists;

    }

    public function getRelatedArtists()
    {
        $artists = $this->collectRelations();

        $ret = [];
        foreach ($artists as $id=>$obj) {
            /** @var Artist $artist */
            $artist = $obj["artist"];

            $ret[$id] = [$artist->getName(), $obj["counts"]];
        }

        return $ret;
    }

    public function getFlat()
    {
        $result = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        return $result;
    }

    public static function mapFieldsToElastic()
    {
        return [
        ];
    }
}
