<?php

namespace Tfd\PartnerWizardBundle\Entity;

use App\Entity\ToolManufacturer;
use App\Entity\VehicleType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tfd_partner_wizard_client')]
class PartnerWizardClient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name = 'TFD';

    #[ORM\Column(length: 80, unique: true)]
    private string $clientId = 'tfd';

    #[ORM\Column(length: 180)]
    private string $headline = 'Chiptuning Händler werden';

    #[ORM\Column(type: 'text')]
    private string $introText = 'Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.';

    #[ORM\Column(length: 20)]
    private string $primaryColor = '#b4e600';

    #[ORM\Column(length: 20)]
    private string $theme = 'dark';

    #[ORM\Column(type: 'boolean')]
    private bool $toolRequired = true;

    #[ORM\Column]
    private int $defaultVehiclePrice = 399;

    #[ORM\Column(type: 'json')]
    private array $allowedDomains = [];

    #[ORM\ManyToMany(targetEntity: VehicleType::class)]
    #[ORM\JoinTable(name: 'tfd_partner_wizard_client_vehicletype')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'vehicle_type_id', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    private Collection $enabledVehicleCategories;

    /**
     * @var Collection<int, ToolManufacturer>
     */
    #[ORM\ManyToMany(targetEntity: ToolManufacturer::class)]
    #[ORM\JoinTable(name: 'tfd_partner_wizard_client_toolmanufacturer')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'tool_manufacturer_id', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    private Collection $enabledCurrentTools;

    #[ORM\Column(type: 'json')]
    private array $enabledServices = [
        'stage1', 'stage2', 'eco', 'adblue', 'dpf', 'agr', 'vmax', 'pops', 'hardcut', 'dsg', 'diagnose', 'coding'
    ];

    #[ORM\Column(type: 'json')]
    private array $customLabels = [];

    #[ORM\Column(type: 'text')]
    private string $privacyNotice = 'Mit dem Absenden erklärst du dich einverstanden, dass wir deine Angaben zur Bearbeitung der Anfrage speichern und dich kontaktieren.';

    #[ORM\Column(type: 'text')]
    private string $toolDisclaimer = 'Die Tool-Empfehlung ist eine erste Orientierung. Vor dem Kauf müssen Fahrzeuge, Steuergeräte und Protokolle konkret geprüft werden.';

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $notificationEmail = null;

    #[ORM\Column(type: 'boolean')]
    private bool $sendNotificationEmail = true;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: PartnerWizardTool::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $tools;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: PartnerWizardPackage::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $packages;

    public function __construct()
    {
        $this->tools = new ArrayCollection();
        $this->packages = new ArrayCollection();
        $this->enabledVehicleCategories = new ArrayCollection();
        $this->enabledCurrentTools = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name . ' (' . $this->clientId . ')';
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getClientId(): string { return $this->clientId; }
    public function setClientId(string $clientId): self { $this->clientId = $clientId; return $this; }
    public function getHeadline(): string { return $this->headline; }
    public function setHeadline(string $headline): self { $this->headline = $headline; return $this; }
    public function getIntroText(): string { return $this->introText; }
    public function setIntroText(string $introText): self { $this->introText = $introText; return $this; }
    public function getPrimaryColor(): string { return $this->primaryColor; }
    public function setPrimaryColor(string $primaryColor): self { $this->primaryColor = $primaryColor; return $this; }
    public function getTheme(): string { return $this->theme; }
    public function setTheme(string $theme): self { $this->theme = $theme; return $this; }
    public function isToolRequired(): bool { return $this->toolRequired; }
    public function setToolRequired(bool $toolRequired): self { $this->toolRequired = $toolRequired; return $this; }
    public function getDefaultVehiclePrice(): int { return $this->defaultVehiclePrice; }
    public function setDefaultVehiclePrice(int $defaultVehiclePrice): self { $this->defaultVehiclePrice = $defaultVehiclePrice; return $this; }
    public function getAllowedDomains(): array { return $this->allowedDomains; }
    public function setAllowedDomains(array $allowedDomains): self { $this->allowedDomains = $allowedDomains; return $this; }
    /**
     * @return Collection<int, VehicleType>
     */
    public function getEnabledVehicleCategories(): Collection { return $this->enabledVehicleCategories; }

    public function addEnabledVehicleCategory(VehicleType $vehicleType): self
    {
        if (!$this->enabledVehicleCategories->contains($vehicleType)) {
            $this->enabledVehicleCategories->add($vehicleType);
        }

        return $this;
    }

    public function removeEnabledVehicleCategory(VehicleType $vehicleType): self
    {
        $this->enabledVehicleCategories->removeElement($vehicleType);

        return $this;
    }
    /**
     * @return Collection<int, ToolManufacturer>
     */
    public function getEnabledCurrentTools(): Collection { return $this->enabledCurrentTools; }

    public function addEnabledCurrentTool(ToolManufacturer $toolManufacturer): self
    {
        if (!$this->enabledCurrentTools->contains($toolManufacturer)) {
            $this->enabledCurrentTools->add($toolManufacturer);
        }

        return $this;
    }

    public function removeEnabledCurrentTool(ToolManufacturer $toolManufacturer): self
    {
        $this->enabledCurrentTools->removeElement($toolManufacturer);

        return $this;
    }
    public function getEnabledServices(): array { return $this->enabledServices; }
    public function setEnabledServices(array $enabledServices): self { $this->enabledServices = $enabledServices; return $this; }
    public function getCustomLabels(): array { return $this->customLabels; }
    public function setCustomLabels(array $customLabels): self { $this->customLabels = $customLabels; return $this; }
    public function getPrivacyNotice(): string { return $this->privacyNotice; }
    public function setPrivacyNotice(string $privacyNotice): self { $this->privacyNotice = $privacyNotice; return $this; }
    public function getToolDisclaimer(): string { return $this->toolDisclaimer; }
    public function setToolDisclaimer(string $toolDisclaimer): self { $this->toolDisclaimer = $toolDisclaimer; return $this; }
    public function getNotificationEmail(): ?string { return $this->notificationEmail; }
    public function setNotificationEmail(?string $notificationEmail): self { $this->notificationEmail = $notificationEmail; return $this; }
    public function isSendNotificationEmail(): bool { return $this->sendNotificationEmail; }
    public function setSendNotificationEmail(bool $sendNotificationEmail): self { $this->sendNotificationEmail = $sendNotificationEmail; return $this; }
    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): self { $this->active = $active; return $this; }
    public function getTools(): Collection { return $this->tools; }
    public function getPackages(): Collection { return $this->packages; }
}
