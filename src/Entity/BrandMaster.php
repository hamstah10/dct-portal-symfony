<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tx_chiptuningdatabase_brand_master')]
class BrandMaster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'image_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $imageMediaAsset = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_type', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleType $vehicleType = null;

    #[ORM\Column]
    private bool $active = true;

    private mixed $uploadedImage = null;

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('BrandMaster #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getImageMediaAsset(): ?MediaAsset
    {
        return $this->imageMediaAsset;
    }

    public function setImageMediaAsset(?MediaAsset $imageMediaAsset): static
    {
        $this->imageMediaAsset = $imageMediaAsset;

        return $this;
    }

    public function getResolvedImagePath(): ?string
    {
        $mediaAssetPath = trim((string) ($this->imageMediaAsset?->getFilePath() ?? ''));
        if ('' !== $mediaAssetPath) {
            return $mediaAssetPath;
        }

        $legacyPath = trim((string) ($this->image ?? ''));

        return '' !== $legacyPath ? $legacyPath : null;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getVehicleType(): ?VehicleType
    {
        return $this->vehicleType;
    }

    public function setVehicleType(?VehicleType $vehicleType): static
    {
        $this->vehicleType = $vehicleType;

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

    public function getUploadedImage(): mixed
    {
        return $this->uploadedImage;
    }

    public function setUploadedImage(mixed $uploadedImage): static
    {
        $this->uploadedImage = $uploadedImage;

        return $this;
    }
}
