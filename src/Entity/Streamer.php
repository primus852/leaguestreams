<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_streamer", uniqueConstraints={@ORM\UniqueConstraint(name="channel_platform", columns={"channel_name", "platform"})})
 * @ORM\Entity(repositoryClass="App\Repository\StreamerRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Streamer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Platform", inversedBy="streamer")
     * @ORM\JoinColumn(name="platform", referencedColumnName="id")
     */
    protected $platform;

    /**
     * @ORM\OneToMany(targetEntity="StreamerReport", mappedBy="streamer")
     */
    protected  $streamerReport;

    /**
     * @ORM\OneToMany(targetEntity="Vod", mappedBy="streamer")
     */
    protected  $vod;

    /**
     * @ORM\OneToMany(targetEntity="Summoner", mappedBy="streamer", cascade={"remove"})
     */
    protected  $summoner;

    /**
     * @ORM\OneToMany(targetEntity="Smurf", mappedBy="streamer")
     */
    protected  $smurf;

    /**
     * @ORM\OneToMany(targetEntity="Report", mappedBy="streamer")
     */
    protected  $report;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="streamer")
     */
    protected  $match;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $channelName;

    /**
     * @ORM\Column(type="string", length=150)
     */
    protected $channelUser;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isOnline;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $viewers;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $resolution;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $fps;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $delay;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected $language;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    protected $thumbnail;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    protected $logo;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    protected $banner;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $started;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isFeatured;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isPartner;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $channelId;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $modified;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $created;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OnlineTime", mappedBy="Streamer", orphanRemoval=true)
     */
    private $onlineTimes;

    public function __construct()
    {
        $this->streamerReport = new ArrayCollection();
        $this->vod = new ArrayCollection();
        $this->summoner = new ArrayCollection();
        $this->smurf = new ArrayCollection();
        $this->report = new ArrayCollection();
        $this->match = new ArrayCollection();
        $this->onlineTimes = new ArrayCollection();
    }

    /**
     * @ORM\PreUpdate
     */
    public function setModified()
    {
        $this->modified = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreated()
    {
        $this->created = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param mixed $platform
     */
    public function setPlatform($platform): void
    {
        $this->platform = $platform;
    }

    /**
     * @return mixed
     */
    public function getStreamerReport()
    {
        return $this->streamerReport;
    }

    /**
     * @param mixed $streamerReport
     */
    public function setStreamerReport($streamerReport): void
    {
        $this->streamerReport = $streamerReport;
    }

    /**
     * @return mixed
     */
    public function getVod()
    {
        return $this->vod;
    }

    /**
     * @param mixed $vod
     */
    public function setVod($vod): void
    {
        $this->vod = $vod;
    }

    /**
     * @return mixed
     */
    public function getSummoner()
    {
        return $this->summoner;
    }

    /**
     * @param mixed $summoner
     */
    public function setSummoner($summoner): void
    {
        $this->summoner = $summoner;
    }

    /**
     * @return mixed
     */
    public function getSmurf()
    {
        return $this->smurf;
    }

    /**
     * @param mixed $smurf
     */
    public function setSmurf($smurf): void
    {
        $this->smurf = $smurf;
    }

    /**
     * @return mixed
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param mixed $report
     */
    public function setReport($report): void
    {
        $this->report = $report;
    }

    /**
     * @return mixed
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * @param mixed $match
     */
    public function setMatch($match): void
    {
        $this->match = $match;
    }

    /**
     * @return mixed
     */
    public function getChannelName()
    {
        return $this->channelName;
    }

    /**
     * @param mixed $channelName
     */
    public function setChannelName($channelName): void
    {
        $this->channelName = $channelName;
    }

    /**
     * @return mixed
     */
    public function getChannelUser()
    {
        return $this->channelUser;
    }

    /**
     * @param mixed $channelUser
     */
    public function setChannelUser($channelUser): void
    {
        $this->channelUser = $channelUser;
    }

    /**
     * @return mixed
     */
    public function getisOnline()
    {
        return $this->isOnline;
    }

    /**
     * @param mixed $isOnline
     */
    public function setIsOnline($isOnline): void
    {
        $this->isOnline = $isOnline;
    }


    /**
     * @return mixed
     */
    public function getViewers()
    {
        return $this->viewers;
    }

    /**
     * @param mixed $viewers
     */
    public function setViewers($viewers): void
    {
        $this->viewers = $viewers;
    }

    /**
     * @return mixed
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * @param mixed $resolution
     */
    public function setResolution($resolution): void
    {
        $this->resolution = $resolution;
    }

    /**
     * @return mixed
     */
    public function getFps()
    {
        return $this->fps;
    }

    /**
     * @param mixed $fps
     */
    public function setFps($fps): void
    {
        $this->fps = $fps;
    }

    /**
     * @return mixed
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param mixed $delay
     */
    public function setDelay($delay): void
    {
        $this->delay = $delay;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language): void
    {
        $this->language = $language;
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
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param mixed $logo
     */
    public function setLogo($logo): void
    {
        $this->logo = $logo;
    }

    /**
     * @return mixed
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @param mixed $banner
     */
    public function setBanner($banner): void
    {
        $this->banner = $banner;
    }

    /**
     * @return mixed
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * @param mixed $started
     */
    public function setStarted($started): void
    {
        $this->started = $started;
    }

    /**
     * @return mixed
     */
    public function getisFeatured()
    {
        return $this->isFeatured;
    }

    /**
     * @param mixed $isFeatured
     */
    public function setIsFeatured($isFeatured): void
    {
        $this->isFeatured = $isFeatured;
    }

    /**
     * @return mixed
     */
    public function getisPartner()
    {
        return $this->isPartner;
    }

    /**
     * @param mixed $isPartner
     */
    public function setIsPartner($isPartner): void
    {
        $this->isPartner = $isPartner;
    }

    /**
     * @return mixed
     */
    public function getChannelId()
    {
        return $this->channelId;
    }

    /**
     * @param mixed $channelId
     */
    public function setChannelId($channelId): void
    {
        $this->channelId = $channelId;
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

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    public function addStreamerReport(StreamerReport $streamerReport): self
    {
        if (!$this->streamerReport->contains($streamerReport)) {
            $this->streamerReport[] = $streamerReport;
            $streamerReport->setStreamer($this);
        }

        return $this;
    }

    public function removeStreamerReport(StreamerReport $streamerReport): self
    {
        if ($this->streamerReport->contains($streamerReport)) {
            $this->streamerReport->removeElement($streamerReport);
            // set the owning side to null (unless already changed)
            if ($streamerReport->getStreamer() === $this) {
                $streamerReport->setStreamer(null);
            }
        }

        return $this;
    }

    public function addVod(Vod $vod): self
    {
        if (!$this->vod->contains($vod)) {
            $this->vod[] = $vod;
            $vod->setStreamer($this);
        }

        return $this;
    }

    public function removeVod(Vod $vod): self
    {
        if ($this->vod->contains($vod)) {
            $this->vod->removeElement($vod);
            // set the owning side to null (unless already changed)
            if ($vod->getStreamer() === $this) {
                $vod->setStreamer(null);
            }
        }

        return $this;
    }

    public function addSummoner(Summoner $summoner): self
    {
        if (!$this->summoner->contains($summoner)) {
            $this->summoner[] = $summoner;
            $summoner->setStreamer($this);
        }

        return $this;
    }

    public function removeSummoner(Summoner $summoner): self
    {
        if ($this->summoner->contains($summoner)) {
            $this->summoner->removeElement($summoner);
            // set the owning side to null (unless already changed)
            if ($summoner->getStreamer() === $this) {
                $summoner->setStreamer(null);
            }
        }

        return $this;
    }

    public function addSmurf(Smurf $smurf): self
    {
        if (!$this->smurf->contains($smurf)) {
            $this->smurf[] = $smurf;
            $smurf->setStreamer($this);
        }

        return $this;
    }

    public function removeSmurf(Smurf $smurf): self
    {
        if ($this->smurf->contains($smurf)) {
            $this->smurf->removeElement($smurf);
            // set the owning side to null (unless already changed)
            if ($smurf->getStreamer() === $this) {
                $smurf->setStreamer(null);
            }
        }

        return $this;
    }

    public function addReport(Report $report): self
    {
        if (!$this->report->contains($report)) {
            $this->report[] = $report;
            $report->setStreamer($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->report->contains($report)) {
            $this->report->removeElement($report);
            // set the owning side to null (unless already changed)
            if ($report->getStreamer() === $this) {
                $report->setStreamer(null);
            }
        }

        return $this;
    }

    public function addMatch(Match $match): self
    {
        if (!$this->match->contains($match)) {
            $this->match[] = $match;
            $match->setStreamer($this);
        }

        return $this;
    }

    public function removeMatch(Match $match): self
    {
        if ($this->match->contains($match)) {
            $this->match->removeElement($match);
            // set the owning side to null (unless already changed)
            if ($match->getStreamer() === $this) {
                $match->setStreamer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|OnlineTime[]
     */
    public function getOnlineTimes(): Collection
    {
        return $this->onlineTimes;
    }

    public function addOnlineTime(OnlineTime $onlineTime): self
    {
        if (!$this->onlineTimes->contains($onlineTime)) {
            $this->onlineTimes[] = $onlineTime;
            $onlineTime->setStreamer($this);
        }

        return $this;
    }

    public function removeOnlineTime(OnlineTime $onlineTime): self
    {
        if ($this->onlineTimes->contains($onlineTime)) {
            $this->onlineTimes->removeElement($onlineTime);
            // set the owning side to null (unless already changed)
            if ($onlineTime->getStreamer() === $this) {
                $onlineTime->setStreamer(null);
            }
        }

        return $this;
    }


}
