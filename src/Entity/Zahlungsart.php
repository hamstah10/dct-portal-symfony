<?php

namespace App\Entity;

use App\Repository\ZahlungsartRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZahlungsartRepository::class)]
class Zahlungsart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apiLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dokuLink = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $zugangsdaten = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apiKeySandbox = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apiKeyLive = null;

    #[ORM\Column]
    private bool $aktiv = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name ?? ('Zahlungsart #' . $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    public function getApiLink(): ?string
    {
        return $this->apiLink;
    }

    public function setApiLink(?string $apiLink): static
    {
        $this->apiLink = $apiLink;

        return $this;
    }

    public function getDokuLink(): ?string
    {
        return $this->dokuLink;
    }

    public function setDokuLink(?string $dokuLink): static
    {
        $this->dokuLink = $dokuLink;

        return $this;
    }

    public function getZugangsdaten(): ?string
    {
        return $this->zugangsdaten;
    }

    public function setZugangsdaten(?string $zugangsdaten): static
    {
        $this->zugangsdaten = $zugangsdaten;

        return $this;
    }

    public function getApiKeySandbox(): ?string
    {
        return $this->apiKeySandbox;
    }

    public function setApiKeySandbox(?string $apiKeySandbox): static
    {
        $this->apiKeySandbox = $apiKeySandbox;

        return $this;
    }

    public function getApiKeyLive(): ?string
    {
        return $this->apiKeyLive;
    }

    public function setApiKeyLive(?string $apiKeyLive): static
    {
        $this->apiKeyLive = $apiKeyLive;

        return $this;
    }

    public function isAktiv(): bool
    {
        return $this->aktiv;
    }

    public function setAktiv(bool $aktiv): static
    {
        $this->aktiv = $aktiv;

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
}
