<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ls_versions_all")
 * @ORM\Entity(repositoryClass="App\Repository\VersionAllRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class VersionAll
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=15)
     */
    protected $version;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=15)
     */
    protected $major;

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
    public function getMajor()
    {
        return $this->major;
    }

    /**
     * @param mixed $major
     */
    public function setMajor($major): void
    {
        $this->major = $major;
    }

    public function getModified(): ?\DateTimeInterface
    {
        return $this->modified;
    }


}
