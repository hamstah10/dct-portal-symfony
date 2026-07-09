<?php

namespace App\Entity;

use App\Repository\ProfitCalculatorOptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfitCalculatorOptionRepository::class)]
#[ORM\Table(name: 'tx_chiptuningprofitcalculator_domain_model_option')]
class ProfitCalculatorOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'options')]
    #[ORM\JoinColumn(name: 'vehicle_type', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?ProfitCalculatorVehicleType $vehicleType = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column(name: 'purchase_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $purchasePrice = '0.00';

    #[ORM\Column(name: 'end_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $endPrice = '0.00';

    #[ORM\Column(name: 'end_price_include_vat')]
    private bool $endPriceIncludeVat = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'icon_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $iconMediaAsset = null;

    #[ORM\Column(name: 'image', length: 255, nullable: true)]
    private ?string $image = null;

    private mixed $uploadedImage = null;

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('ProfitOption #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicleType(): ?ProfitCalculatorVehicleType
    {
        return $this->vehicleType;
    }

    public function setVehicleType(?ProfitCalculatorVehicleType $vehicleType): static
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
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

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getPurchasePrice(): string
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(string $purchasePrice): static
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    public function getEndPrice(): string
    {
        return $this->endPrice;
    }

    public function setEndPrice(string $endPrice): static
    {
        $this->endPrice = $endPrice;

        return $this;
    }

    public function isEndPriceIncludeVat(): bool
    {
        return $this->endPriceIncludeVat;
    }

    public function setEndPriceIncludeVat(bool $endPriceIncludeVat): static
    {
        $this->endPriceIncludeVat = $endPriceIncludeVat;

        return $this;
    }

    public function getIconMediaAsset(): ?MediaAsset
    {
        return $this->iconMediaAsset;
    }

    public function setIconMediaAsset(?MediaAsset $iconMediaAsset): static
    {
        $this->iconMediaAsset = $iconMediaAsset;

        return $this;
    }

    public function getResolvedIconPath(): ?string
    {
        $mediaAssetPath = trim((string) ($this->iconMediaAsset?->getFilePath() ?? ''));

        return '' !== $mediaAssetPath ? $mediaAssetPath : null;
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
