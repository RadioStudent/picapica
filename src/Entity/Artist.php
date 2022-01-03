<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Repository\ArtistRepository;

/**
 * Artist
 *
 * @ORM\Table(
 *  name="data_artists",
 *  indexes={@ORM\Index(name="name", columns={"name"})}
 * )
 * @ORM\Entity(repositoryClass=ArtistRepository::class)
 */
class Artist extends BaseEntity
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
     * @var Collection|Album[]
     *
     * @ORM\ManyToMany(
     *  targetEntity="Album",
     *  mappedBy="artists",
     *  fetch="LAZY"
     * )
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
     */
    private $artistRelations;

    /**
     * @var Collection|Artist[]
     */
    private $allRelatedArtists;

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

    public function getAllRelatedArtists()
    {
        if (!$this->allRelatedArtists) {
            $this->allRelatedArtists = $this->collectRelations();
        }

        return $this->allRelatedArtists;
    }

    public function getAllRelatedArtistIds()
    {
        $artists = $this->getAllRelatedArtists();

        return array_keys($artists);
    }

    public function getAllRelatedArtistNames()
    {
        $artists = $this->getAllRelatedArtists();

        $ret = [];
        foreach ($artists as $id=>$obj) {
            /** @var Artist $artist */
            $artist = $obj["artist"];

            $ret[$id] = [$artist->getName(), $obj["counts"]];
        }

        return $ret;
    }

    public function getCorrectName()
    {
        $artists = $this->getAllRelatedArtists();

        $best = [$this, 0, 0];
        foreach ($artists as $id=>$obj) {
            if ($obj['counts']['correction'] > $best[1] ||
                $obj['counts']['correction'] == $best[1] && $obj['counts']['mistake'] < $best[2]) {

                $best = [$obj['artist'], $obj['counts']['correction'], $obj['counts']['mistake']];
            }
        }

        /** @var Artist $artist */
        $artist = $best[0];

        return $artist->getName();
    }

    public function getFlat($preset = 'short')
    {
        $result = [];

        if ($preset == 'short') {
            $result = [
                'id'          => $this->id,
                'name'        => $this->name,
                'correctName' => $this->getCorrectName(),
                'relatedIds'  => $this->getAllRelatedArtistIds(),
            ];

        } elseif ($preset == 'long') {
            $result = [
                'id'             => $this->id,
                'name'           => $this->name,
                'correctName'    => $this->getCorrectName(),
                'relatedArtists' => $this->getAllRelatedArtistNames(),
                'albums'         => $this->getAlbums(),
            ];
        }

        return $result;
    }

    public static function mapFieldsToElastic($type)
    {
        return [];
    }
}
