<?php

namespace Tfd\PartnerWizardBundle\Entity;

use App\Entity\VehicleType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tfd_partner_wizard_combination')]
class PartnerWizardCombination
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartnerWizardClient::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PartnerWizardClient $client = null;

    #[ORM\Column(length: 140)]
    private string $name = '';

    #[ORM\ManyToMany(targetEntity: VehicleType::class)]
    #[ORM\JoinTable(name: 'tfd_partner_wizard_combination_vehicletype')]
    #[ORM\JoinColumn(name: 'combination_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'vehicle_type_id', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    private Collection $vehicleCategories;

    #[ORM\Column(type: 'json')]
    private array $services = [];

    #[ORM\ManyToMany(targetEntity: PartnerWizardTool::class)]
    #[ORM\JoinTable(name: 'tfd_partner_wizard_combination_tool')]
    #[ORM\JoinColumn(name: 'combination_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'tool_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $tools;

    #[ORM\Column]
    private int $priority = 100;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    public function __construct()
    {
        $this->tools = new ArrayCollection();
        $this->vehicleCategories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int { return $this->id; }
    public function getClient(): ?PartnerWizardClient { return $this->client; }
    public function setClient(?PartnerWizardClient $client): self { $this->client = $client; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    /**
     * @return Collection<int, VehicleType>
     */
    public function getVehicleCategories(): Collection { return $this->vehicleCategories; }

    public function addVehicleCategory(VehicleType $vehicleType): self
    {
        if (!$this->vehicleCategories->contains($vehicleType)) {
            $this->vehicleCategories->add($vehicleType);
        }

        return $this;
    }

    public function removeVehicleCategory(VehicleType $vehicleType): self
    {
        $this->vehicleCategories->removeElement($vehicleType);

        return $this;
    }
    public function getServices(): array { return $this->services; }
    public function setServices(array $services): self { $this->services = $services; return $this; }
    public function getPriority(): int { return $this->priority; }
    public function setPriority(int $priority): self { $this->priority = $priority; return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): self { $this->active = $active; return $this; }

    /**
     * @return Collection<int, PartnerWizardTool>
     */
    public function getTools(): Collection { return $this->tools; }

    public function addTool(PartnerWizardTool $tool): self
    {
        if (!$this->tools->contains($tool)) {
            $this->tools->add($tool);
        }

        return $this;
    }

    public function removeTool(PartnerWizardTool $tool): self
    {
        $this->tools->removeElement($tool);

        return $this;
    }
}
