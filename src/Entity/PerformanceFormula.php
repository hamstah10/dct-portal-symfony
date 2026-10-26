<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'performance_formula')]
class PerformanceFormula
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $name = '';

    #[ORM\Column(length: 255)]
    private string $label = '';

    #[ORM\Column(type: 'text')]
    private string $formula = '';

    #[ORM\Column(length: 100)]
    private string $resultUnit = '';

    #[ORM\Column(type: 'json')]
    private array $requiredFields = [];

    #[ORM\Column]
    private int $sorting = 0;

    #[ORM\Column]
    private bool $active = true;

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

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getFormula(): string
    {
        return $this->formula;
    }

    public function setFormula(string $formula): static
    {
        $this->formula = $formula;
        return $this;
    }

    public function getResultUnit(): string
    {
        return $this->resultUnit;
    }

    public function setResultUnit(string $resultUnit): static
    {
        $this->resultUnit = $resultUnit;
        return $this;
    }

    public function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    public function setRequiredFields(array $requiredFields): static
    {
        $this->requiredFields = $requiredFields;
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
