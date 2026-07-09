<?php

namespace Tfd\PartnerWizardBundle\Entity;

use App\Entity\VehicleType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tfd_partner_wizard_lead')]
class PartnerWizardLead
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartnerWizardClient::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?PartnerWizardClient $client = null;

    #[ORM\Column(length: 120)]
    private string $name = '';

    #[ORM\Column(length: 180)]
    private string $email = '';

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $goal = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $experience = null;

    #[ORM\ManyToMany(targetEntity: VehicleType::class)]
    #[ORM\JoinTable(name: 'tfd_partner_wizard_lead_vehicletype')]
    #[ORM\JoinColumn(name: 'lead_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'vehicle_type_id', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    private Collection $vehicleCategories;

    #[ORM\ManyToOne(targetEntity: VehicleType::class)]
    #[ORM\JoinColumn(name: 'main_vehicle_type_id', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleType $mainVehicleType = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $currentTool = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $desiredToolLevel = null;

    #[ORM\Column(type: 'json')]
    private array $services = [];

    #[ORM\Column]
    private int $monthlyVehicleCount = 0;

    #[ORM\Column(length: 40)]
    private string $recommendationStatus = 'tool_required';

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $recommendedPackageKey = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $recommendedPackageTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $recommendedPackageText = null;

    #[ORM\Column(type: 'json')]
    private array $recommendedTools = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $toolDirection = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $nextStep = null;

    #[ORM\Column]
    private int $estimatedMonthlyRevenue = 0;

    #[ORM\Column(type: 'json')]
    private array $payload = [];

    #[ORM\Column(length: 40)]
    private string $status = 'new';

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $source = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $originDomain = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->vehicleCategories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return trim($this->name . ' / ' . $this->email);
    }

    public function getId(): ?int { return $this->id; }
    public function getClient(): ?PartnerWizardClient { return $this->client; }
    public function setClient(?PartnerWizardClient $client): self { $this->client = $client; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function getCompany(): ?string { return $this->company; }
    public function setCompany(?string $company): self { $this->company = $company; return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }
    public function getWebsite(): ?string { return $this->website; }
    public function setWebsite(?string $website): self { $this->website = $website; return $this; }
    public function getGoal(): ?string { return $this->goal; }
    public function setGoal(?string $goal): self { $this->goal = $goal; return $this; }
    public function getExperience(): ?string { return $this->experience; }
    public function setExperience(?string $experience): self { $this->experience = $experience; return $this; }
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

    public function getMainVehicleType(): ?VehicleType { return $this->mainVehicleType; }
    public function setMainVehicleType(?VehicleType $mainVehicleType): self { $this->mainVehicleType = $mainVehicleType; return $this; }
    public function getCurrentTool(): ?string { return $this->currentTool; }
    public function setCurrentTool(?string $currentTool): self { $this->currentTool = $currentTool; return $this; }
    public function getDesiredToolLevel(): ?string { return $this->desiredToolLevel; }
    public function setDesiredToolLevel(?string $desiredToolLevel): self { $this->desiredToolLevel = $desiredToolLevel; return $this; }
    public function getServices(): array { return $this->services; }
    public function setServices(array $services): self { $this->services = $services; return $this; }
    public function getMonthlyVehicleCount(): int { return $this->monthlyVehicleCount; }
    public function setMonthlyVehicleCount(int $monthlyVehicleCount): self { $this->monthlyVehicleCount = $monthlyVehicleCount; return $this; }
    public function getRecommendationStatus(): string { return $this->recommendationStatus; }
    public function setRecommendationStatus(string $recommendationStatus): self { $this->recommendationStatus = $recommendationStatus; return $this; }
    public function getRecommendedPackageKey(): ?string { return $this->recommendedPackageKey; }
    public function setRecommendedPackageKey(?string $recommendedPackageKey): self { $this->recommendedPackageKey = $recommendedPackageKey; return $this; }
    public function getRecommendedPackageTitle(): ?string { return $this->recommendedPackageTitle; }
    public function setRecommendedPackageTitle(?string $recommendedPackageTitle): self { $this->recommendedPackageTitle = $recommendedPackageTitle; return $this; }
    public function getRecommendedPackageText(): ?string { return $this->recommendedPackageText; }
    public function setRecommendedPackageText(?string $recommendedPackageText): self { $this->recommendedPackageText = $recommendedPackageText; return $this; }
    public function getRecommendedTools(): array { return $this->recommendedTools; }
    public function setRecommendedTools(array $recommendedTools): self { $this->recommendedTools = $recommendedTools; return $this; }
    public function getToolDirection(): ?string { return $this->toolDirection; }
    public function setToolDirection(?string $toolDirection): self { $this->toolDirection = $toolDirection; return $this; }
    public function getNextStep(): ?string { return $this->nextStep; }
    public function setNextStep(?string $nextStep): self { $this->nextStep = $nextStep; return $this; }
    public function getEstimatedMonthlyRevenue(): int { return $this->estimatedMonthlyRevenue; }
    public function setEstimatedMonthlyRevenue(int $estimatedMonthlyRevenue): self { $this->estimatedMonthlyRevenue = $estimatedMonthlyRevenue; return $this; }
    public function getPayload(): array { return $this->payload; }
    public function setPayload(array $payload): self { $this->payload = $payload; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; $this->updatedAt = new \DateTimeImmutable(); return $this; }
    public function getSource(): ?string { return $this->source; }
    public function setSource(?string $source): self { $this->source = $source; return $this; }
    public function getOriginDomain(): ?string { return $this->originDomain; }
    public function setOriginDomain(?string $originDomain): self { $this->originDomain = $originDomain; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
}
