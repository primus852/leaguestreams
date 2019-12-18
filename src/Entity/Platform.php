<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_platforms")
 * @ORM\Entity(repositoryClass="App\Repository\PlatformRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Platform
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Streamer", mappedBy="platform")
     */
    protected $streamer;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $loginUrl;

    /**
     * @ORM\Column(type="string", length=200)
     */
    protected $url;

    /**
     * @ORM\Column(type="string", length=200)
     */
    protected $channelUrl;

    /**
     * @ORM\Column(name="last_modified", type="datetime", nullable=false)
     */
    protected $modified;

    public function __construct()
    {
        $this->streamer = new ArrayCollection();
    }

    /**
     * @ORM\PreUpdate
     */
    public function setModified()
    {
        $this->modified = new \DateTime();
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

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLoginUrl()
    {
        return $this->loginUrl;
    }

    /**
     * @param mixed $loginUrl
     */
    public function setLoginUrl($loginUrl): void
    {
        $this->loginUrl = $loginUrl;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getChannelUrl()
    {
        return $this->channelUrl;
    }

    /**
     * @param mixed $channelUrl
     */
    public function setChannelUrl($channelUrl): void
    {
        $this->channelUrl = $channelUrl;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getModified()
    {
        return $this->modified;
    }

    public function addStreamer(Streamer $streamer): self
    {
        if (!$this->streamer->contains($streamer)) {
            $this->streamer[] = $streamer;
            $streamer->setPlatform($this);
        }

        return $this;
    }

    public function removeStreamer(Streamer $streamer): self
    {
        if ($this->streamer->contains($streamer)) {
            $this->streamer->removeElement($streamer);
            // set the owning side to null (unless already changed)
            if ($streamer->getPlatform() === $this) {
                $streamer->setPlatform(null);
            }
        }

        return $this;
    }


}
