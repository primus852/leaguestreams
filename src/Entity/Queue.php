<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_queues")
 * @ORM\Entity(repositoryClass="App\Repository\QueueRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Queue
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
     * @ORM\Column(type="string", length=100)
     */
    protected $officialId;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="queue")
     */
    protected $match;

    /**
     * @ORM\OneToMany(targetEntity="CurrentMatch", mappedBy="queue")
     */
    protected $currentMatch;


    /**
     * @ORM\Column(name="last_modified", type="datetime", nullable=false)
     */
    protected $modified;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $note;

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
    public function getOfficialId()
    {
        return $this->officialId;
    }

    /**
     * @param mixed $officialId
     */
    public function setOfficialId($officialId): void
    {
        $this->officialId = $officialId;
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
            $match->setQueue($this);
        }

        return $this;
    }

    public function removeMatch(Match $match): self
    {
        if ($this->match->contains($match)) {
            $this->match->removeElement($match);
            // set the owning side to null (unless already changed)
            if ($match->getQueue() === $this) {
                $match->setQueue(null);
            }
        }

        return $this;
    }

    public function addCurrentMatch(CurrentMatch $currentMatch): self
    {
        if (!$this->currentMatch->contains($currentMatch)) {
            $this->currentMatch[] = $currentMatch;
            $currentMatch->setQueue($this);
        }

        return $this;
    }

    public function removeCurrentMatch(CurrentMatch $currentMatch): self
    {
        if ($this->currentMatch->contains($currentMatch)) {
            $this->currentMatch->removeElement($currentMatch);
            // set the owning side to null (unless already changed)
            if ($currentMatch->getQueue() === $this) {
                $currentMatch->setQueue(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }




}
