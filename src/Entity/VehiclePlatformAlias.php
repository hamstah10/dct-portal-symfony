<?php

namespace App\Entity;

use App\Repository\VehiclePlatformAliasRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehiclePlatformAliasRepository::class)]
class VehiclePlatformAlias
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $sourceBrand = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $sourceModel = null;

    #[ORM\Column(length: 150)]
    private ?string $aliasBrand = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $aliasModel = null;

    #[ORM\Column]
    private float $scoreWeight = 0.75;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name ?? ('Plattform-Alias #' . $this->id);
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

    public function getSourceBrand(): ?string
    {
        return $this->sourceBrand;
    }

    public function setSourceBrand(?string $sourceBrand): static
    {
        $this->sourceBrand = $sourceBrand;

        return $this;
    }

    public function getSourceModel(): ?string
    {
        return $this->sourceModel;
    }

    public function setSourceModel(?string $sourceModel): static
    {
        $this->sourceModel = $sourceModel;

        return $this;
    }

    public function getAliasBrand(): ?string
    {
        return $this->aliasBrand;
    }

    public function setAliasBrand(string $aliasBrand): static
    {
        $this->aliasBrand = $aliasBrand;

        return $this;
    }

    public function getAliasModel(): ?string
    {
        return $this->aliasModel;
    }

    public function setAliasModel(?string $aliasModel): static
    {
        $this->aliasModel = $aliasModel;

        return $this;
    }

    public function getScoreWeight(): float
    {
        return $this->scoreWeight;
    }

    public function setScoreWeight(float $scoreWeight): static
    {
        $this->scoreWeight = max(0.0, min(1.0, $scoreWeight));

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

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

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
