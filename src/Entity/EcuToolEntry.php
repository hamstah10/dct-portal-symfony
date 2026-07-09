<?php

namespace App\Entity;

use App\Repository\EcuToolEntryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcuToolEntryRepository::class)]
#[ORM\Table(name: 'tfd_ecu_tool_entry')]
class EcuToolEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'toolEntries')]
    #[ORM\JoinColumn(name: 'ecu_id', referencedColumnName: 'uid', nullable: false, onDelete: 'CASCADE')]
    private ?Ecu $ecu = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'tool_manufacturer_id', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?ToolManufacturer $toolManufacturer = null;

    /**
     * @var Collection<int, ToolMethode>
     */
    #[ORM\ManyToMany(targetEntity: ToolMethode::class)]
    #[ORM\JoinTable(name: 'tfd_ecu_tool_entry_methode_mm')]
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
        return $this->toolManufacturer?->getName() ?? ('EcuToolEntry #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEcu(): ?Ecu
    {
        return $this->ecu;
    }

    public function setEcu(?Ecu $ecu): static
    {
        $this->ecu = $ecu;

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
