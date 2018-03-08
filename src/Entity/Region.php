<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_regions")
 * @ORM\Entity(repositoryClass="App\Repository\RegionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Region
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Smurf", mappedBy="region")
     */
    protected $smurf;

    /**
     * @ORM\OneToMany(targetEntity="Summoner", mappedBy="region")
     */
    protected $summoner;

    /**
     * @ORM\OneToMany(targetEntity="Report", mappedBy="region")
     */
    protected $report;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="region")
     */
    protected $match;

    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $short;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $long;

    /**
     * @ORM\Column(type="string", length=150)
     */
    protected $url;

    /**
     * @ORM\Column(type="integer")
     */
    protected $port;

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
}
