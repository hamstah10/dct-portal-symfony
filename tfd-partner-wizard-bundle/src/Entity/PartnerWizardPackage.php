<?php

namespace Tfd\PartnerWizardBundle\Entity;

use App\Entity\VehicleType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tfd_partner_wizard_package')]
class PartnerWizardPackage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartnerWizardClient::class, inversedBy: 'packages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?PartnerWizardClient $client = null;

    #[ORM\Column(length: 80)]
    private string $packageKey = '';

    #[ORM\Column(length: 140)]
    private string $title = '';

    #[ORM\Column(type: 'text')]
    private string $description = '';

    #[ORM\Column(length: 40)]
    private string $requiredStatus = 'any'; // any, tool_required, ready

    #[ORM\Column]
    private int $minVehiclesPerMonth = 0;

    #[ORM\Column]
    private int $maxVehiclesPerMonth = 999;

    #[ORM\Column(type: 'json')]
    private array $requiredExperience = [];

    #[ORM\Column(type: 'json')]
    private array $requiredGoals = [];

    #[ORM\ManyToMany(targetEntity: VehicleType::class)]
    #[ORM\JoinTable(name: 'tfd_partner_wizard_package_vehicletype')]
    #[ORM\JoinColumn(name: 'package_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'vehicle_type_id', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    private Collection $recommendedVehicleCategories;

    #[ORM\Column(type: 'json')]
    private array $features = [];

    #[ORM\Column(length: 120)]
    private string $ctaLabel = 'Anfrage senden';

    #[ORM\Column]
    private int $priority = 100;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    public function __construct()
    {
        $this->recommendedVehicleCategories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): ?int { return $this->id; }
    public function getClient(): ?PartnerWizardClient { return $this->client; }
    public function setClient(?PartnerWizardClient $client): self { $this->client = $client; return $this; }
    public function getPackageKey(): string { return $this->packageKey; }
    public function setPackageKey(string $packageKey): self { $this->packageKey = $packageKey; return $this; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }
    public function getRequiredStatus(): string { return $this->requiredStatus; }
    public function setRequiredStatus(string $requiredStatus): self { $this->requiredStatus = $requiredStatus; return $this; }
    public function getMinVehiclesPerMonth(): int { return $this->minVehiclesPerMonth; }
    public function setMinVehiclesPerMonth(int $minVehiclesPerMonth): self { $this->minVehiclesPerMonth = $minVehiclesPerMonth; return $this; }
    public function getMaxVehiclesPerMonth(): int { return $this->maxVehiclesPerMonth; }
    public function setMaxVehiclesPerMonth(int $maxVehiclesPerMonth): self { $this->maxVehiclesPerMonth = $maxVehiclesPerMonth; return $this; }
    public function getRequiredExperience(): array { return $this->requiredExperience; }
    public function setRequiredExperience(array $requiredExperience): self { $this->requiredExperience = $requiredExperience; return $this; }
    public function getRequiredGoals(): array { return $this->requiredGoals; }
    public function setRequiredGoals(array $requiredGoals): self { $this->requiredGoals = $requiredGoals; return $this; }
    /**
     * @return Collection<int, VehicleType>
     */
    public function getRecommendedVehicleCategories(): Collection { return $this->recommendedVehicleCategories; }

    public function addRecommendedVehicleCategory(VehicleType $vehicleType): self
    {
        if (!$this->recommendedVehicleCategories->contains($vehicleType)) {
            $this->recommendedVehicleCategories->add($vehicleType);
        }

        return $this;
    }

    public function removeRecommendedVehicleCategory(VehicleType $vehicleType): self
    {
        $this->recommendedVehicleCategories->removeElement($vehicleType);

        return $this;
    }
    public function getFeatures(): array { return $this->features; }
    public function setFeatures(array $features): self { $this->features = $features; return $this; }
    public function getCtaLabel(): string { return $this->ctaLabel; }
    public function setCtaLabel(string $ctaLabel): self { $this->ctaLabel = $ctaLabel; return $this; }
    public function getPriority(): int { return $this->priority; }
    public function setPriority(int $priority): self { $this->priority = $priority; return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): self { $this->active = $active; return $this; }
}
