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
    /**
     * @var Artist
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Artist")
     * @ORM\JoinColumn(name="ARTIST_ID1", referencedColumnName="ID")
     */
    private $artist1;

    /**
     * @var Artist
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Artist")
     * @ORM\JoinColumn(name="ARTIST_ID2", referencedColumnName="ID")
     */
    private $artist2;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="TYPE", type="integer")
     */
    private $type;

    /**
     * @return Artist
     */
    public function getArtist1()
    {
        return $this->artist1;
    }

    /**
     * @param Artist $artist1
     * @return $this
     */
    public function setArtist1($artist1)
    {
        $this->artist1 = $artist1;

        return $this;
    }

    /**
     * @return Artist
     */
    public function getArtist2()
    {
        return $this->artist2;
    }

    /**
     * @param Artist $artist2
     * @return $this
     */
    public function setArtist2($artist2)
    {
        $this->artist2 = $artist2;

        return $this;
    }

    /**
     * Set type
     *
     * @param integer $type
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
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }
}
