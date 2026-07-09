<?php

namespace App\Entity;

use App\Repository\ProfitCalculatorVehicleTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfitCalculatorVehicleTypeRepository::class)]
#[ORM\Table(name: 'tx_chiptuningprofitcalculator_domain_model_vehicletype')]
class ProfitCalculatorVehicleType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'vehicleTypes')]
    #[ORM\JoinColumn(name: 'calculator', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?ProfitCalculator $calculator = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $identifier = null;

    #[ORM\Column(name: 'sorting')]
    private int $sorting = 0;

    #[ORM\Column(name: 'end_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $endPrice = '0.00';

    #[ORM\Column(name: 'purchase_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $purchasePrice = '0.00';

    #[ORM\Column(name: 'purchase_price_include_vat')]
    private bool $purchasePriceIncludeVat = false;

    #[ORM\Column(name: 'end_price_include_vat')]
    private bool $endPriceIncludeVat = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'icon_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $iconMediaAsset = null;

    #[ORM\Column(name: 'image', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(name: 'icon', length: 255, nullable: true)]
    private ?string $icon = null;

    private mixed $uploadedImage = null;

    #[ORM\Column(name: 'options')]
    private int $optionsCount = 0;

    /**
     * @var Collection<int, ProfitCalculatorOption>
     */
    #[ORM\OneToMany(mappedBy: 'vehicleType', targetEntity: ProfitCalculatorOption::class)]
    private Collection $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('ProfitVehicleType #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCalculator(): ?ProfitCalculator
    {
        return $this->calculator;
    }

    public function setCalculator(?ProfitCalculator $calculator): static
    {
        $this->calculator = $calculator;

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

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): static
    {
        $this->sorting = max(0, $sorting);

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

    public function getPurchasePrice(): string
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(string $purchasePrice): static
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    public function isPurchasePriceIncludeVat(): bool
    {
        return $this->purchasePriceIncludeVat;
    }

    public function setPurchasePriceIncludeVat(bool $purchasePriceIncludeVat): static
    {
        $this->purchasePriceIncludeVat = $purchasePriceIncludeVat;

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

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

    public function getUploadedImage(): mixed
    {
        return $this->uploadedImage;
    }

    public function setUploadedImage(mixed $uploadedImage): static
    {
        $this->uploadedImage = $uploadedImage;

        return $this;
    }

    public function getOptionsCount(): int
    {
        return $this->optionsCount;
    }

    public function setOptionsCount(int $optionsCount): static
    {
        $this->optionsCount = max(0, $optionsCount);

        return $this;
    }

    /**
     * @return Collection<int, ProfitCalculatorOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(ProfitCalculatorOption $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
            $option->setVehicleType($this);
        }

        return $this;
    }

    public function removeOption(ProfitCalculatorOption $option): static
    {
        if ($this->options->removeElement($option) && $option->getVehicleType() === $this) {
            $option->setVehicleType(null);
        }

        return $this;
    }
}
