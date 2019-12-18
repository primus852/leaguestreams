<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_regions")
 * @ORM\Entity(repositoryClass="App\Repository\RegionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Region
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Smurf", mappedBy="region")
     */
    protected $smurf;

    /**
     * @ORM\OneToMany(targetEntity="Summoner", mappedBy="region")
     */
    protected $summoner;

    /**
     * @ORM\OneToMany(targetEntity="Report", mappedBy="region")
     */
    protected $report;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="region")
     */
    protected $match;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $short;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $long;

    /**
     * @ORM\Column(type="string", length=150)
     */
    protected $url;

    /**
     * @ORM\Column(type="integer")
     */
    protected $port;

    /**
     * @ORM\Column(name="last_modified", type="datetime", nullable=false)
     */
    protected $modified;

    public function __construct()
    {
        $this->smurf = new ArrayCollection();
        $this->summoner = new ArrayCollection();
        $this->report = new ArrayCollection();
        $this->match = new ArrayCollection();
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
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
    public function getShort()
    {
        return $this->short;
    }

    /**
     * @param mixed $short
     */
    public function setShort($short): void
    {
        $this->short = $short;
    }

    /**
     * @return mixed
     */
    public function getLong()
    {
        return $this->long;
    }

    /**
     * @param mixed $long
     */
    public function setLong($long): void
    {
        $this->long = $long;
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
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port): void
    {
        $this->port = $port;
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }

    public function addSmurf(Smurf $smurf): self
    {
        if (!$this->smurf->contains($smurf)) {
            $this->smurf[] = $smurf;
            $smurf->setRegion($this);
        }

        return $this;
    }

    public function removeSmurf(Smurf $smurf): self
    {
        if ($this->smurf->contains($smurf)) {
            $this->smurf->removeElement($smurf);
            // set the owning side to null (unless already changed)
            if ($smurf->getRegion() === $this) {
                $smurf->setRegion(null);
            }
        }

        return $this;
    }

    public function addSummoner(Summoner $summoner): self
    {
        if (!$this->summoner->contains($summoner)) {
            $this->summoner[] = $summoner;
            $summoner->setRegion($this);
        }

        return $this;
    }

    public function removeSummoner(Summoner $summoner): self
    {
        if ($this->summoner->contains($summoner)) {
            $this->summoner->removeElement($summoner);
            // set the owning side to null (unless already changed)
            if ($summoner->getRegion() === $this) {
                $summoner->setRegion(null);
            }
        }

        return $this;
    }

    public function addReport(Report $report): self
    {
        if (!$this->report->contains($report)) {
            $this->report[] = $report;
            $report->setRegion($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->report->contains($report)) {
            $this->report->removeElement($report);
            // set the owning side to null (unless already changed)
            if ($report->getRegion() === $this) {
                $report->setRegion(null);
            }
        }

        return $this;
    }

    public function addMatch(Match $match): self
    {
        if (!$this->match->contains($match)) {
            $this->match[] = $match;
            $match->setRegion($this);
        }

        return $this;
    }

    public function removeMatch(Match $match): self
    {
        if ($this->match->contains($match)) {
            $this->match->removeElement($match);
            // set the owning side to null (unless already changed)
            if ($match->getRegion() === $this) {
                $match->setRegion(null);
            }
        }

        return $this;
    }


}
