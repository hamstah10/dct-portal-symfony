<?php

namespace App\Entity;

use App\Repository\FormulaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Formel mit Variablen und Wenn-Dann-Regeln (Formeln & Logik im Superadmin).
 * Regeln werden der Reihe nach geprüft, die erste passende gewinnt; leere Bedingung = Standardfall.
 */
#[ORM\Entity(repositoryClass: FormulaRepository::class)]
#[ORM\Table(name: 'formula')]
#[ORM\Index(name: 'IDX_FORMULA_AREA', columns: ['area'])]
class Formula
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'formula_key', length: 120, unique: true)]
    private string $key = '';

    #[ORM\Column(length: 160)]
    private string $name = '';

    #[ORM\Column(length: 120)]
    private string $area = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $unit = null;

    /**
     * @var list<array{name: string, label: string, default: float|int|string|null}>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $variables = [];

    /**
     * @var list<array{label: string, condition: string, expression: string}>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $rules = [];

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;

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

    public function getArea(): string
    {
        return $this->area;
    }

    public function setArea(string $area): static
    {
        $this->area = $area;

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

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return list<array{name: string, label: string, default: float|int|string|null}>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param list<array{name: string, label: string, default: float|int|string|null}> $variables
     */
    public function setVariables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * @return list<array{label: string, condition: string, expression: string}>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param list<array{label: string, condition: string, expression: string}> $rules
     */
    public function setRules(array $rules): static
    {
        $this->rules = $rules;

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

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
