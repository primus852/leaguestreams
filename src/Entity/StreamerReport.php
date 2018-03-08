<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_streamer_report")
 * @ORM\Entity(repositoryClass="App\Repository\StreamerReportRepository")
 */
class StreamerReport
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Streamer", inversedBy="streamerReport")
     */
    protected  $streamer;

    /**
     * @ORM\Column(type="text")
     */
    protected $reason;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $ip;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isResolved;

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

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param mixed $reason
     */
    public function setReason($reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getisResolved()
    {
        return $this->isResolved;
    }

    /**
     * @param mixed $isResolved
     */
    public function setIsResolved($isResolved): void
    {
        $this->isResolved = $isResolved;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


}
