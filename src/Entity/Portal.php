<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PortalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PortalRepository::class)]
#[ORM\Table(name: 'portal')]
class Portal
{
    public const DEFAULT_PRIMARY_COLOR = '#b91c1c';
    public const DEFAULT_SECONDARY_COLOR = '#111111';
    public const DEFAULT_MENU_BACKGROUND_COLOR = '#111111';
    public const DEFAULT_CONTENT_BACKGROUND_COLOR = '#050505';
    public const DEFAULT_HEADING_FONT = '\'Averta\', \'IBM Plex Sans\', sans-serif';
    public const DEFAULT_BODY_FONT = '\'Averta\', \'IBM Plex Sans\', sans-serif';
    public const DEFAULT_DATA_FONT = '\'JetBrains Mono\', \'IBM Plex Mono\', monospace';

    public const THEME_PRESET_ORIGINAL = 'original';
    public const THEME_PRESET_OCEAN = 'ocean';
    public const THEME_PRESET_COPPER = 'copper';
    public const THEME_PRESET_FOREST = 'forest';
    public const THEME_PRESET_ARCTIC = 'arctic';
    public const THEME_PRESET_GRAPHITE = 'graphite';
    public const THEME_PRESET_SUNSET = 'sunset';
    public const THEME_PRESET_SAND = 'sand';
    public const THEME_PRESET_MIDNIGHT = 'midnight';
    public const THEME_PRESET_AURORA = 'aurora';
    public const THEME_PRESET_MONO = 'mono';
    public const THEME_PRESET_ROSE = 'rose';
    public const THEME_PRESET_VOLT = 'volt';
    public const THEME_PRESET_IVORY = 'ivory';
    public const THEME_PRESET_AUTOMOTIVE = 'automotive';
    public const THEME_PRESET_OEM = 'oem';
    public const THEME_PRESET_SIEMONEIT = 'siemoneit';

    public const TYPE_CUSTOMER_PORTAL = 'customer_portal';
    public const TYPE_BRAND_SUITE = 'brand_suite';
    public const TYPE_MARKETING_SITE = 'marketing_site';
    public const TYPE_SUPERADMIN_PORTAL = 'superadmin_portal';

