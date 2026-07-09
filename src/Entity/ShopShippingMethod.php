<?php

namespace App\Entity;

use App\Repository\ShopShippingMethodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopShippingMethodRepository::class)]
#[ORM\Table(name: 'tx_tuningtoolshop_domain_model_shippingmethod')]
class ShopShippingMethod implements ManualUidEntityInterface
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

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN)]
    private bool $isActive = false;

    #[ORM\Column(name: 'isActive', type: Types::BOOLEAN)]
    private bool $legacyIsActive = false;

    #[ORM\Column(name: 'sort_order')]
    private int $sortOrder = 0;

    #[ORM\Column(name: 'sortOrder')]
    private int $legacySortOrder = 0;

    #[ORM\Column(name: 'min_weight', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $minWeight = null;

    #[ORM\Column(name: 'max_weight', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $maxWeight = null;

    /**
     * @var Collection<int, ShopOrder>
     */
    #[ORM\OneToMany(mappedBy: 'shippingMethod', targetEntity: ShopOrder::class)]
    private Collection $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('Versand #' . $this->uid);
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

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): static
    {
        $this->provider = $provider;

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

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

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

    public function isLegacyIsActive(): bool
    {
        return $this->legacyIsActive;
    }

    public function setLegacyIsActive(bool $legacyIsActive): static
    {
        $this->legacyIsActive = $legacyIsActive;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getLegacySortOrder(): int
    {
        return $this->legacySortOrder;
    }

    public function setLegacySortOrder(int $legacySortOrder): static
    {
        $this->legacySortOrder = $legacySortOrder;

        return $this;
    }

    public function getMinWeight(): ?string
    {
        return $this->minWeight;
    }

    public function setMinWeight(?string $minWeight): static
    {
        $this->minWeight = $minWeight;

        return $this;
    }

    public function getMaxWeight(): ?string
    {
        return $this->maxWeight;
    }

    public function setMaxWeight(?string $maxWeight): static
    {
        $this->maxWeight = $maxWeight;

        return $this;
    }

    /**
     * @return Collection<int, ShopOrder>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(ShopOrder $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setShippingMethod($this);
        }

        return $this;
    }

    public function removeOrder(ShopOrder $order): static
    {
        if ($this->orders->removeElement($order) && $order->getShippingMethod() === $this) {
            $order->setShippingMethod(null);
        }

        return $this;
    }
}
