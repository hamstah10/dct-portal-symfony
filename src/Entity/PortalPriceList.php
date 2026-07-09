<?php

namespace App\Entity;

use App\Repository\PortalPriceListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortalPriceListRepository::class)]
#[ORM\Table(name: 'portal_price_list')]
#[ORM\UniqueConstraint(name: 'uniq_portal_price_list_code', columns: ['code'])]
class PortalPriceList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $code = '';

    #[ORM\Column(length: 190)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $active = true;

    /**
     * @var Collection<int, PortalPriceListItem>
     */
    #[ORM\OneToMany(mappedBy: 'priceList', targetEntity: PortalPriceListItem::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['section' => 'ASC', 'category' => 'ASC', 'sortOrder' => 'ASC', 'id' => 'ASC'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('Preisliste #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    /**
     * @return Collection<int, PortalPriceListItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(PortalPriceListItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setPriceList($this);
        }

        return $this;
    }

    public function removeItem(PortalPriceListItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getPriceList() === $this) {
                $item->setPriceList(null);
            }
        }

        return $this;
    }
}
