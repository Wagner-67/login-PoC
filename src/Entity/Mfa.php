<?php

namespace App\Entity;

use App\Repository\MfaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MfaRepository::class)]
class Mfa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'user_id', length: 255)]
    private ?string $userId = null;

    #[ORM\Column(name: 'finger_print', length: 255)]
    private ?string $fingerPrint = null;

    #[ORM\Column]
    private ?bool $suspicious = null;

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

    public function getFingerPrint(): ?string
    {
        return $this->fingerPrint;
    }

    public function setFingerPrint(string $fingerPrint): static
    {
        $this->fingerPrint = $fingerPrint;
        return $this;
    }

    public function isSuspicious(): ?bool
    {
        return $this->suspicious;
    }

    public function setSuspicious(bool $suspicious): static
    {
        $this->suspicious = $suspicious;
        return $this;
    }
}
