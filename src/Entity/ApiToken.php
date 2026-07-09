<?php

namespace App\Entity;

use App\Repository\ApiTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
class ApiToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $applicationName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $applicationLink = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $apiToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $publicKey = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secretKey = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $tokenIdentifier = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\ManyToOne(inversedBy: 'apiTokens')]
    #[ORM\JoinColumn(name: 'vehicle_data_context', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataContext $vehicleDataContext = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Kunde $kunde = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $gueltigBis = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->applicationName ?? ('API Token #' . $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplicationName(): ?string
    {
        return $this->applicationName;
    }

    public function setApplicationName(string $applicationName): static
    {
        $this->applicationName = $applicationName;

        return $this;
    }

    public function getApplicationLink(): ?string
    {
        return $this->applicationLink;
    }

    public function setApplicationLink(?string $applicationLink): static
    {
        $this->applicationLink = $applicationLink;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): static
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(?string $publicKey): static
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function setSecretKey(?string $secretKey): static
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    public function getTokenIdentifier(): ?string
    {
        return $this->tokenIdentifier;
    }

    public function setTokenIdentifier(string $tokenIdentifier): static
    {
        $this->tokenIdentifier = trim($tokenIdentifier);

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getVehicleDataContext(): ?VehicleDataContext
    {
        return $this->vehicleDataContext;
    }

    public function setVehicleDataContext(?VehicleDataContext $vehicleDataContext): static
    {
        $this->vehicleDataContext = $vehicleDataContext;

        return $this;
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

    public function getGueltigBis(): ?\DateTimeImmutable
    {
        return $this->gueltigBis;
    }

    public function setGueltigBis(?\DateTimeImmutable $gueltigBis): static
    {
        $this->gueltigBis = $gueltigBis;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isGueltig(?\DateTimeImmutable $reference = null): bool
    {
        $reference ??= new \DateTimeImmutable();

        return null === $this->gueltigBis || $this->gueltigBis >= $reference;
    }
}
