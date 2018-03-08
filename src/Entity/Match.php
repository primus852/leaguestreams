<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_matches")
 * @ORM\Entity(repositoryClass="App\Repository\MatchRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Match
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
     * @ORM\Column(type="integer")
     */
    protected $team;

    /**
     * @ORM\ManyToOne(targetEntity="Streamer", inversedBy="match")
     * @ORM\JoinColumn(name="streamer", referencedColumnName="id")
     */
    protected $streamer;

    /**
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="match")
     * @ORM\JoinColumn(name="region", referencedColumnName="id")
     */
    protected $region;

    /**
     * @ORM\ManyToOne(targetEntity="Champion", inversedBy="match")
     * @ORM\JoinColumn(name="champion", referencedColumnName="id")
     */
    protected $champion;

    /**
     * @ORM\ManyToOne(targetEntity="Champion", inversedBy="matchEnemy")
     */
    protected $enemyChampion;

    /**
     * @ORM\ManyToOne(targetEntity="Summoner", inversedBy="match")
     * @ORM\JoinColumn(name="summoner", referencedColumnName="id")
     */
    protected $summoner;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $crawled;

    /**
     * @ORM\ManyToOne(targetEntity="Map", inversedBy="match")
     * @ORM\JoinColumn(name="map", referencedColumnName="id")
     */
    protected $map;

    /**
     * @ORM\ManyToOne(targetEntity="Queue", inversedBy="match")
     */
    protected $queue;

    /**
     * @ORM\Column(type="string", length=25)
     */
    protected $lane;

    /**
     * @ORM\Column(type="string", length=25)
     */
    protected $role;

    /**
     * @ORM\Column(type="integer")
     */
    protected $length;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $type;

    /**
     * @ORM\Column(type="integer")
     */
    protected $win;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $gameCreation;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $gameVersion;

    /**
     * @ORM\ManyToOne(targetEntity="Spell", inversedBy="match_p1_s1")
     */
    protected $p1_spell1;

    /**
     * @ORM\ManyToOne(targetEntity="Spell", inversedBy="match_p1_s2")
     */
    protected $p1_spell2;

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
    public function getEnemyChampion()
    {
        return $this->enemyChampion;
    }

    /**
     * @param mixed $enemyChampion
     */
    public function setEnemyChampion($enemyChampion): void
    {
        $this->enemyChampion = $enemyChampion;
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
    public function getCrawled()
    {
        return $this->crawled;
    }

    /**
     * @param mixed $crawled
     */
    public function setCrawled($crawled): void
    {
        $this->crawled = $crawled;
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
    public function getLane()
    {
        return $this->lane;
    }

    /**
     * @param mixed $lane
     */
    public function setLane($lane): void
    {
        $this->lane = $lane;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role): void
    {
        $this->role = $role;
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
    public function getWin()
    {
        return $this->win;
    }

    /**
     * @param mixed $win
     */
    public function setWin($win): void
    {
        $this->win = $win;
    }

    /**
     * @return mixed
     */
    public function getGameCreation()
    {
        return $this->gameCreation;
    }

    /**
     * @param mixed $gameCreation
     */
    public function setGameCreation($gameCreation): void
    {
        $this->gameCreation = $gameCreation;
    }

    /**
     * @return mixed
     */
    public function getGameVersion()
    {
        return $this->gameVersion;
    }

    /**
     * @param mixed $gameVersion
     */
    public function setGameVersion($gameVersion): void
    {
        $this->gameVersion = $gameVersion;
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
