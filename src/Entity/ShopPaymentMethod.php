<?php

namespace App\Entity;

use App\Repository\ShopPaymentMethodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopPaymentMethodRepository::class)]
#[ORM\Table(name: 'tx_tuningtoolshop_domain_model_paymentmethod')]
class ShopPaymentMethod implements ManualUidEntityInterface
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

    #[ORM\Column(name: 'sort_order')]
    private int $sortOrder = 0;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN)]
    private bool $isActive = false;

    #[ORM\Column(name: 'handler_class', length: 255, nullable: true)]
    private ?string $handlerClass = null;

    #[ORM\Column(name: 'client_id', length: 255, nullable: true)]
    private ?string $clientId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secret = null;

    #[ORM\Column(name: 'api_url', length: 2048, nullable: true)]
    private ?string $apiUrl = null;

    #[ORM\Column(name: 'is_sandbox', type: Types::BOOLEAN)]
    private bool $isSandbox = false;

    /**
     * @var Collection<int, ShopOrder>
     */
    #[ORM\OneToMany(mappedBy: 'paymentMethod', targetEntity: ShopOrder::class)]
    private Collection $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('Zahlungsart #' . $this->uid);
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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

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

    public function getHandlerClass(): ?string
    {
        return $this->handlerClass;
    }

    public function setHandlerClass(?string $handlerClass): static
    {
        $this->handlerClass = $handlerClass;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): static
    {
        $this->secret = $secret;

        return $this;
    }

    public function getApiUrl(): ?string
    {
        return $this->apiUrl;
    }

    public function setApiUrl(?string $apiUrl): static
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    public function isSandbox(): bool
    {
        return $this->isSandbox;
    }

    public function setIsSandbox(bool $isSandbox): static
    {
        $this->isSandbox = $isSandbox;

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
            $order->setPaymentMethod($this);
        }

        return $this;
    }

    public function removeOrder(ShopOrder $order): static
    {
        if ($this->orders->removeElement($order) && $order->getPaymentMethod() === $this) {
            $order->setPaymentMethod(null);
        }

        return $this;
    }
}
