<?php

namespace RadioStudent\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TracklistTrack
 *
 * @ORM\Table("rel_tracklist2track")
 * @ORM\Entity
 */
class TracklistTrack
{
    //TODO: primary key
    /**
     * @var Tracklist
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Tracklist", inversedBy="tracklistTracks")
     * @ORM\JoinColumn(name="TRACKLIST_ID", referencedColumnName="ID")
     */
    private $tracklist;

    /**
     * @var Track
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Track")
     * @ORM\JoinColumn(name="TRACK_ID", referencedColumnName="ID")
     */
    private $track;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="TRACK_NUM", type="integer")
     */
    private $trackNum;

    /**
     * @var string
     *
     * @ORM\Column(name="COMMENT", type="string", length=255)
     */
    private $comment = "";

    /**
     * @return Tracklist
     */
    public function getTracklist()
    {
        return $this->tracklist;
    }

    /**
     * @param Tracklist $tracklist
     *
     * @return $this
     */
    public function setTracklist(Tracklist $tracklist)
    {
        $this->tracklist = $tracklist;

        return $this;
    }

    /**
     * @return Track
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * @param Track $track
     *
     * @return $this
     */
    public function setTrack(Track $track)
    {
        $this->track = $track;

        return $this;
    }

    /**
     * @return int
     */
    public function getTrackNum()
    {
        return $this->trackNum;
    }

    /**
     * @param int $trackNum
     *
     * @return $this
     */
    public function setTrackNum($trackNum)
    {
        $this->trackNum = $trackNum;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

}
