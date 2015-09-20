<?php

namespace RadioStudent\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ArtistRelation
 *
 * @ORM\Table("rel_artist2artist")
 * @ORM\Entity
 */
class ArtistRelation
{
    const TYPE_CORRECTED = "correction";
    const TYPE_MISTAKE = "mistake";
    const TYPE_DIFFERENT = "unrelated";
    const TYPE_BOTH_CORRECT = "alias";

    /**
     * @var integer
     *
     * @ORM\Column(name="ID", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Artist
     *
     * @ORM\ManyToOne(targetEntity="Artist", inversedBy="artistRelations")
     * @ORM\JoinColumn(name="ARTIST_ID", referencedColumnName="ID")
     */
    private $artist;

    /**
     * @var Artist
     *
     * @ORM\ManyToOne(targetEntity="Artist")
     * @ORM\JoinColumn(name="RELATED_ARTIST_ID", referencedColumnName="ID")
     */
    private $relatedArtist;

    /**
     * @var integer This field describes what the relatedArtist is to artist.
     * @ORM\Column(name="RELATION_TYPE", type="string")
     */
    private $type;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return $this
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * @return Artist
     */
    public function getRelatedArtist()
    {
        return $this->relatedArtist;
    }

    /**
     * @param Artist $relatedArtist
     * @return $this
     */
    public function setRelatedArtist($relatedArtist)
    {
        $this->relatedArtist = $relatedArtist;

        return $this;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
