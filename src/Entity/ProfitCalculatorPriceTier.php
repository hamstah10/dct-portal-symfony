<?php

namespace App\Entity;

use App\Repository\ProfitCalculatorPriceTierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfitCalculatorPriceTierRepository::class)]
#[ORM\Table(name: 'tx_chiptuningprofitcalculator_domain_model_pricetier')]
class ProfitCalculatorPriceTier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column]
    private int $credits = 0;

    #[ORM\Column(name: 'purchase_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $purchasePrice = '0.00';

    #[ORM\Column(name: 'price_per_file', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $pricePerFile = '0.00';

    #[ORM\Column(name: 'is_default_price')]
    private bool $isDefaultPrice = false;

    public function __toString(): string
    {
        return $this->credits > 0 ? ('Credits ' . $this->credits) : ('PriceTier #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCredits(): int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): static
    {
        $this->credits = max(0, $credits);

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

    public function getPricePerFile(): string
    {
        return $this->pricePerFile;
    }

    public function setPricePerFile(string $pricePerFile): static
    {
        $this->pricePerFile = $pricePerFile;

        return $this;
    }

    public function isDefaultPrice(): bool
    {
        return $this->isDefaultPrice;
    }

    public function setIsDefaultPrice(bool $isDefaultPrice): static
    {
        $this->isDefaultPrice = $isDefaultPrice;

        return $this;
    }
}
