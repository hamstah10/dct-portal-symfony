<?php

namespace Tfd\PartnerWizardBundle\Entity;

use App\Entity\ShopProduct;
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

    /**
     * @var Collection<int, ShopProduct>
     */
    #[ORM\ManyToMany(targetEntity: ShopProduct::class)]
    #[ORM\JoinTable(name: 'tfd_partner_wizard_client_shopproduct')]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'shop_product_uid', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    private Collection $recommendedProducts;

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

    /** SHA-256-Hash des API-Tokens. Der Klartext wird nie gespeichert. */
    #[ORM\Column(length: 64, nullable: true)]
    private ?string $apiTokenHash = null;

    /** Erste Zeichen des Tokens zur Wiedererkennung im Admin. */
    #[ORM\Column(length: 16, nullable: true)]
    private ?string $apiTokenHint = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $apiTokenCreatedAt = null;

    /** true = jede Anfrage braucht den Token (reiner Server-Zugriff), auch aus dem Browser. */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $apiTokenRequired = false;

    /** true = Texte, Fragen und Antwortoptionen dürfen über die Headless-API (nur mit API-Token) geändert werden. */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $textApiEnabled = false;

    /** Terminbuchung im Wizard (freie Slots aus dem Superadmin-Kalender). */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $appointmentEnabled = false;

    /** true = ohne gewählten Termin kann die Anfrage nicht abgeschickt werden. */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $appointmentRequired = false;

    #[ORM\Column(length: 120, options: ['default' => 'Partner-Beratung'])]
    private string $appointmentTitle = 'Partner-Beratung';

    /** Kategorie-Key im Superadmin-Kalender (superadmin_calendar_category.category_key). */
    #[ORM\Column(length: 20, options: ['default' => 'support'])]
    private string $appointmentCalendarId = 'support';

    #[ORM\Column(options: ['default' => 30])]
    private int $appointmentDurationMinutes = 30;

    /** Pause vor und nach jedem belegten Termin. */
    #[ORM\Column(options: ['default' => 0])]
    private int $appointmentBufferMinutes = 0;

    /** Frühester Termin: so viele Stunden ab jetzt. */
    #[ORM\Column(options: ['default' => 24])]
    private int $appointmentMinNoticeHours = 24;

    /** Wie viele Tage im Voraus gebucht werden kann. */
    #[ORM\Column(options: ['default' => 21])]
    private int $appointmentDaysAhead = 21;

    /** ISO-Wochentage 1 = Montag … 7 = Sonntag. */
    #[ORM\Column(type: 'json')]
    private array $appointmentWeekdays = [1, 2, 3, 4, 5];

    #[ORM\Column(length: 5, options: ['default' => '09:00'])]
    private string $appointmentDayStart = '09:00';

    #[ORM\Column(length: 5, options: ['default' => '17:00'])]
    private string $appointmentDayEnd = '17:00';

    #[ORM\Column(length: 60, options: ['default' => 'Europe/Berlin'])]
    private string $appointmentTimezone = 'Europe/Berlin';

    /** Ort oder Link, der in den Kalendereintrag geschrieben wird (z. B. Telefon, Meet-Link). */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $appointmentLocation = null;

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
        $this->recommendedProducts = new ArrayCollection();
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

    /** Eine Domain pro Zeile (Formularfeld im Admin). */
    public function getAllowedDomainsText(): string { return implode("\n", $this->allowedDomains); }

    public function setAllowedDomainsText(?string $text): self
    {
        $domains = [];
        foreach (preg_split('/[\s,;]+/', (string) $text, -1, PREG_SPLIT_NO_EMPTY) as $entry) {
            $entry = strtolower(trim($entry));
            $entry = preg_replace('#^[a-z][a-z0-9+.-]*://#', '', $entry);
            $entry = preg_replace('#[/?\#:].*$#', '', $entry);
            if ($entry !== '' && !in_array($entry, $domains, true)) {
                $domains[] = $entry;
            }
        }
        $this->allowedDomains = $domains;

        return $this;
    }
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
    /**
     * @return Collection<int, ShopProduct>
     */
    public function getRecommendedProducts(): Collection { return $this->recommendedProducts; }

    public function addRecommendedProduct(ShopProduct $product): self
    {
        if (!$this->recommendedProducts->contains($product)) {
            $this->recommendedProducts->add($product);
        }

        return $this;
    }

    public function removeRecommendedProduct(ShopProduct $product): self
    {
        $this->recommendedProducts->removeElement($product);

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

    /**
     * Erzeugt einen neuen Token, speichert nur den Hash und gibt den Klartext einmalig zurück.
     */
    public function generateApiToken(): string
    {
        $token = 'pw_' . bin2hex(random_bytes(24));
        $this->apiTokenHash = hash('sha256', $token);
        $this->apiTokenHint = substr($token, 0, 10);
        $this->apiTokenCreatedAt = new \DateTimeImmutable();

        return $token;
    }

    public function clearApiToken(): self
    {
        $this->apiTokenHash = null;
        $this->apiTokenHint = null;
        $this->apiTokenCreatedAt = null;
        $this->apiTokenRequired = false;

        return $this;
    }

    public function hasApiToken(): bool { return null !== $this->apiTokenHash; }

    public function isApiTokenValid(string $token): bool
    {
        return null !== $this->apiTokenHash && '' !== $token && hash_equals($this->apiTokenHash, hash('sha256', $token));
    }

    public function getApiTokenHint(): ?string { return $this->apiTokenHint; }
    public function getApiTokenCreatedAt(): ?\DateTimeImmutable { return $this->apiTokenCreatedAt; }
    public function isApiTokenRequired(): bool { return $this->apiTokenRequired; }
    public function setApiTokenRequired(bool $apiTokenRequired): self { $this->apiTokenRequired = $apiTokenRequired; return $this; }
    public function isTextApiEnabled(): bool { return $this->textApiEnabled; }
    public function setTextApiEnabled(bool $textApiEnabled): self { $this->textApiEnabled = $textApiEnabled; return $this; }
    public function getTools(): Collection { return $this->tools; }
    public function getPackages(): Collection { return $this->packages; }

    public function isAppointmentEnabled(): bool { return $this->appointmentEnabled; }
    public function setAppointmentEnabled(bool $value): self { $this->appointmentEnabled = $value; return $this; }
    public function isAppointmentRequired(): bool { return $this->appointmentRequired; }
    public function setAppointmentRequired(bool $value): self { $this->appointmentRequired = $value; return $this; }
    public function getAppointmentTitle(): string { return $this->appointmentTitle; }
    public function setAppointmentTitle(?string $value): self { $this->appointmentTitle = trim((string) $value) ?: 'Partner-Beratung'; return $this; }
    public function getAppointmentCalendarId(): string { return $this->appointmentCalendarId; }
    public function setAppointmentCalendarId(?string $value): self { $this->appointmentCalendarId = preg_match('/^[a-z0-9-]{1,20}$/', (string) $value) ? (string) $value : 'support'; return $this; }
    public function getAppointmentDurationMinutes(): int { return $this->appointmentDurationMinutes; }
    public function setAppointmentDurationMinutes(?int $value): self { $this->appointmentDurationMinutes = max(5, min(480, (int) $value)); return $this; }
    public function getAppointmentBufferMinutes(): int { return $this->appointmentBufferMinutes; }
    public function setAppointmentBufferMinutes(?int $value): self { $this->appointmentBufferMinutes = max(0, min(240, (int) $value)); return $this; }
    public function getAppointmentMinNoticeHours(): int { return $this->appointmentMinNoticeHours; }
    public function setAppointmentMinNoticeHours(?int $value): self { $this->appointmentMinNoticeHours = max(0, min(720, (int) $value)); return $this; }
    public function getAppointmentDaysAhead(): int { return $this->appointmentDaysAhead; }
    public function setAppointmentDaysAhead(?int $value): self { $this->appointmentDaysAhead = max(1, min(180, (int) $value)); return $this; }

    /** @return list<int> */
    public function getAppointmentWeekdays(): array
    {
        $days = array_values(array_unique(array_filter(array_map('intval', $this->appointmentWeekdays), static fn (int $d): bool => $d >= 1 && $d <= 7)));
        sort($days);

        return $days;
    }

    public function setAppointmentWeekdays(?array $value): self { $this->appointmentWeekdays = array_values(array_map('intval', (array) $value)); return $this; }
    public function getAppointmentDayStart(): string { return $this->appointmentDayStart; }
    public function setAppointmentDayStart(?string $value): self { $this->appointmentDayStart = self::normalizeTime($value, '09:00'); return $this; }
    public function getAppointmentDayEnd(): string { return $this->appointmentDayEnd; }
    public function setAppointmentDayEnd(?string $value): self { $this->appointmentDayEnd = self::normalizeTime($value, '17:00'); return $this; }
    public function getAppointmentTimezone(): string { return $this->appointmentTimezone; }

    public function setAppointmentTimezone(?string $value): self
    {
        $value = trim((string) $value);
        $this->appointmentTimezone = in_array($value, \DateTimeZone::listIdentifiers(), true) ? $value : 'Europe/Berlin';

        return $this;
    }

    public function getAppointmentLocation(): ?string { return $this->appointmentLocation; }
    public function setAppointmentLocation(?string $value): self { $this->appointmentLocation = trim((string) $value) ?: null; return $this; }

    private static function normalizeTime(?string $value, string $fallback): string
    {
        $value = trim((string) $value);
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $m) && (int) $m[1] <= 24 && (int) $m[2] < 60) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        return $fallback;
    }
}
