<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_champions")
 * @ORM\Entity(repositoryClass="App\Repository\ChampionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Champion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="champion")
     */
    protected $match;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="enemyChampion")
     */
    protected $matchEnemy;

    /**
     * @ORM\OneToMany(targetEntity="CurrentMatch", mappedBy="champion")
     */
    protected $currentMatch;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=150)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=150)
     */
    protected $image;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $champKey;

    /**
     * @ORM\Column(type="text")
     */
    protected $blurb;

    /**
     * @ORM\Column(type="text")
     */
    protected $lore;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $spellPassiveImage;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $spellPassiveName;

    /**
     * @ORM\Column(type="text")
     */
    protected $spellPassiveDescription;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $spellQImage;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $spellQName;

    /**
     * @ORM\Column(type="text")
     */
    protected $spellQDescription;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $spellWImage;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $spellWName;

    /**
     * @ORM\Column(type="text")
     */
    protected $spellWDescription;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $spellEImage;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $spellEName;

    /**
     * @ORM\Column(type="text")
     */
    protected $spellEDescription;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $spellRImage;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $spellRName;

    /**
     * @ORM\Column(type="text")
     */
    protected $spellRDescription;

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
    public function getMatchEnemy()
    {
        return $this->matchEnemy;
    }

    /**
     * @param mixed $matchEnemy
     */
    public function setMatchEnemy($matchEnemy): void
    {
        $this->matchEnemy = $matchEnemy;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
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
    public function getChampKey()
    {
        return $this->champKey;
    }

    /**
     * @param mixed $champKey
     */
    public function setChampKey($champKey): void
    {
        $this->champKey = $champKey;
    }

    /**
     * @return mixed
     */
    public function getBlurb()
    {
        return $this->blurb;
    }

    /**
     * @param mixed $blurb
     */
    public function setBlurb($blurb): void
    {
        $this->blurb = $blurb;
    }

    /**
     * @return mixed
     */
    public function getLore()
    {
        return $this->lore;
    }

    /**
     * @param mixed $lore
     */
    public function setLore($lore): void
    {
        $this->lore = $lore;
    }

    /**
     * @return mixed
     */
    public function getSpellPassiveImage()
    {
        return $this->spellPassiveImage;
    }

    /**
     * @param mixed $spellPassiveImage
     */
    public function setSpellPassiveImage($spellPassiveImage): void
    {
        $this->spellPassiveImage = $spellPassiveImage;
    }

    /**
     * @return mixed
     */
    public function getSpellPassiveName()
    {
        return $this->spellPassiveName;
    }

    /**
     * @param mixed $spellPassiveName
     */
    public function setSpellPassiveName($spellPassiveName): void
    {
        $this->spellPassiveName = $spellPassiveName;
    }

    /**
     * @return mixed
     */
    public function getSpellPassiveDescription()
    {
        return $this->spellPassiveDescription;
    }

    /**
     * @param mixed $spellPassiveDescription
     */
    public function setSpellPassiveDescription($spellPassiveDescription): void
    {
        $this->spellPassiveDescription = $spellPassiveDescription;
    }

    /**
     * @return mixed
     */
    public function getSpellQImage()
    {
        return $this->spellQImage;
    }

    /**
     * @param mixed $spellQImage
     */
    public function setSpellQImage($spellQImage): void
    {
        $this->spellQImage = $spellQImage;
    }

    /**
     * @return mixed
     */
    public function getSpellQName()
    {
        return $this->spellQName;
    }

    /**
     * @param mixed $spellQName
     */
    public function setSpellQName($spellQName): void
    {
        $this->spellQName = $spellQName;
    }

    /**
     * @return mixed
     */
    public function getSpellQDescription()
    {
        return $this->spellQDescription;
    }

    /**
     * @param mixed $spellQDescription
     */
    public function setSpellQDescription($spellQDescription): void
    {
        $this->spellQDescription = $spellQDescription;
    }

    /**
     * @return mixed
     */
    public function getSpellWImage()
    {
        return $this->spellWImage;
    }

    /**
     * @param mixed $spellWImage
     */
    public function setSpellWImage($spellWImage): void
    {
        $this->spellWImage = $spellWImage;
    }

    /**
     * @return mixed
     */
    public function getSpellWName()
    {
        return $this->spellWName;
    }

    /**
     * @param mixed $spellWName
     */
    public function setSpellWName($spellWName): void
    {
        $this->spellWName = $spellWName;
    }

    /**
     * @return mixed
     */
    public function getSpellWDescription()
    {
        return $this->spellWDescription;
    }

    /**
     * @param mixed $spellWDescription
     */
    public function setSpellWDescription($spellWDescription): void
    {
        $this->spellWDescription = $spellWDescription;
    }

    /**
     * @return mixed
     */
    public function getSpellEImage()
    {
        return $this->spellEImage;
    }

    /**
     * @param mixed $spellEImage
     */
    public function setSpellEImage($spellEImage): void
    {
        $this->spellEImage = $spellEImage;
    }

    /**
     * @return mixed
     */
    public function getSpellEName()
    {
        return $this->spellEName;
    }

    /**
     * @param mixed $spellEName
     */
    public function setSpellEName($spellEName): void
    {
        $this->spellEName = $spellEName;
    }

    /**
     * @return mixed
     */
    public function getSpellEDescription()
    {
        return $this->spellEDescription;
    }

    /**
     * @param mixed $spellEDescription
     */
    public function setSpellEDescription($spellEDescription): void
    {
        $this->spellEDescription = $spellEDescription;
    }

    /**
     * @return mixed
     */
    public function getSpellRImage()
    {
        return $this->spellRImage;
    }

    /**
     * @param mixed $spellRImage
     */
    public function setSpellRImage($spellRImage): void
    {
        $this->spellRImage = $spellRImage;
    }

    /**
     * @return mixed
     */
    public function getSpellRName()
    {
        return $this->spellRName;
    }

    /**
     * @param mixed $spellRName
     */
    public function setSpellRName($spellRName): void
    {
        $this->spellRName = $spellRName;
    }

    /**
     * @return mixed
     */
    public function getSpellRDescription()
    {
        return $this->spellRDescription;
    }

    /**
     * @param mixed $spellRDescription
     */
    public function setSpellRDescription($spellRDescription): void
    {
        $this->spellRDescription = $spellRDescription;
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
