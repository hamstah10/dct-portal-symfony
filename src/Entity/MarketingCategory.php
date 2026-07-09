<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MarketingCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarketingCategoryRepository::class)]
class MarketingCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    /**
     * @var Collection<int, MarketingAdSize>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: MarketingAdSize::class)]
    private Collection $adSizes;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $icon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uxIcon = null;

    public function __construct()
    {
        $this->adSizes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? ('Kategorie #' . $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = trim($title);

        return $this;
    }

    /**
     * @return Collection<int, MarketingAdSize>
     */
    public function getAdSizes(): Collection
    {
        return $this->adSizes;
    }

    public function addAdSize(MarketingAdSize $adSize): static
    {
        if (!$this->adSizes->contains($adSize)) {
            $this->adSizes->add($adSize);
            $adSize->setCategory($this);
        }

        return $this;
    }

    public function removeAdSize(MarketingAdSize $adSize): static
    {
        if ($this->adSizes->removeElement($adSize)) {
            if ($adSize->getCategory() === $this) {
                $adSize->setCategory(null);
            }
        }

        return $this;
    }

    public function getIcon(): ?MediaAsset
    {
        return $this->icon;
    }

    public function setIcon(?MediaAsset $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getUxIcon(): ?string
    {
        return $this->uxIcon;
    }

    public function setUxIcon(?string $uxIcon): static
    {
        $this->uxIcon = null !== $uxIcon ? trim($uxIcon) : null;

        return $this;
    }
}
