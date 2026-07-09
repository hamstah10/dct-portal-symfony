<?php

namespace App\Entity;

use App\Repository\SuperadminRememberedVehicleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuperadminRememberedVehicleRepository::class)]
#[ORM\Table(name: 'superadmin_remembered_vehicle')]
#[ORM\UniqueConstraint(name: 'UNIQ_SUPERADMIN_REMEMBERED_ENGINE', columns: ['engine_id'])]
#[ORM\Index(name: 'IDX_SUPERADMIN_REMEMBERED_UPDATED_AT', columns: ['updated_at'])]
class SuperadminRememberedVehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $engineId = 0;

    #[ORM\Column(length: 190)]
    private string $label = '';

    #[ORM\Column(length: 255)]
    private string $subline = '';

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $selection = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEngineId(): int
    {
        return $this->engineId;
    }

    public function setEngineId(int $engineId): static
    {
        $this->engineId = $engineId;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = mb_substr($label, 0, 190);

        return $this;
    }

    public function getSubline(): string
    {
        return $this->subline;
    }

    public function setSubline(string $subline): static
    {
        $this->subline = mb_substr($subline, 0, 255);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSelection(): array
    {
        return $this->selection;
    }

    /**
     * @param array<string, mixed> $selection
     */
    public function setSelection(array $selection): static
    {
        $this->selection = $selection;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
