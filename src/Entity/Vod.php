<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_vods")
 * @ORM\Entity(repositoryClass="App\Repository\VodRepository")
 */
class Vod
{
    /**
     * @ORM\Column(type="string", length=250)
     * @ORM\Id
     */
    protected $videoId;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $thumbnail;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastCheck;

    /**
     * @ORM\Column(type="integer")
     */
    protected $length;

    /**
     * @ORM\ManyToOne(targetEntity="Streamer", inversedBy="vod")
     */
    protected  $streamer;

    /**
     * @return mixed
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * @param mixed $videoId
     */
    public function setVideoId($videoId): void
    {
        $this->videoId = $videoId;
    }

    /**
     * @return mixed
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param mixed $thumbnail
     */
    public function setThumbnail($thumbnail): void
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created): void
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getLastCheck()
    {
        return $this->lastCheck;
    }

    /**
     * @param mixed $lastCheck
     */
    public function setLastCheck($lastCheck): void
    {
        $this->lastCheck = $lastCheck;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     */
    public function setLength($length): void
    {
        $this->length = $length;
    }

    /**
     * @return mixed
     */
    public function getStreamer()
    {
        return $this->streamer;
    }

    /**
     * @param mixed $streamer
     */
    public function setStreamer($streamer): void
    {
        $this->streamer = $streamer;
    }


}
