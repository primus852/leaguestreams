<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_current_match")
 * @ORM\Entity(repositoryClass="App\Repository\CurrentMatchRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CurrentMatch
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="bigint")
     */
    protected $matchId;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isPlaying;

    /**
     * @ORM\Column(type="integer")
     */
    protected $team;

    /**
     * @ORM\ManyToOne(targetEntity="Champion", inversedBy="currentMatch")
     */
    protected $champion;

    /**
     * @ORM\ManyToOne(targetEntity="Map", inversedBy="currentMatch")
     */
    protected $map;

    /**
     * @ORM\OneToOne(targetEntity="Summoner", inversedBy="currentMatch")
     */
    protected $summoner;

    /**
     * @ORM\ManyToOne(targetEntity="Queue", inversedBy="currentMatch")
     */
    protected $queue;

    /**
     * @ORM\ManyToOne(targetEntity="Spell", inversedBy="currentMatch_p1_s1")
     */
    protected $p1_spell1;

    /**
     * @ORM\ManyToOne(targetEntity="Spell", inversedBy="currentMatch_p1_s2")
     */
    protected $p1_spell2;

    /**
     * @ORM\Column(type="integer")
     */
    protected $length;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $type;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $mode;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $runes;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $masteries;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $perks;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $modified;

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
    public function getMatchId()
    {
        return $this->matchId;
    }

    /**
     * @param mixed $matchId
     */
    public function setMatchId($matchId): void
    {
        $this->matchId = $matchId;
    }

    /**
     * @return mixed
     */
    public function getIsPlaying()
    {
        return $this->isPlaying;
    }

    /**
     * @param mixed $isPlaying
     */
    public function setIsPlaying($isPlaying): void
    {
        $this->isPlaying = $isPlaying;
    }

    /**
     * @return mixed
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param mixed $team
     */
    public function setTeam($team): void
    {
        $this->team = $team;
    }

    /**
     * @return mixed
     */
    public function getChampion()
    {
        return $this->champion;
    }

    /**
     * @param mixed $champion
     */
    public function setChampion($champion): void
    {
        $this->champion = $champion;
    }

    /**
     * @return mixed
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @param mixed $map
     */
    public function setMap($map): void
    {
        $this->map = $map;
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
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param mixed $queue
     */
    public function setQueue($queue): void
    {
        $this->queue = $queue;
    }

    /**
     * @return mixed
     */
    public function getP1Spell1()
    {
        return $this->p1_spell1;
    }

    /**
     * @param mixed $p1_spell1
     */
    public function setP1Spell1($p1_spell1): void
    {
        $this->p1_spell1 = $p1_spell1;
    }

    /**
     * @return mixed
     */
    public function getP1Spell2()
    {
        return $this->p1_spell2;
    }

    /**
     * @param mixed $p1_spell2
     */
    public function setP1Spell2($p1_spell2): void
    {
        $this->p1_spell2 = $p1_spell2;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param mixed $mode
     */
    public function setMode($mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return mixed
     */
    public function getRunes()
    {
        return $this->runes;
    }

    /**
     * @param mixed $runes
     */
    public function setRunes($runes): void
    {
        $this->runes = $runes;
    }

    /**
     * @return mixed
     */
    public function getMasteries()
    {
        return $this->masteries;
    }

    /**
     * @param mixed $masteries
     */
    public function setMasteries($masteries): void
    {
        $this->masteries = $masteries;
    }

    /**
     * @return mixed
     */
    public function getPerks()
    {
        return $this->perks;
    }

    /**
     * @param mixed $perks
     */
    public function setPerks($perks): void
    {
        $this->perks = $perks;
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




}
