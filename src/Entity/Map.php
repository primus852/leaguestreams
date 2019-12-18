<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_maps")
 * @ORM\Entity(repositoryClass="App\Repository\MapRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Map
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="map")
     */
    protected $match;

    /**
     * @ORM\OneToMany(targetEntity="CurrentMatch", mappedBy="map")
     */
    protected $currentMatch;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;


    /**
     * @ORM\Column(name="last_modified", type="datetime", nullable=false)
     */
    protected $modified;

    public function __construct()
    {
        $this->match = new ArrayCollection();
        $this->currentMatch = new ArrayCollection();
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

    public function addMatch(Match $match): self
    {
        if (!$this->match->contains($match)) {
            $this->match[] = $match;
            $match->setMap($this);
        }

        return $this;
    }

    public function removeMatch(Match $match): self
    {
        if ($this->match->contains($match)) {
            $this->match->removeElement($match);
            // set the owning side to null (unless already changed)
            if ($match->getMap() === $this) {
                $match->setMap(null);
            }
        }

        return $this;
    }

    public function addCurrentMatch(CurrentMatch $currentMatch): self
    {
        if (!$this->currentMatch->contains($currentMatch)) {
            $this->currentMatch[] = $currentMatch;
            $currentMatch->setMap($this);
        }

        return $this;
    }

    public function removeCurrentMatch(CurrentMatch $currentMatch): self
    {
        if ($this->currentMatch->contains($currentMatch)) {
            $this->currentMatch->removeElement($currentMatch);
            // set the owning side to null (unless already changed)
            if ($currentMatch->getMap() === $this) {
                $currentMatch->setMap(null);
            }
        }

        return $this;
    }



}
