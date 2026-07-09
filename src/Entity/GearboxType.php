<?php

namespace App\Entity;

use App\Repository\GearboxTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GearboxTypeRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_gearboxtype')]
class GearboxType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $active = true;

    /**
     * @var Collection<int, Gearbox>
     */
    #[ORM\OneToMany(mappedBy: 'gearboxType', targetEntity: Gearbox::class)]
    private Collection $gearboxes;

    public function __construct()
    {
        $this->gearboxes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('GearboxType #' . ($this->id ?? 0));
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
     * @return Collection<int, Gearbox>
     */
    public function getGearboxes(): Collection
    {
        return $this->gearboxes;
    }
}
