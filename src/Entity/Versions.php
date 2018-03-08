<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_versions")
 * @ORM\Entity(repositoryClass="App\Repository\VersionsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Versions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $version;

    /**
     * @ORM\Column(type="string", length=150)
     */
    protected $cdn;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $champion;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $profileicon;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $item;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $map;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $mastery;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $spell;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $rune;

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
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version): void
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getCdn()
    {
        return $this->cdn;
    }

    /**
     * @param mixed $cdn
     */
    public function setCdn($cdn): void
    {
        $this->cdn = $cdn;
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
    public function getProfileicon()
    {
        return $this->profileicon;
    }

    /**
     * @param mixed $profileicon
     */
    public function setProfileicon($profileicon): void
    {
        $this->profileicon = $profileicon;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item): void
    {
        $this->item = $item;
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
    public function getMastery()
    {
        return $this->mastery;
    }

    /**
     * @param mixed $mastery
     */
    public function setMastery($mastery): void
    {
        $this->mastery = $mastery;
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
    public function getRune()
    {
        return $this->rune;
    }

    /**
     * @param mixed $rune
     */
    public function setRune($rune): void
    {
        $this->rune = $rune;
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
