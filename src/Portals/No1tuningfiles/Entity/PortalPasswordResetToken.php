<?php

namespace App\Portals\No1tuningfiles\Entity;

use App\Entity\Kunde;
use App\Portals\No1tuningfiles\Repository\PortalPasswordResetTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortalPasswordResetTokenRepository::class)]
#[ORM\Table(name: 'no1_portal_password_reset_token')]
class PortalPasswordResetToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Kunde $kunde = null;

    #[ORM\Column(length: 80)]
    private string $brandKey = 'no1tuningfiles';

    #[ORM\Column(length: 128)]
    private string $tokenHash = '';

    #[ORM\Column]
    private \DateTimeImmutable $requestedAt;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    public function __construct()
    {
        $this->requestedAt = new \DateTimeImmutable();
        $this->expiresAt = $this->requestedAt->modify('+2 hours');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKunde(): ?Kunde
    {
        return $this->kunde;
    }

    public function setKunde(?Kunde $kunde): static
    {
        $this->kunde = $kunde;

        return $this;
    }

    public function getBrandKey(): string
    {
        return $this->brandKey;
    }

    public function setBrandKey(string $brandKey): static
    {
        $this->brandKey = trim($brandKey);

        return $this;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(string $tokenHash): static
    {
        $this->tokenHash = trim($tokenHash);

        return $this;
    }

    public function getRequestedAt(): \DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeImmutable $requestedAt): static
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getUsedAt(): ?\DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function setUsedAt(?\DateTimeImmutable $usedAt): static
    {
        $this->usedAt = $usedAt;

        return $this;
    }
}
