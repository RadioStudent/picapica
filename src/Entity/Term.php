<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Term
 *
 * @ORM\Table("terms")
 * @ORM\Entity
 */
class Term
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
     * @ORM\Column(name="TIME", type="time")
     */
    private $time;

    /**
     * @var integer
     *
     * @ORM\Column(name="DURATION", type="integer", nullable=true)
     */
    private $duration;

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
     *
     * @return $this
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
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param \DateTime $time
     *
     * @return $this
     */
    public function setTime(\DateTime $time)
    {
        $this->time = $time;

        return $this;
    }

    public function getFlat($preset = 'short')
    {
        $result = [
            'id'        => $this->id,
            'name'      => $this->name,
            'time'      => $this->time->format("H:i:s"),
            'group'     => "Weekday", //TODO: term groups!
            'duration'  => $this->duration
        ];

        return $result;
    }
}
