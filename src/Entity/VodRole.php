<?php

namespace App\Entity;

use App\Repository\VodRoleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VodRoleRepository::class)
 */
class VodRole
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $role;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $streamerName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $championName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $enemyChampionName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $gameStart;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gameLink;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $gamePatch;

    /**
     * @ORM\Column(type="integer")
     */
    private $gameLength;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isWin;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $playerLeague;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $championKey;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $enemyChampionKey;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getStreamerName(): ?string
    {
        return $this->streamerName;
    }

    public function setStreamerName(string $streamerName): self
    {
        $this->streamerName = $streamerName;

        return $this;
    }

    public function getChampionName(): ?string
    {
        return $this->championName;
    }

    public function setChampionName(string $championName): self
    {
        $this->championName = $championName;

        return $this;
    }

    public function getEnemyChampionName(): ?string
    {
        return $this->enemyChampionName;
    }

    public function setEnemyChampionName(string $enemyChampionName): self
    {
        $this->enemyChampionName = $enemyChampionName;

        return $this;
    }

    public function getGameStart(): ?\DateTimeInterface
    {
        return $this->gameStart;
    }

    public function setGameStart(\DateTimeInterface $gameStart): self
    {
        $this->gameStart = $gameStart;

        return $this;
    }

    public function getGameLink(): ?string
    {
        return $this->gameLink;
    }

    public function setGameLink(string $gameLink): self
    {
        $this->gameLink = $gameLink;

        return $this;
    }

    public function getGamePatch(): ?string
    {
        return $this->gamePatch;
    }

    public function setGamePatch(string $gamePatch): self
    {
        $this->gamePatch = $gamePatch;

        return $this;
    }

    public function getGameLength(): ?int
    {
        return $this->gameLength;
    }

    public function setGameLength(int $gameLength): self
    {
        $this->gameLength = $gameLength;

        return $this;
    }

    public function getIsWin(): ?bool
    {
        return $this->isWin;
    }

    public function setIsWin(bool $isWin): self
    {
        $this->isWin = $isWin;

        return $this;
    }

    public function getPlayerLeague(): ?string
    {
        return $this->playerLeague;
    }

    public function setPlayerLeague(string $playerLeague): self
    {
        $this->playerLeague = $playerLeague;

        return $this;
    }

    public function getChampionKey(): ?string
    {
        return $this->championKey;
    }

    public function setChampionKey(string $championKey): self
    {
        $this->championKey = $championKey;

        return $this;
    }

    public function getEnemyChampionKey(): ?string
    {
        return $this->enemyChampionKey;
    }

    public function setEnemyChampionKey(string $enemyChampionKey): self
    {
        $this->enemyChampionKey = $enemyChampionKey;

        return $this;
    }
}
