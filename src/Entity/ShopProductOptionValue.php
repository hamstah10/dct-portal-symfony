<?php

namespace App\Entity;

use App\Repository\ShopProductOptionValueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopProductOptionValueRepository::class)]
#[ORM\Table(name: 'tx_tuningtoolshop_domain_model_productoptionvalue')]
class ShopProductOptionValue implements ManualUidEntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private int $uid = 0;

    #[ORM\Column]
    private int $pid = 0;

    #[ORM\Column]
    private int $tstamp = 0;

    #[ORM\Column]
    private int $crdate = 0;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $deleted = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $hidden = false;

    #[ORM\Column]
    private int $sorting = 0;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(name: 'price_modifier', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $priceModifier = '0.00';

    #[ORM\Column(name: 'special_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $specialPrice = '0.00';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: ShopProductOption::class, inversedBy: 'optionValues')]
    #[ORM\JoinColumn(name: 'product_option', referencedColumnName: 'uid', nullable: true)]
    private ?ShopProductOption $productOption = null;

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('Option Wert #' . $this->uid);
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setUid(int $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function setPid(int $pid): static
    {
        $this->pid = $pid;

        return $this;
    }

    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    public function setTstamp(int $tstamp): static
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    public function getCrdate(): int
    {
        return $this->crdate;
    }

    public function setCrdate(int $crdate): static
    {
        $this->crdate = $crdate;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): static
    {
        $this->sorting = $sorting;

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

    public function getPriceModifier(): string
    {
        return $this->priceModifier;
    }

    public function setPriceModifier(string $priceModifier): static
    {
        $this->priceModifier = $priceModifier;

        return $this;
    }

    public function getSpecialPrice(): string
    {
        return $this->specialPrice;
    }

    public function setSpecialPrice(string $specialPrice): static
    {
        $this->specialPrice = $specialPrice;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getProductOption(): ?ShopProductOption
    {
        return $this->productOption;
    }

    public function setProductOption(?ShopProductOption $productOption): static
    {
        $this->productOption = $productOption;

        return $this;
    }
}
