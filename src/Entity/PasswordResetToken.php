<?php

namespace App\Entity;

use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PasswordResetTokenRepository::class)]
class PasswordResetToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: "refresh_tokens", length: 255)]
    private ?string $refreshTokens = null;

    #[ORM\Column(name: "user_id", length: 255)]
    private ?string $userId = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $timestamp = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $expiresAt = null;

    #[ORM\Column]
    private bool $used = false;

    // -------------------
    // GETTER / SETTER
    // -------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRefreshTokens(): ?string
    {
        return $this->refreshTokens;
    }

    public function setRefreshTokens(string $refreshTokens): static
    {
        $this->refreshTokens = $refreshTokens;
        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function getTimestamp(): ?\DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTime $timestamp): static
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTime $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function setUsed(bool $used): static
    {
        $this->used = $used;
        return $this;
    }
}