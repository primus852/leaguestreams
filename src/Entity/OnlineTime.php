<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OnlineTimeRepository")
 */
class OnlineTime
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $onlineDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $totalOnline;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Streamer", inversedBy="onlineTimes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Streamer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOnlineDate(): ?\DateTimeInterface
    {
        return $this->onlineDate;
    }

    public function setOnlineDate(\DateTimeInterface $onlineDate): self
    {
        $this->onlineDate = $onlineDate;

        return $this;
    }

    public function getTotalOnline(): ?int
    {
        return $this->totalOnline;
    }

    public function setTotalOnline(?int $totalOnline): self
    {
        $this->totalOnline = $totalOnline;

        return $this;
    }

    public function getStreamer(): ?Streamer
    {
        return $this->Streamer;
    }

    public function setStreamer(?Streamer $Streamer): self
    {
        $this->Streamer = $Streamer;

        return $this;
    }
}
