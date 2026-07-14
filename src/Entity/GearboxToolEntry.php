<?php

namespace App\Entity;

use App\Repository\GearboxToolEntryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GearboxToolEntryRepository::class)]
#[ORM\Table(name: 'tfd_gearbox_tool_entry')]
class GearboxToolEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'toolEntries')]
    #[ORM\JoinColumn(name: 'gearbox_id', referencedColumnName: 'uid', nullable: false, onDelete: 'CASCADE')]
    private ?Gearbox $gearbox = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'tool_manufacturer_id', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?ToolManufacturer $toolManufacturer = null;

    /**
     * @var Collection<int, ToolMethode>
     */
    #[ORM\ManyToMany(targetEntity: ToolMethode::class)]
    #[ORM\JoinTable(name: 'tfd_gearbox_tool_entry_methode_mm')]
    #[ORM\JoinColumn(name: 'entry_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'methode_id', referencedColumnName: 'uid')]
    private Collection $toolMethods;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    public function __construct()
    {
        $this->toolMethods = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->toolManufacturer?->getName() ?? ('GearboxToolEntry #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGearbox(): ?Gearbox
    {
        return $this->gearbox;
    }

    public function setGearbox(?Gearbox $gearbox): static
    {
        $this->gearbox = $gearbox;

        return $this;
    }

    public function getToolManufacturer(): ?ToolManufacturer
    {
        return $this->toolManufacturer;
    }

    public function setToolManufacturer(?ToolManufacturer $toolManufacturer): static
    {
        $this->toolManufacturer = $toolManufacturer;

        return $this;
    }

    /**
     * @return Collection<int, ToolMethode>
     */
    public function getToolMethods(): Collection
    {
        return $this->toolMethods;
    }

    public function addToolMethod(ToolMethode $toolMethod): static
    {
        if (!$this->toolMethods->contains($toolMethod)) {
            $this->toolMethods->add($toolMethod);
        }

        return $this;
    }

    public function removeToolMethod(ToolMethode $toolMethod): static
    {
        $this->toolMethods->removeElement($toolMethod);

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
