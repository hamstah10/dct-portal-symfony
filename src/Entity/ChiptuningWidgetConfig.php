<?php

namespace App\Entity;

use App\Repository\ChiptuningWidgetConfigRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChiptuningWidgetConfigRepository::class)]
#[ORM\Table(name: 'chiptuning_widget_config')]
#[ORM\Index(columns: ['active'], name: 'IDX_CHIPTUNING_WIDGET_ACTIVE')]
class ChiptuningWidgetConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Kunde $kunde = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ApiToken $apiToken = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_data_pool', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $vehicleDataPool = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_type', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleType $vehicleType = null;

    #[ORM\Column(length: 120, unique: true)]
    private ?string $embedKey = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(length: 255)]
    private ?string $title = 'Chiptuning Datenbank';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subtitle = 'Fahrzeug waehlen und Leistungsdaten anzeigen';

    #[ORM\Column(length: 50)]
    private string $templateVariant = VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_LIGHT;

    #[ORM\Column(length: 20)]
    private string $primaryColor = '#d53b2a';

    #[ORM\Column(length: 20)]
    private string $backgroundColor = '#ffffff';

    #[ORM\Column(length: 20)]
    private string $textColor = '#171d2a';

    #[ORM\Column(length: 20)]
    private string $selectBackgroundColor = '#ffffff';

    #[ORM\Column(length: 20)]
    private string $selectOpenBackgroundColor = '#f7f8fb';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $customCss = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $allowedDomains = null;

    #[ORM\Column]
    private bool $sendTrackingEvents = true;

    #[ORM\Column]
    private bool $showPerformanceChart = true;

    #[ORM\Column(length: 10)]
    private string $typeIconColor = 'black';

    #[ORM\Column]
    private int $iframeHeight = 850;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function __toString(): string
    {
        return $this->title ?? ('Chiptuning Widget #' . $this->id);
    }

    /**
     * @return array<string, string>
     */
    public static function getTemplateChoices(): array
    {
        return [
            'Premium Hell' => VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_LIGHT,
            'Premium Dunkel' => VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_DARK,
            'Widget Katalog Hell' => VehicleFrontendSettings::TEMPLATE_WIDGET_CATALOG_LIGHT,
            'Dunkel' => VehicleFrontendSettings::TEMPLATE_PERFORMANCE,
            'Hell / Minimal' => VehicleFrontendSettings::TEMPLATE_MINIMAL,
            'Contrast' => VehicleFrontendSettings::TEMPLATE_CONTRAST,
            'Sidebar' => VehicleFrontendSettings::TEMPLATE_LAYOUT3,
            'Download' => VehicleFrontendSettings::TEMPLATE_LAYOUT4,
            'V5 Tabelle' => VehicleFrontendSettings::TEMPLATE_LAYOUT5,
            'V6 Sidebar Hell' => VehicleFrontendSettings::TEMPLATE_LAYOUT6,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKunde(): ?Kunde
    {
        return $this->kunde;
    }

    public function setKunde(?Kunde $kunde): static
    {
        $this->kunde = $kunde;

        return $this;
    }

    public function getApiToken(): ?ApiToken
    {
        return $this->apiToken;
    }

    public function setApiToken(?ApiToken $apiToken): static
    {
        $this->apiToken = $apiToken;

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

    public function getVehicleType(): ?VehicleType
    {
        return $this->vehicleType;
    }

    public function setVehicleType(?VehicleType $vehicleType): static
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    public function getEmbedKey(): ?string
    {
        return $this->embedKey;
    }

    public function setEmbedKey(?string $embedKey): static
    {
        $normalized = strtolower(trim((string) $embedKey));
        $normalized = preg_replace('/[^a-z0-9_-]+/', '-', $normalized) ?? '';
        $normalized = trim($normalized, '-_');
        $this->embedKey = '' === $normalized ? null : $normalized;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = trim((string) $title) ?: 'Chiptuning Datenbank';

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): static
    {
        $this->subtitle = trim((string) $subtitle) ?: null;

        return $this;
    }

    public function getTemplateVariant(): string
    {
        return VehicleFrontendSettings::isValidTemplateVariant($this->templateVariant)
            ? self::normalizeTemplateVariant($this->templateVariant)
            : VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_LIGHT;
    }

    public function setTemplateVariant(string $templateVariant): static
    {
        $variant = self::normalizeTemplateVariant($templateVariant);
        $this->templateVariant = VehicleFrontendSettings::isValidTemplateVariant($variant)
            ? $variant
            : VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_LIGHT;

        return $this;
    }

    public function getPrimaryColor(): string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(string $primaryColor): static
    {
        $this->primaryColor = trim($primaryColor) ?: '#d53b2a';

        return $this;
    }

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(string $backgroundColor): static
    {
        $this->backgroundColor = trim($backgroundColor) ?: '#ffffff';

        return $this;
    }

    public function getTextColor(): string
    {
        return $this->textColor;
    }

    public function setTextColor(string $textColor): static
    {
        $this->textColor = trim($textColor) ?: '#171d2a';

        return $this;
    }

    public function getSelectBackgroundColor(): string
    {
        return $this->selectBackgroundColor;
    }

    public function setSelectBackgroundColor(string $selectBackgroundColor): static
    {
        $this->selectBackgroundColor = trim($selectBackgroundColor) ?: '#ffffff';

        return $this;
    }

    public function getSelectOpenBackgroundColor(): string
    {
        return $this->selectOpenBackgroundColor;
    }

    public function setSelectOpenBackgroundColor(string $selectOpenBackgroundColor): static
    {
        $this->selectOpenBackgroundColor = trim($selectOpenBackgroundColor) ?: '#f7f8fb';

        return $this;
    }

    public function getCustomCss(): ?string
    {
        return $this->customCss;
    }

    public function setCustomCss(?string $customCss): static
    {
        $this->customCss = $customCss;

        return $this;
    }

    public function getAllowedDomains(): ?string
    {
        return $this->allowedDomains;
    }

    public function setAllowedDomains(?string $allowedDomains): static
    {
        $this->allowedDomains = $allowedDomains;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getAllowedDomainPatterns(): array
    {
        $lines = preg_split('/\R+/', (string) $this->allowedDomains) ?: [];

        return array_values(array_filter(array_map(static fn (string $line): string => strtolower(trim($line)), $lines)));
    }

    public function isSendTrackingEvents(): bool
    {
        return $this->sendTrackingEvents;
    }

    public function setSendTrackingEvents(bool $sendTrackingEvents): static
    {
        $this->sendTrackingEvents = $sendTrackingEvents;

        return $this;
    }

    public function isShowPerformanceChart(): bool
    {
        return $this->showPerformanceChart;
    }

    public function setShowPerformanceChart(bool $showPerformanceChart): static
    {
        $this->showPerformanceChart = $showPerformanceChart;

        return $this;
    }

    public function getTypeIconColor(): string
    {
        return $this->typeIconColor;
    }

    public function setTypeIconColor(string $typeIconColor): static
    {
        $this->typeIconColor = in_array($typeIconColor, ['white', 'black'], true) ? $typeIconColor : 'black';

        return $this;
    }

    public function getIframeHeight(): int
    {
        return $this->iframeHeight;
    }

    public function setIframeHeight(int $iframeHeight): static
    {
        $this->iframeHeight = max(320, $iframeHeight);

        return $this;
    }

    public function getJavascriptSnippet(): string
    {
        return '';
    }

    public function getIframeSnippet(): string
    {
        return '';
    }

    public function getWidgetPreview(): string
    {
        return '';
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getWidgetPayload(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'buttonText' => 'Anfrage senden',
            'successTitle' => 'Anfrage gesendet!',
            'successMessage' => null,
            'primaryColor' => $this->primaryColor,
            'buttonTextColor' => self::getReadableForegroundColor($this->primaryColor),
            'backgroundColor' => $this->backgroundColor,
            'textColor' => $this->textColor,
            'selectBackgroundColor' => $this->selectBackgroundColor,
            'selectOpenBackgroundColor' => $this->selectOpenBackgroundColor,
            'customCss' => $this->customCss,
            'fields' => [
                'phone' => ['visible' => false, 'required' => false],
                'email' => ['visible' => false, 'required' => false],
                'finVin' => ['visible' => false, 'required' => false],
                'licensePlate' => ['visible' => false, 'required' => false],
                'mileage' => ['visible' => false, 'required' => false],
            ],
            'privacy' => ['required' => false, 'url' => null, 'textHtml' => null],
            'redirectAfterSubmit' => null,
            'sendTrackingEvents' => $this->sendTrackingEvents,
            'showPerformanceChart' => $this->showPerformanceChart,
            'showInquiryButton' => false,
            'typeIconColor' => $this->typeIconColor,
            'vehicleTypeId' => $this->vehicleType?->getId(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFrontendSettingsPayload(): array
    {
        $templateVariant = $this->getTemplateVariant();
        $flags = [
            'glbAutorotate' => false,
            'showPrices' => true,
            'showLogo' => false,
            'showOnlyStage1' => false,
        ];

        return [
            'layoutSwitcherEnabled' => false,
            'templateVariant' => $templateVariant,
            'layoutSettings' => [
                $templateVariant => $flags,
                VehicleFrontendSettings::TEMPLATE_PERFORMANCE => $flags,
                VehicleFrontendSettings::TEMPLATE_MINIMAL => $flags,
                VehicleFrontendSettings::TEMPLATE_CONTRAST => $flags,
                VehicleFrontendSettings::TEMPLATE_LAYOUT3 => $flags,
                VehicleFrontendSettings::TEMPLATE_LAYOUT4 => $flags,
                VehicleFrontendSettings::TEMPLATE_LAYOUT5 => $flags,
                VehicleFrontendSettings::TEMPLATE_LAYOUT6 => $flags,
                VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_DARK => $flags,
                VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_LIGHT => $flags,
                VehicleFrontendSettings::TEMPLATE_WIDGET_CATALOG_LIGHT => $flags,
            ],
        ];
    }

    private static function normalizeTemplateVariant(string $variant): string
    {
        $variant = strtolower(trim($variant));

        return VehicleFrontendSettings::TEMPLATE_LAYOUT2 === $variant
            ? VehicleFrontendSettings::TEMPLATE_CONTRAST
            : $variant;
    }

    private static function getReadableForegroundColor(string $backgroundColor): string
    {
        $hex = ltrim(trim($backgroundColor), '#');
        if (3 === strlen($hex)) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (6 !== strlen($hex) || 1 !== preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            return '#ffffff';
        }

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));
        $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $luminance > 150 ? '#111827' : '#ffffff';
    }
}
