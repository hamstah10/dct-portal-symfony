<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CreditCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreditCategoryRepository::class)]
class CreditCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    /**
     * @var Collection<int, CreditPackage>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: CreditPackage::class, orphanRemoval: true)]
    private Collection $packages;

    public function __construct()
    {
        $this->packages = new ArrayCollection();
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
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, CreditPackage>
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(CreditPackage $package): static
    {
        if (!$this->packages->contains($package)) {
            $this->packages->add($package);
            $package->setCategory($this);
        }

        return $this;
    }

    public function removePackage(CreditPackage $package): static
    {
        if ($this->packages->removeElement($package)) {
            if ($package->getCategory() === $this) {
                $package->setCategory(null);
            }
        }

        return $this;
    }
}

