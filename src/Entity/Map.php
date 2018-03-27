<?php

namespace App\Entity;

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



}
