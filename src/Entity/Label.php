<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Label
 *
 * @ORM\Table(
 *  name="data_label",
 *  indexes={@ORM\Index(name="name", columns={"name"})}
 * )
 * @ORM\Entity
 */
class Label extends BaseEntity
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
     * @ORM\ManyToMany(targetEntity="Album", mappedBy="label")
     */
    private $albums;

    public function __construct()
    {
        $this->albums = new ArrayCollection();
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getAlbums()
    {
        return $this->albums;
    }

    public function addAlbum(Album $album)
    {
        $this->albums[] = $album;
    }

    public function getFlat($preset = 'short')
    {
        $result = [
            'id'   => $this->id,
            'name' => $this->name
        ];

        /*
        if ($preset == 'long') {
            $result['albums'] = $this->albums;
        }
        */

        return $result;
    }

    public static function mapFieldsToElastic($type)
    {
        return [];
    }
}
