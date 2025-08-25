<?php

namespace App\Entity;

use App\Repository\TwoFactorAuthRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TwoFactorAuthRepository::class)]
class TwoFactorAuth
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'user_id', length: 255)]
    private ?string $userId = null;

    #[ORM\Column(name: 'last_login')]
    private ?\DateTime $lastLogin = null;

    #[ORM\Column(name: 'login_count')]
    private ?int $loginCount = null;

    #[ORM\Column(name: 'last_2fa')]
    private ?\DateTime $last2fa = null;

    #[ORM\Column(name: 'has_to_verify')]
    private ?bool $HasToVerify = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTime $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLoginCount(): ?int
    {
        return $this->loginCount;
    }

    public function setLoginCount(int $loginCount): static
    {
        $this->loginCount = $loginCount;

        return $this;
    }

    public function getLast2fa(): ?\DateTime
    {
        return $this->last2fa;
    }

    public function setLast2fa(\DateTime $last2fa): static
    {
        $this->last2fa = $last2fa;

        return $this;
    }

    public function hasToVerify(): ?bool
    {
        return $this->HasToVerify;
    }

    public function setHasToVerify(bool $hasToVerify): static
    {
        $this->HasToVerify = $hasToVerify;

        return $this;
    }
}
