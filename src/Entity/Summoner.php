<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_summoner")
 * @ORM\Entity(repositoryClass="App\Repository\SummonerRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Summoner
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $summonerId;

    /**
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="summoner")
     */
    protected $region;

    /**
     * @ORM\OneToMany(targetEntity="SummonerReport", mappedBy="summoner", cascade={"remove"})
     */
    protected $summonerReport;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="summoner", cascade={"remove"})
     */
    protected $match;

    /**
     * @ORM\OneToOne(targetEntity="CurrentMatch", mappedBy="summoner", cascade={"remove"})
     */
    protected $currentMatch;

    /**
     * @ORM\ManyToOne(targetEntity="Streamer", inversedBy="summoner")
     */
    protected $streamer;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $division;

    /**
     * @ORM\Column(type="integer")
     */
    protected $lp;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $league;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $accountId;


    /**
     * @ORM\ManyToMany(targetEntity="Spell", inversedBy="summoner", cascade={"remove"})
     * @ORM\JoinTable(name="spell_summoner")
     */
    protected $spell;

    /**
     * @ORM\Column(name="last_modified", type="datetime", nullable=false)
     */
    protected $modified;

    public function __construct()
    {
        $this->summonerReport = new ArrayCollection();
        $this->match = new ArrayCollection();
        $this->spell = new ArrayCollection();
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
    public function getSummonerId()
    {
        return $this->summonerId;
    }

    /**
     * @param mixed $summonerId
     */
    public function setSummonerId($summonerId): void
    {
        $this->summonerId = $summonerId;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     */
    public function setRegion($region): void
    {
        $this->region = $region;
    }

    /**
     * @return mixed
     */
    public function getSummonerReport()
    {
        return $this->summonerReport;
    }

    /**
     * @param mixed $summonerReport
     */
    public function setSummonerReport($summonerReport): void
    {
        $this->summonerReport = $summonerReport;
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
    public function getCurrentMatch()
    {
        return $this->currentMatch;
    }

    /**
     * @param mixed $currentMatch
     */
    public function setCurrentMatch($currentMatch): void
    {
        $this->currentMatch = $currentMatch;
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
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * @param mixed $division
     */
    public function setDivision($division): void
    {
        $this->division = $division;
    }

    /**
     * @return mixed
     */
    public function getLp()
    {
        return $this->lp;
    }

    /**
     * @param mixed $lp
     */
    public function setLp($lp): void
    {
        $this->lp = $lp;
    }

    /**
     * @return mixed
     */
    public function getLeague()
    {
        return $this->league;
    }

    /**
     * @param mixed $league
     */
    public function setLeague($league): void
    {
        $this->league = $league;
    }

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param mixed $accountId
     */
    public function setAccountId($accountId): void
    {
        $this->accountId = $accountId;
    }

    /**
     * @return mixed
     */
    public function getSpell()
    {
        return $this->spell;
    }

    /**
     * @param mixed $spell
     */
    public function setSpell($spell): void
    {
        $this->spell = $spell;
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

    public function addSummonerReport(SummonerReport $summonerReport): self
    {
        if (!$this->summonerReport->contains($summonerReport)) {
            $this->summonerReport[] = $summonerReport;
            $summonerReport->setSummoner($this);
        }

        return $this;
    }

    public function removeSummonerReport(SummonerReport $summonerReport): self
    {
        if ($this->summonerReport->contains($summonerReport)) {
            $this->summonerReport->removeElement($summonerReport);
            // set the owning side to null (unless already changed)
            if ($summonerReport->getSummoner() === $this) {
                $summonerReport->setSummoner(null);
            }
        }

        return $this;
    }

    public function addMatch(Match $match): self
    {
        if (!$this->match->contains($match)) {
            $this->match[] = $match;
            $match->setSummoner($this);
        }

        return $this;
    }

    public function removeMatch(Match $match): self
    {
        if ($this->match->contains($match)) {
            $this->match->removeElement($match);
            // set the owning side to null (unless already changed)
            if ($match->getSummoner() === $this) {
                $match->setSummoner(null);
            }
        }

        return $this;
    }

    public function addSpell(Spell $spell): self
    {
        if (!$this->spell->contains($spell)) {
            $this->spell[] = $spell;
        }

        return $this;
    }

    public function removeSpell(Spell $spell): self
    {
        if ($this->spell->contains($spell)) {
            $this->spell->removeElement($spell);
        }

        return $this;
    }


}
