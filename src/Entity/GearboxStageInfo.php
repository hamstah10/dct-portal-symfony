<?php

namespace App\Entity;

use App\Repository\GearboxStageInfoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Erklärungstext je Stage-Spalte der DSG-Optionsmatrix ("Stages im Überblick")
 * inkl. Feature-Liste (enthalten / nicht enthalten).
 */
#[ORM\Entity(repositoryClass: GearboxStageInfoRepository::class)]
#[ORM\Table(name: 'tfd_gearbox_stage_info')]
class GearboxStageInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private int $sorting = 0;

    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    /**
     * @var Collection<int, GearboxStageInfoFeature>
     */
    #[ORM\OneToMany(mappedBy: 'stageInfo', targetEntity: GearboxStageInfoFeature::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sorting' => 'ASC', 'id' => 'ASC'])]
    private Collection $features;

    public function __construct()
    {
        $this->features = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('GearboxStageInfo #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): static
    {
        $this->sorting = $sorting;

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
     * @return Collection<int, GearboxStageInfoFeature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(GearboxStageInfoFeature $feature): static
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->setStageInfo($this);
        }

        return $this;
    }

    public function removeFeature(GearboxStageInfoFeature $feature): static
    {
        if ($this->features->removeElement($feature)) {
            if ($feature->getStageInfo() === $this) {
                $feature->setStageInfo(null);
            }
        }

        return $this;
    }
}
