<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'knowledge_entry')]
class KnowledgeEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    #[ORM\Column(length: 100)]
    private string $category = '';

    #[ORM\Column(type: Types::JSON)]
    private array $tags = [];

    #[ORM\ManyToMany(targetEntity: VehicleEngine::class)]
    #[ORM\JoinTable(
        name: 'knowledge_entry_vehicle_engine',
        joinColumns: [new ORM\JoinColumn(name: 'knowledge_entry_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'vehicle_engine_uid', referencedColumnName: 'uid')],
    )]
    private Collection $vehicleEngines;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private int $usefulness = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->vehicleEngines = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
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

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): static
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return Collection<int, VehicleEngine>
     */
    public function getVehicleEngines(): Collection
    {
        return $this->vehicleEngines;
    }

    public function addVehicleEngine(VehicleEngine $engine): static
    {
        if (!$this->vehicleEngines->contains($engine)) {
            $this->vehicleEngines->add($engine);
        }
        return $this;
    }

    public function removeVehicleEngine(VehicleEngine $engine): static
    {
        $this->vehicleEngines->removeElement($engine);
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

    public function getUsefulness(): int
    {
        return $this->usefulness;
    }

    public function setUsefulness(int $usefulness): static
    {
        $this->usefulness = max(0, min(100, $usefulness));
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
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
}
