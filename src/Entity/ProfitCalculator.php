<?php

namespace App\Entity;

use App\Repository\ProfitCalculatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfitCalculatorRepository::class)]
#[ORM\Table(name: 'tx_chiptuningprofitcalculator_domain_model_calculator')]
class ProfitCalculator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(name: 'vat_rate', type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $vatRate = '0.00';

    #[ORM\Column(name: 'purchase_price_include_vat')]
    private bool $purchasePriceIncludeVat = false;

    #[ORM\Column(name: 'end_price_include_vat')]
    private bool $endPriceIncludeVat = false;

    #[ORM\Column(name: 'options_template', length: 64, nullable: true)]
    private ?string $optionsTemplate = null;

    /**
     * @var Collection<int, ProfitCalculatorVehicleType>
     */
    #[ORM\OneToMany(mappedBy: 'calculator', targetEntity: ProfitCalculatorVehicleType::class)]
    private Collection $vehicleTypes;

    public function __construct()
    {
        $this->vehicleTypes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('ProfitCalculator #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getVatRate(): string
    {
        return $this->vatRate;
    }

    public function setVatRate(string $vatRate): static
    {
        $this->vatRate = $vatRate;

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

    public function getOptionsTemplate(): ?string
    {
        return $this->optionsTemplate;
    }

    public function setOptionsTemplate(?string $optionsTemplate): static
    {
        $this->optionsTemplate = $optionsTemplate;

        return $this;
    }

    /**
     * @return Collection<int, ProfitCalculatorVehicleType>
     */
    public function getVehicleTypes(): Collection
    {
        return $this->vehicleTypes;
    }

    public function addVehicleType(ProfitCalculatorVehicleType $vehicleType): static
    {
        if (!$this->vehicleTypes->contains($vehicleType)) {
            $this->vehicleTypes->add($vehicleType);
            $vehicleType->setCalculator($this);
        }

        return $this;
    }

    public function removeVehicleType(ProfitCalculatorVehicleType $vehicleType): static
    {
        if ($this->vehicleTypes->removeElement($vehicleType) && $vehicleType->getCalculator() === $this) {
            $vehicleType->setCalculator(null);
        }

        return $this;
    }
}