    public const MODULE_DASHBOARD = 'dashboard';
    public const MODULE_PROFILE = 'profile';
    public const MODULE_ORDER = 'order';
    public const MODULE_UPLOADS = 'uploads';
    public const MODULE_VEHICLE_DATABASE = 'vehicle_database';
    public const MODULE_CREDITS = 'credits';
    public const MODULE_REGISTRATION = 'registration';
    public const MODULE_PUBLIC_WEBSITE = 'public_website';
    public const MODULE_OPS_OVERVIEW = 'ops_overview';
    public const MODULE_OPS_ORDERS = 'ops_orders';
    public const MODULE_OPS_VEHICLES = 'ops_vehicles';
    public const MODULE_OPS_CUSTOMERS = 'ops_customers';
    public const MODULE_OPS_TICKETS = 'ops_tickets';
    public const MODULE_OPS_EMAIL = 'ops_email';
    public const MODULE_OPS_CALENDAR = 'ops_calendar';
    public const MODULE_OPS_INSTALLATION = 'ops_installation';
    public const MODULE_OPS_DSG = 'ops_dsg';
    public const MODULE_OPS_SEARCH = 'ops_search';
    public const MODULE_OPS_CONTACT = 'ops_contact';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80, unique: true)]
    private ?string $portalKey = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 40)]
    private string $portalType = self::TYPE_BRAND_SUITE;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Firma $firma = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_data_pool_id', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $vehicleDataPool = null;

    #[ORM\Column(length: 180, nullable: true, unique: true)]
    private ?string $portalHost = null;

    #[ORM\Column(length: 180, nullable: true, unique: true)]
    private ?string $websiteHost = null;

    #[ORM\Column(length: 7)]
    private string $primaryColor = '#ff0000';

    #[ORM\Column(length: 7)]
    private string $secondaryColor = '#000000';

    #[ORM\Column(length: 7)]
    private string $menuBackgroundColor = '#111111';

    #[ORM\Column(length: 7)]
    private string $contentBackgroundColor = '#050505';

    #[ORM\Column]
    private bool $customDesignEnabled = false;

    #[ORM\Column(length: 40)]
    private string $themePreset = self::THEME_PRESET_ORIGINAL;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $headingFont = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bodyFont = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dataFont = null;

    /**
     * @var Collection<int, Zahlungsart>
     */
    #[ORM\ManyToMany(targetEntity: Zahlungsart::class)]
    #[ORM\JoinTable(name: 'portal_zahlungsart')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $zahlungsarten;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private bool $maintenanceMode = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $creditPrice = null;

    #[ORM\Column]
    private bool $portalEnabled = true;

    #[ORM\Column]
    private bool $winolsEnabled = false;

    #[ORM\Column]
    private bool $websiteEnabled = true;

    #[ORM\Column]
    private bool $customerRegistrationEnabled = true;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $enabledModules = [
        self::MODULE_DASHBOARD,
        self::MODULE_PROFILE,
        self::MODULE_ORDER,
        self::MODULE_UPLOADS,
        self::MODULE_VEHICLE_DATABASE,
        self::MODULE_REGISTRATION,
        self::MODULE_PUBLIC_WEBSITE,
    ];

    #[ORM\Column]
    private int $sortOrder = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->zahlungsarten = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?? ('Portal #' . $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPortalKey(): ?string
    {
        return $this->portalKey;
    }

    public function setPortalKey(string $portalKey): static
    {
        $this->portalKey = strtolower(trim($portalKey));

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    public function getPortalType(): string
    {
        return $this->portalType;
    }

    public function setPortalType(string $portalType): static
    {
        $this->portalType = $portalType;

        return $this;
    }

    public function getFirma(): ?Firma
    {
        return $this->firma;
    }

    public function setFirma(?Firma $firma): static
    {
        $this->firma = $firma;

        return $this;
    }

    public function getVehicleDataPool(): ?VehicleDataPool
    {
        return $this->vehicleDataPool;
    }

    public function setVehicleDataPool(?VehicleDataPool $vehicleDataPool): static
    {
        $this->vehicleDataPool = $vehicleDataPool;

        return $this;
    }

    public function getPortalHost(): ?string
    {
        return $this->portalHost;
    }

    public function setPortalHost(?string $portalHost): static
    {
        $this->portalHost = $this->normalizeHost($portalHost);

        return $this;
    }

    public function getWebsiteHost(): ?string
    {
        return $this->websiteHost;
    }

    public function setWebsiteHost(?string $websiteHost): static
    {
        $this->websiteHost = $this->normalizeHost($websiteHost);

        return $this;
    }

    public function getPrimaryColor(): string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(string $primaryColor): static
    {
        $this->primaryColor = $primaryColor;

        return $this;
    }

    public function getSecondaryColor(): string
    {
        return $this->secondaryColor;
    }

    public function setSecondaryColor(string $secondaryColor): static
    {
        $this->secondaryColor = $secondaryColor;

        return $this;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function isCustomDesignEnabled(): bool
    {
        return $this->customDesignEnabled;
    }

    public function setCustomDesignEnabled(bool $customDesignEnabled): static
    {
        $this->customDesignEnabled = $customDesignEnabled;

        return $this;
    }

    public function getThemePreset(): string
    {
        return $this->themePreset;
    }

    public function setThemePreset(string $themePreset): static
    {
        $this->themePreset = trim($themePreset) !== '' ? trim($themePreset) : self::THEME_PRESET_ORIGINAL;

        return $this;
    }

    public function getMenuBackgroundColor(): string
    {
        return $this->menuBackgroundColor;
    }

    public function setMenuBackgroundColor(string $menuBackgroundColor): static
    {
        $this->menuBackgroundColor = $menuBackgroundColor;

        return $this;
    }

    public function getContentBackgroundColor(): string
    {
        return $this->contentBackgroundColor;
    }

    public function setContentBackgroundColor(string $contentBackgroundColor): static
    {
        $this->contentBackgroundColor = $contentBackgroundColor;

        return $this;
    }

    public function setLogoPath(?string $logoPath): static
    {
        $this->logoPath = $logoPath;

        return $this;
    }

    public function getHeadingFont(): ?string
    {
        return $this->headingFont;
    }

    public function setHeadingFont(?string $headingFont): static
    {
        $headingFont = trim((string) $headingFont);
        $this->headingFont = $headingFont !== '' ? $headingFont : null;

        return $this;
    }

    public function getBodyFont(): ?string
    {
        return $this->bodyFont;
    }

    public function setBodyFont(?string $bodyFont): static
    {
        $bodyFont = trim((string) $bodyFont);
        $this->bodyFont = $bodyFont !== '' ? $bodyFont : null;

        return $this;
    }

    public function getDataFont(): ?string
    {
        return $this->dataFont;
    }

    public function setDataFont(?string $dataFont): static
    {
        $dataFont = trim((string) $dataFont);
        $this->dataFont = $dataFont !== '' ? $dataFont : null;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return Collection<int, Zahlungsart>
     */
    public function getZahlungsarten(): Collection
    {
        return $this->zahlungsarten;
    }

    public function addZahlungsart(Zahlungsart $zahlungsart): static
    {
        if (!$this->zahlungsarten->contains($zahlungsart)) {
            $this->zahlungsarten->add($zahlungsart);
        }

        return $this;
    }

    public function removeZahlungsart(Zahlungsart $zahlungsart): static
    {
        $this->zahlungsarten->removeElement($zahlungsart);

        return $this;
    }

    /**
     * @param iterable<Zahlungsart> $zahlungsarten
     */
    public function setZahlungsarten(iterable $zahlungsarten): static
    {
        $this->zahlungsarten->clear();

        foreach ($zahlungsarten as $zahlungsart) {
            if ($zahlungsart instanceof Zahlungsart) {
                $this->addZahlungsart($zahlungsart);
            }
        }

        return $this;
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

    public function isMaintenanceMode(): bool
    {
        return $this->maintenanceMode;
    }

    public function setMaintenanceMode(bool $maintenanceMode): static
    {
        $this->maintenanceMode = $maintenanceMode;

        return $this;
    }

    public function getCreditPrice(): ?string
    {
        return $this->creditPrice;
    }

    public function setCreditPrice(?string $creditPrice): static
    {
        $this->creditPrice = null !== $creditPrice && '' !== trim($creditPrice) ? $creditPrice : null;

        return $this;
    }

    public function isPortalEnabled(): bool
    {
        return $this->portalEnabled;
    }

    public function setPortalEnabled(bool $portalEnabled): static
    {
        $this->portalEnabled = $portalEnabled;

        return $this;
    }

    public function isWinolsEnabled(): bool
    {
        return $this->winolsEnabled;
    }

    public function setWinolsEnabled(bool $winolsEnabled): static
    {
        $this->winolsEnabled = $winolsEnabled;

        return $this;
    }

    public function isWebsiteEnabled(): bool
    {
        return $this->websiteEnabled;
    }

    public function setWebsiteEnabled(bool $websiteEnabled): static
    {
        $this->websiteEnabled = $websiteEnabled;

        return $this;
    }

    public function isCustomerRegistrationEnabled(): bool
    {
        return $this->customerRegistrationEnabled;
    }

    public function setCustomerRegistrationEnabled(bool $customerRegistrationEnabled): static
    {
        $this->customerRegistrationEnabled = $customerRegistrationEnabled;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getEnabledModules(): array
    {
        return $this->enabledModules;
    }

    /**
     * @param list<string> $enabledModules
     */
    public function setEnabledModules(array $enabledModules): static
    {
        $enabledModules = array_values(array_unique(array_filter(array_map(
            static fn (mixed $module): string => trim((string) $module),
            $enabledModules
        ))));

        $this->enabledModules = $enabledModules;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPortalUrl(): ?string
    {
        if (!$this->portalHost) {
            return null;
        }

        return 'https://' . $this->portalHost;
    }

    public function getWebsiteUrl(): ?string
    {
        if (!$this->websiteHost) {
            return null;
        }

        return 'https://' . $this->websiteHost;
    }

    public function getPrimaryRgb(): string
    {
        return $this->hexToRgbString($this->primaryColor);
    }

    public function getSecondaryRgb(): string
    {
        return $this->hexToRgbString($this->secondaryColor);
    }

    /**
     * @return array{primaryColor:string,secondaryColor:string,menuBackgroundColor:string,contentBackgroundColor:string,headingFont:string,bodyFont:string,dataFont:string,themePreset:string}
     */
    public function getResolvedDesign(): array
    {
        if (!$this->customDesignEnabled) {
            $design = self::getThemePresetDesign($this->themePreset);
        } else {
            $design = [
                'primaryColor' => $this->primaryColor,
                'secondaryColor' => $this->secondaryColor,
                'menuBackgroundColor' => $this->menuBackgroundColor,
                'contentBackgroundColor' => $this->contentBackgroundColor,
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
                'dataFont' => self::DEFAULT_DATA_FONT,
                'themePreset' => $this->themePreset,
            ];
        }

        if ($this->headingFont !== null) {
            $design['headingFont'] = $this->headingFont;
        }

        if ($this->bodyFont !== null) {
            $design['bodyFont'] = $this->bodyFont;
        }

        if ($this->dataFont !== null) {
            $design['dataFont'] = $this->dataFont;
        }

        $design['portalName'] = $this->name ?? $this->portalKey ?? 'Portal';
        $design['portalKey'] = $this->portalKey ?? '';

        $firmaLogo = $this->firma?->getLogo();
        $design['logoUrl'] = $firmaLogo !== null && $firmaLogo !== ''
            ? '/uploads/firmen/' . $firmaLogo
            : null;

        $portalLogo = $this->logoPath;
        if ($portalLogo !== null && $portalLogo !== '') {
            $design['logoUrl'] = '/' . ltrim($portalLogo, '/');
        }

        return $design;
    }

    private function normalizeHost(?string $host): ?string
    {
        $host = trim((string) $host);
        if ($host === '') {
            return null;
        }

        $host = preg_replace('#^https?://#i', '', $host) ?? $host;
        $host = rtrim($host, '/');

        return strtolower($host);
    }

    private function hexToRgbString(string $hex): string
    {
        $normalized = ltrim(trim($hex), '#');
        if (strlen($normalized) === 3) {
            $normalized = preg_replace('/(.)/', '$1$1', $normalized) ?? $normalized;
        }

        if (strlen($normalized) !== 6 || !ctype_xdigit($normalized)) {
            return '255, 0, 0';
        }

        return sprintf(
            '%d, %d, %d',
            hexdec(substr($normalized, 0, 2)),
            hexdec(substr($normalized, 2, 2)),
            hexdec(substr($normalized, 4, 2))
        );
    }

    /**
     * @return array<string, string>
     */
    public static function getPortalTypeChoices(): array
    {
        return [
            'Kundenportal' => self::TYPE_CUSTOMER_PORTAL,
            'Brand Suite' => self::TYPE_BRAND_SUITE,
            'Marketing-Website' => self::TYPE_MARKETING_SITE,
            'Superadmin-Portal' => self::TYPE_SUPERADMIN_PORTAL,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getModuleChoices(): array
    {
        return [
            'Dashboard' => self::MODULE_DASHBOARD,
            'Profil' => self::MODULE_PROFILE,
            'Auftrag' => self::MODULE_ORDER,
            'Uploads' => self::MODULE_UPLOADS,
            'Fahrzeugdatenbank' => self::MODULE_VEHICLE_DATABASE,
            'Credits' => self::MODULE_CREDITS,
            'Registrierung' => self::MODULE_REGISTRATION,
            'Oeffentliche Website' => self::MODULE_PUBLIC_WEBSITE,
            'Operations / Übersicht' => self::MODULE_OPS_OVERVIEW,
            'Operations / Aufträge' => self::MODULE_OPS_ORDERS,
            'Operations / Fahrzeugdatenbank' => self::MODULE_OPS_VEHICLES,
            'Operations / Kunden' => self::MODULE_OPS_CUSTOMERS,
            'Operations / Tickets' => self::MODULE_OPS_TICKETS,
            'Operations / E-Mail' => self::MODULE_OPS_EMAIL,
            'Operations / Kalender' => self::MODULE_OPS_CALENDAR,
            'Operations / Installation' => self::MODULE_OPS_INSTALLATION,
            'Operations / Getriebe' => self::MODULE_OPS_DSG,
            'Operations / Suche' => self::MODULE_OPS_SEARCH,
            'Operations / Kontakt' => self::MODULE_OPS_CONTACT,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getThemePresetChoices(): array
    {
        return [
            'Original' => self::THEME_PRESET_ORIGINAL,
            'Ocean Drive' => self::THEME_PRESET_OCEAN,
            'Copper Forge' => self::THEME_PRESET_COPPER,
            'Forest Signal' => self::THEME_PRESET_FOREST,
            'Arctic Light' => self::THEME_PRESET_ARCTIC,
            'Graphite Neon | dark / tech' => self::THEME_PRESET_GRAPHITE,
            'Sunset Grid | dark / bold' => self::THEME_PRESET_SUNSET,
            'Sand Luxe | light / premium' => self::THEME_PRESET_SAND,
            'Midnight Signal | dark / blue' => self::THEME_PRESET_MIDNIGHT,
            'Aurora Pulse | dark / vibrant' => self::THEME_PRESET_AURORA,
            'Mono Slate | neutral / clean' => self::THEME_PRESET_MONO,
            'Rose Studio | warm / editorial' => self::THEME_PRESET_ROSE,
            'Volt Grid | dark / neon' => self::THEME_PRESET_VOLT,
            'Ivory Editorial | light / minimal' => self::THEME_PRESET_IVORY,
            'Dark Automotive | graphite / motorsport' => self::THEME_PRESET_AUTOMOTIVE,
            'OEM Light | hell / serienmäßig' => self::THEME_PRESET_OEM,
            'Siemoneit Racing | hell / schwarz-rot' => self::THEME_PRESET_SIEMONEIT,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getFontChoices(): array
    {
        return [
            'Theme-Standard' => '',
            'Averta (lokal)' => '\'Averta\', \'IBM Plex Sans\', sans-serif',
            'Alegreya' => '\'Alegreya\', serif',
            'Archivo' => '\'Archivo\', sans-serif',
            'Assistant' => '\'Assistant\', sans-serif',
            'Barlow' => '\'Barlow\', sans-serif',
            'Bitter' => '\'Bitter\', serif',
            'Cabin' => '\'Cabin\', sans-serif',
            'Cormorant Garamond' => '\'Cormorant Garamond\', serif',
            'Crimson Text' => '\'Crimson Text\', serif',
            'DM Sans' => '\'DM Sans\', sans-serif',
            'DM Serif Display' => '\'DM Serif Display\', serif',
            'EB Garamond' => '\'EB Garamond\', serif',
            'Exo 2' => '\'Exo 2\', sans-serif',
            'Figtree' => '\'Figtree\', sans-serif',
            'Fraunces' => '\'Fraunces\', serif',
            'Heebo' => '\'Heebo\', sans-serif',
            'Hind' => '\'Hind\', sans-serif',
            'IBM Plex Mono' => '\'IBM Plex Mono\', monospace',
            'IBM Plex Sans' => '\'IBM Plex Sans\', sans-serif',
            'Inter' => '\'Inter\', sans-serif',
            'JetBrains Mono' => '\'JetBrains Mono\', monospace',
            'Josefin Sans' => '\'Josefin Sans\', sans-serif',
            'Jost' => '\'Jost\', sans-serif',
            'Kanit' => '\'Kanit\', sans-serif',
            'Lato' => '\'Lato\', sans-serif',
            'Libre Baskerville' => '\'Libre Baskerville\', serif',
            'Lora' => '\'Lora\', serif',
            'Manrope' => '\'Manrope\', sans-serif',
            'Merriweather' => '\'Merriweather\', serif',
            'Montserrat' => '\'Montserrat\', sans-serif',
            'Mukta' => '\'Mukta\', sans-serif',
            'Mulish' => '\'Mulish\', sans-serif',
            'Noto Sans' => '\'Noto Sans\', sans-serif',
            'Nunito Sans' => '\'Nunito Sans\', sans-serif',
            'Open Sans' => '\'Open Sans\', sans-serif',
            'Outfit' => '\'Outfit\', sans-serif',
            'Playfair Display' => '\'Playfair Display\', serif',
            'Plus Jakarta Sans' => '\'Plus Jakarta Sans\', sans-serif',
            'Poppins' => '\'Poppins\', sans-serif',
            'PT Sans' => '\'PT Sans\', sans-serif',
            'Public Sans' => '\'Public Sans\', sans-serif',
            'Rajdhani' => '\'Rajdhani\', sans-serif',
            'Roboto' => '\'Roboto\', sans-serif',
            'Rubik' => '\'Rubik\', sans-serif',
            'Sora' => '\'Sora\', sans-serif',
            'Source Sans 3' => '\'Source Sans 3\', sans-serif',
            'Source Serif 4' => '\'Source Serif 4\', serif',
            'Space Grotesk' => '\'Space Grotesk\', sans-serif',
            'Space Mono' => '\'Space Mono\', monospace',
            'Teko' => '\'Teko\', sans-serif',
            'Titillium Web' => '\'Titillium Web\', sans-serif',
            'Urbanist' => '\'Urbanist\', sans-serif',
            'Work Sans' => '\'Work Sans\', sans-serif',
        ];
    }

    /**
     * @return array{primaryColor:string,secondaryColor:string,menuBackgroundColor:string,contentBackgroundColor:string,headingFont:string,bodyFont:string,dataFont:string,themePreset:string}
     */
    public static function getThemePresetDesign(string $preset): array
    {
        return array_merge(
            match ($preset) {
                self::THEME_PRESET_OCEAN => [
                'primaryColor' => '#0ea5e9',
                'secondaryColor' => '#0f172a',
                'menuBackgroundColor' => '#082f49',
                'contentBackgroundColor' => '#06131f',
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
            ],
            self::THEME_PRESET_COPPER => [
                'primaryColor' => '#c2410c',
                'secondaryColor' => '#2a211c',
                'menuBackgroundColor' => '#1c1917',
                'contentBackgroundColor' => '#120f0d',
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
            ],
            self::THEME_PRESET_FOREST => [
                'primaryColor' => '#16a34a',
                'secondaryColor' => '#13261b',
                'menuBackgroundColor' => '#0b1f14',
                'contentBackgroundColor' => '#08150f',
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
            ],
            self::THEME_PRESET_ARCTIC => [
                'primaryColor' => '#2563eb',
                'secondaryColor' => '#ffffff',
                'menuBackgroundColor' => '#e0f2fe',
                'contentBackgroundColor' => '#f8fafc',
                'headingFont' => '\'Fraunces\', serif',
                'bodyFont' => '\'Manrope\', sans-serif',
            ],
            self::THEME_PRESET_GRAPHITE => [
                'primaryColor' => '#22c55e',
                'secondaryColor' => '#16181d',
                'menuBackgroundColor' => '#0f1115',
                'contentBackgroundColor' => '#090b0f',
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
            ],
            self::THEME_PRESET_SUNSET => [
                'primaryColor' => '#f97316',
                'secondaryColor' => '#2a1631',
                'menuBackgroundColor' => '#1f1027',
                'contentBackgroundColor' => '#140b19',
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
            ],
            self::THEME_PRESET_SAND => [
                'primaryColor' => '#b45309',
                'secondaryColor' => '#f7f1e8',
                'menuBackgroundColor' => '#efe4d1',
                'contentBackgroundColor' => '#fffaf2',
                'headingFont' => '\'Cormorant Garamond\', serif',
                'bodyFont' => '\'Source Sans 3\', sans-serif',
            ],
            self::THEME_PRESET_MIDNIGHT => [
                'primaryColor' => '#3b82f6',
                'secondaryColor' => '#111827',
                'menuBackgroundColor' => '#0f172a',
                'contentBackgroundColor' => '#020617',
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
            ],
            self::THEME_PRESET_AURORA => [
                'primaryColor' => '#14b8a6',
                'secondaryColor' => '#22132f',
                'menuBackgroundColor' => '#140b1d',
                'contentBackgroundColor' => '#09060f',
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
            ],
            self::THEME_PRESET_MONO => [
                'primaryColor' => '#475569',
                'secondaryColor' => '#1f2937',
                'menuBackgroundColor' => '#111827',
                'contentBackgroundColor' => '#0f172a',
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
            ],
            self::THEME_PRESET_ROSE => [
                'primaryColor' => '#e11d48',
                'secondaryColor' => '#fff1f2',
                'menuBackgroundColor' => '#ffe4e6',
                'contentBackgroundColor' => '#fff8f8',
                'headingFont' => '\'Playfair Display\', serif',
                'bodyFont' => '\'Source Sans 3\', sans-serif',
            ],
            self::THEME_PRESET_VOLT => [
                'primaryColor' => '#84cc16',
                'secondaryColor' => '#18181b',
                'menuBackgroundColor' => '#09090b',
                'contentBackgroundColor' => '#0b0f07',
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
            ],
            self::THEME_PRESET_IVORY => [
                'primaryColor' => '#7c3aed',
                'secondaryColor' => '#ffffff',
                'menuBackgroundColor' => '#f5f3ff',
                'contentBackgroundColor' => '#fafaf9',
                'headingFont' => '\'Libre Baskerville\', serif',
                'bodyFont' => '\'Work Sans\', sans-serif',
            ],
            self::THEME_PRESET_AUTOMOTIVE => [
                'primaryColor' => '#e42525',
                'secondaryColor' => '#171a21',
                'menuBackgroundColor' => '#12151c',
                'contentBackgroundColor' => '#0c0e13',
                'headingFont' => '\'Outfit\', system-ui, sans-serif',
                'bodyFont' => '\'Space Grotesk\', system-ui, sans-serif',
            ],
            self::THEME_PRESET_OEM => [
                'primaryColor' => '#1d4ed8',
                'secondaryColor' => '#ffffff',
                'menuBackgroundColor' => '#d5dfe9',
                'contentBackgroundColor' => '#e8eef5',
                'headingFont' => '\'Barlow\', system-ui, sans-serif',
                'bodyFont' => '\'Source Sans 3\', system-ui, sans-serif',
            ],
            self::THEME_PRESET_SIEMONEIT => [
                'primaryColor' => '#dc2626',
                'secondaryColor' => '#ffffff',
                'menuBackgroundColor' => '#111111',
                'contentBackgroundColor' => '#f4f4f5',
                'headingFont' => '\'IBM Plex Sans\', system-ui, sans-serif',
                'bodyFont' => '\'IBM Plex Sans\', system-ui, sans-serif',
            ],
            default => [
                'primaryColor' => self::DEFAULT_PRIMARY_COLOR,
                'secondaryColor' => self::DEFAULT_SECONDARY_COLOR,
                'menuBackgroundColor' => self::DEFAULT_MENU_BACKGROUND_COLOR,
                'contentBackgroundColor' => self::DEFAULT_CONTENT_BACKGROUND_COLOR,
                'headingFont' => self::DEFAULT_HEADING_FONT,
                'bodyFont' => self::DEFAULT_BODY_FONT,
            ],
        },
            [
                'dataFont' => self::DEFAULT_DATA_FONT,
                'themePreset' => $preset,
            ]
        );
    }
}
