<?php

namespace App\Entity;

use App\Repository\GearboxStageOptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Infos/Leistungen je Stage für ein Getriebemodell (z.B. DQ200 Stage 2: "600 Nm").
 * Zellwerte: "✓" = enthalten, Text = konkreter Wert (z.B. "280 Nm"), NULL = nicht enthalten.
 */
#[ORM\Entity(repositoryClass: GearboxStageOptionRepository::class)]
#[ORM\Table(name: 'tfd_gearbox_stage_option')]
class GearboxStageOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'gearbox_name', length: 100)]
    private string $gearboxName = '';

    #[ORM\Column(length: 150)]
    private string $category = '';

    #[ORM\Column(name: 'category_description', type: Types::TEXT, nullable: true)]
    private ?string $categoryDescription = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(name: 'name_en', length: 255)]
    private string $nameEn = '';

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fahrbarkeit = null;

    #[ORM\Column(name: 'stage1', length: 100, nullable: true)]
    private ?string $stage1 = null;

    #[ORM\Column(name: 'stage2', length: 100, nullable: true)]
    private ?string $stage2 = null;

    #[ORM\Column(name: 'stage2_plus', length: 100, nullable: true)]
    private ?string $stage2Plus = null;

    #[ORM\Column(name: 'stage3', length: 100, nullable: true)]
    private ?string $stage3 = null;

    #[ORM\Column(name: 'stage4', length: 100, nullable: true)]
    private ?string $stage4 = null;

    #[ORM\Column]
    private int $sorting = 0;

    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    public function __toString(): string
    {
        return '' !== $this->name
            ? sprintf('%s – %s', $this->gearboxName, $this->name)
            : ('GearboxStageOption #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGearboxName(): string
    {
        return $this->gearboxName;
    }

    public function setGearboxName(string $gearboxName): static
    {
        $this->gearboxName = $gearboxName;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCategoryDescription(): ?string
    {
        return $this->categoryDescription;
    }

    public function setCategoryDescription(?string $categoryDescription): static
    {
        $this->categoryDescription = $categoryDescription;

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

    public function getNameEn(): string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): static
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    public function getFahrbarkeit(): ?string
    {
        return $this->fahrbarkeit;
    }

    public function setFahrbarkeit(?string $fahrbarkeit): static
    {
        $this->fahrbarkeit = $fahrbarkeit;

        return $this;
    }

    public function getStage1(): ?string
    {
        return $this->stage1;
    }

    public function setStage1(?string $stage1): static
    {
        $this->stage1 = $stage1;

        return $this;
    }

    public function getStage2(): ?string
    {
        return $this->stage2;
    }

    public function setStage2(?string $stage2): static
    {
        $this->stage2 = $stage2;

        return $this;
    }

    public function getStage2Plus(): ?string
    {
        return $this->stage2Plus;
    }

    public function setStage2Plus(?string $stage2Plus): static
    {
        $this->stage2Plus = $stage2Plus;

        return $this;
    }

    public function getStage3(): ?string
    {
        return $this->stage3;
    }

    public function setStage3(?string $stage3): static
    {
        $this->stage3 = $stage3;

        return $this;
    }

    public function getStage4(): ?string
    {
        return $this->stage4;
    }

    public function setStage4(?string $stage4): static
    {
        $this->stage4 = $stage4;

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
}
