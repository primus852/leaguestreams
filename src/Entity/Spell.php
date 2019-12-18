<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    public function __construct()
    {
        $this->summoner = new ArrayCollection();
        $this->currentMatch_p1_s1 = new ArrayCollection();
        $this->currentMatch_p1_s2 = new ArrayCollection();
        $this->match_p1_s1 = new ArrayCollection();
        $this->match_p1_s2 = new ArrayCollection();
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

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    public function addSummoner(Summoner $summoner): self
    {
        if (!$this->summoner->contains($summoner)) {
            $this->summoner[] = $summoner;
            $summoner->addSpell($this);
        }

        return $this;
    }

    public function removeSummoner(Summoner $summoner): self
    {
        if ($this->summoner->contains($summoner)) {
            $this->summoner->removeElement($summoner);
            $summoner->removeSpell($this);
        }

        return $this;
    }

    public function addCurrentMatchP1S1(CurrentMatch $currentMatchP1S1): self
    {
        if (!$this->currentMatch_p1_s1->contains($currentMatchP1S1)) {
            $this->currentMatch_p1_s1[] = $currentMatchP1S1;
            $currentMatchP1S1->setP1Spell1($this);
        }

        return $this;
    }

    public function removeCurrentMatchP1S1(CurrentMatch $currentMatchP1S1): self
    {
        if ($this->currentMatch_p1_s1->contains($currentMatchP1S1)) {
            $this->currentMatch_p1_s1->removeElement($currentMatchP1S1);
            // set the owning side to null (unless already changed)
            if ($currentMatchP1S1->getP1Spell1() === $this) {
                $currentMatchP1S1->setP1Spell1(null);
            }
        }

        return $this;
    }

    public function addCurrentMatchP1S2(CurrentMatch $currentMatchP1S2): self
    {
        if (!$this->currentMatch_p1_s2->contains($currentMatchP1S2)) {
            $this->currentMatch_p1_s2[] = $currentMatchP1S2;
            $currentMatchP1S2->setP1Spell2($this);
        }

        return $this;
    }

    public function removeCurrentMatchP1S2(CurrentMatch $currentMatchP1S2): self
    {
        if ($this->currentMatch_p1_s2->contains($currentMatchP1S2)) {
            $this->currentMatch_p1_s2->removeElement($currentMatchP1S2);
            // set the owning side to null (unless already changed)
            if ($currentMatchP1S2->getP1Spell2() === $this) {
                $currentMatchP1S2->setP1Spell2(null);
            }
        }

        return $this;
    }

    public function addMatchP1S1(Match $matchP1S1): self
    {
        if (!$this->match_p1_s1->contains($matchP1S1)) {
            $this->match_p1_s1[] = $matchP1S1;
            $matchP1S1->setP1Spell1($this);
        }

        return $this;
    }

    public function removeMatchP1S1(Match $matchP1S1): self
    {
        if ($this->match_p1_s1->contains($matchP1S1)) {
            $this->match_p1_s1->removeElement($matchP1S1);
            // set the owning side to null (unless already changed)
            if ($matchP1S1->getP1Spell1() === $this) {
                $matchP1S1->setP1Spell1(null);
            }
        }

        return $this;
    }

    public function addMatchP1S2(Match $matchP1S2): self
    {
        if (!$this->match_p1_s2->contains($matchP1S2)) {
            $this->match_p1_s2[] = $matchP1S2;
            $matchP1S2->setP1Spell2($this);
        }

        return $this;
    }

    public function removeMatchP1S2(Match $matchP1S2): self
    {
        if ($this->match_p1_s2->contains($matchP1S2)) {
            $this->match_p1_s2->removeElement($matchP1S2);
            // set the owning side to null (unless already changed)
            if ($matchP1S2->getP1Spell2() === $this) {
                $matchP1S2->setP1Spell2(null);
            }
        }

        return $this;
    }




}
