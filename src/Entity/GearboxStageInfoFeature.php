<?php

namespace App\Entity;

use App\Repository\GearboxStageInfoFeatureRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Einzelnes Feature einer Stage-Info (enthalten oder nicht enthalten).
 */
#[ORM\Entity(repositoryClass: GearboxStageInfoFeatureRepository::class)]
#[ORM\Table(name: 'tfd_gearbox_stage_info_feature')]
class GearboxStageInfoFeature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'features')]
    #[ORM\JoinColumn(name: 'stage_info_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?GearboxStageInfo $stageInfo = null;

    #[ORM\Column(length: 255)]
    private string $label = '';

    #[ORM\Column(options: ['default' => false])]
    private bool $included = false;

    #[ORM\Column]
    private int $sorting = 0;

    public function __toString(): string
    {
        return '' !== $this->label ? $this->label : ('Feature #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStageInfo(): ?GearboxStageInfo
    {
        return $this->stageInfo;
    }

    public function setStageInfo(?GearboxStageInfo $stageInfo): static
    {
        $this->stageInfo = $stageInfo;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function isIncluded(): bool
    {
        return $this->included;
    }

    public function setIncluded(bool $included): static
    {
        $this->included = $included;

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
}
