<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_spells")
 * @ORM\Entity(repositoryClass="App\Repository\SpellRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Spell
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Summoner", mappedBy="spell")
     */
    protected $summoner;

    /**
     * @ORM\OneToMany(targetEntity="CurrentMatch", mappedBy="p1_spell1", cascade={"remove"})
     */
    protected $currentMatch_p1_s1;

    /**
     * @ORM\OneToMany(targetEntity="CurrentMatch", mappedBy="p1_spell2", cascade={"remove"})
     */
    protected $currentMatch_p1_s2;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="p1_spell1", cascade={"remove"})
     */
    protected $match_p1_s1;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="p1_spell2", cascade={"remove"})
     */
    protected $match_p1_s2;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=150)
     */
    protected $image;


    /**
     * @ORM\Column(name="last_modified", type="datetime", nullable=false)
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
    public function getCurrentMatchP1S1()
    {
        return $this->currentMatch_p1_s1;
    }

    /**
     * @param mixed $currentMatch_p1_s1
     */
    public function setCurrentMatchP1S1($currentMatch_p1_s1): void
    {
        $this->currentMatch_p1_s1 = $currentMatch_p1_s1;
    }

    /**
     * @return mixed
     */
    public function getCurrentMatchP1S2()
    {
        return $this->currentMatch_p1_s2;
    }

    /**
     * @param mixed $currentMatch_p1_s2
     */
    public function setCurrentMatchP1S2($currentMatch_p1_s2): void
    {
        $this->currentMatch_p1_s2 = $currentMatch_p1_s2;
    }

    /**
     * @return mixed
     */
    public function getMatchP1S1()
    {
        return $this->match_p1_s1;
    }

    /**
     * @param mixed $match_p1_s1
     */
    public function setMatchP1S1($match_p1_s1): void
    {
        $this->match_p1_s1 = $match_p1_s1;
    }

    /**
     * @return mixed
     */
    public function getMatchP1S2()
    {
        return $this->match_p1_s2;
    }

    /**
     * @param mixed $match_p1_s2
     */
    public function setMatchP1S2($match_p1_s2): void
    {
        $this->match_p1_s2 = $match_p1_s2;
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
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
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
