<?php

namespace App\Entity;

use App\Repository\WebsiteWidgetConfigRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebsiteWidgetConfigRepository::class)]
#[ORM\Table(name: 'website_widget_config')]
#[ORM\Index(columns: ['active'], name: 'IDX_WEBSITE_WIDGET_ACTIVE')]
class WebsiteWidgetConfig
{
    public const RECIPIENT_EMAIL_CUSTOMER = 'customer';
    public const RECIPIENT_EMAIL_CUSTOM = 'custom';

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

    #[ORM\Column(length: 20)]
    private string $recipientEmailMode = self::RECIPIENT_EMAIL_CUSTOMER;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $customRecipientEmail = null;

    #[ORM\Column(length: 120, unique: true)]
    private ?string $embedKey = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subtitle = null;

    #[ORM\Column(length: 120)]
    private string $buttonText = 'Anfrage senden';

    #[ORM\Column(length: 120)]
    private string $successTitle = 'Anfrage gesendet!';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $successMessage = null;

    #[ORM\Column(length: 50)]
    private string $templateVariant = VehicleFrontendSettings::TEMPLATE_PERFORMANCE;

    #[ORM\Column(length: 20)]
    private string $primaryColor = '#5a2116';

    #[ORM\Column(length: 20)]
    private string $backgroundColor = '#ffffff';

    #[ORM\Column(length: 20)]
    private string $textColor = '#1a1a1a';

    #[ORM\Column(length: 20)]
    private string $selectBackgroundColor = '#ffffff';

    #[ORM\Column(length: 20)]
    private string $selectOpenBackgroundColor = '#f3f4f6';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $customCss = null;

    #[ORM\Column]
    private bool $showPhone = true;

    #[ORM\Column]
    private bool $showEmail = true;

    #[ORM\Column]
    private bool $showFinVin = false;

    #[ORM\Column]
    private bool $showLicensePlate = true;

    #[ORM\Column]
    private bool $showMileage = false;

    #[ORM\Column]
    private bool $phoneRequired = true;

    #[ORM\Column]
    private bool $emailRequired = true;

    #[ORM\Column]
    private bool $privacyRequired = true;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $privacyUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $privacyTextHtml = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $redirectAfterSubmit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $allowedDomains = null;

    #[ORM\Column]
    private bool $sendTrackingEvents = true;

    #[ORM\Column]
    private bool $showPerformanceChart = true;

    #[ORM\Column(length: 10)]
    private string $typeIconColor = 'white';

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
        return $this->title ?? ('Website Widget #' . $this->id);
    }

    /**
     * @return array<string, string>
     */
    public static function getTemplateChoices(): array
    {
        return [
            'Dunkel' => VehicleFrontendSettings::TEMPLATE_PERFORMANCE,
            'Premium Dunkel' => VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_DARK,
            'Premium Hell' => VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_LIGHT,
            'Widget Katalog Hell' => VehicleFrontendSettings::TEMPLATE_WIDGET_CATALOG_LIGHT,
            'Hell / Minimal' => VehicleFrontendSettings::TEMPLATE_MINIMAL,
            'Contrast' => VehicleFrontendSettings::TEMPLATE_CONTRAST,
            'Sidebar' => VehicleFrontendSettings::TEMPLATE_LAYOUT3,
            'Download' => VehicleFrontendSettings::TEMPLATE_LAYOUT4,
            'V5 Tabelle' => VehicleFrontendSettings::TEMPLATE_LAYOUT5,
            'V6 Sidebar Hell' => VehicleFrontendSettings::TEMPLATE_LAYOUT6,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getRecipientEmailModeChoices(): array
    {
        return [
            'E-Mail aus Kundendatensatz' => self::RECIPIENT_EMAIL_CUSTOMER,
            'Eigene E-Mail-Adresse' => self::RECIPIENT_EMAIL_CUSTOM,
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

    public function getRecipientEmailMode(): string
    {
        return \in_array($this->recipientEmailMode, [self::RECIPIENT_EMAIL_CUSTOMER, self::RECIPIENT_EMAIL_CUSTOM], true)
            ? $this->recipientEmailMode
            : self::RECIPIENT_EMAIL_CUSTOMER;
    }

    public function setRecipientEmailMode(string $recipientEmailMode): static
    {
        $this->recipientEmailMode = \in_array($recipientEmailMode, [self::RECIPIENT_EMAIL_CUSTOMER, self::RECIPIENT_EMAIL_CUSTOM], true)
            ? $recipientEmailMode
            : self::RECIPIENT_EMAIL_CUSTOMER;

        return $this;
    }

    public function getCustomRecipientEmail(): ?string
    {
        return $this->customRecipientEmail;
    }

    public function setCustomRecipientEmail(?string $customRecipientEmail): static
    {
        $this->customRecipientEmail = trim((string) $customRecipientEmail) ?: null;

        return $this;
    }

    public function getResolvedRecipientEmail(): ?string
    {
        if (self::RECIPIENT_EMAIL_CUSTOM === $this->getRecipientEmailMode()) {
            return $this->customRecipientEmail;
        }

        return $this->kunde?->getEmail();
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
        $this->title = trim((string) $title) ?: null;

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

    public function getButtonText(): string
    {
        return $this->buttonText;
    }

    public function setButtonText(string $buttonText): static
    {
        $this->buttonText = trim($buttonText) ?: 'Anfrage senden';

        return $this;
    }

    public function getSuccessTitle(): string
    {
        return $this->successTitle;
    }

    public function setSuccessTitle(string $successTitle): static
    {
        $this->successTitle = trim($successTitle) ?: 'Anfrage gesendet!';

        return $this;
    }

    public function getSuccessMessage(): ?string
    {
        return $this->successMessage;
    }

    public function setSuccessMessage(?string $successMessage): static
    {
        $this->successMessage = $successMessage;

        return $this;
    }

    public function getTemplateVariant(): string
    {
        return VehicleFrontendSettings::isValidTemplateVariant($this->templateVariant)
            ? self::normalizeTemplateVariant($this->templateVariant)
            : VehicleFrontendSettings::TEMPLATE_PERFORMANCE;
    }

    public function setTemplateVariant(string $templateVariant): static
    {
        $variant = self::normalizeTemplateVariant($templateVariant);
        $this->templateVariant = VehicleFrontendSettings::isValidTemplateVariant($variant)
            ? $variant
            : VehicleFrontendSettings::TEMPLATE_PERFORMANCE;

        if (VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_DARK === $this->templateVariant) {
            $this->applyPremiumDarkDefaults();
        }

        if (VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_LIGHT === $this->templateVariant) {
            $this->applyPremiumLightDefaults();
        }

        if (VehicleFrontendSettings::TEMPLATE_WIDGET_CATALOG_LIGHT === $this->templateVariant) {
            $this->applyCatalogLightDefaults();
        }

        return $this;
    }

    public function getPrimaryColor(): string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(string $primaryColor): static
    {
        $this->primaryColor = trim($primaryColor) ?: '#5a2116';

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
        $this->textColor = trim($textColor) ?: '#1a1a1a';

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
        $this->selectOpenBackgroundColor = trim($selectOpenBackgroundColor) ?: '#f3f4f6';

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

    public function isShowPhone(): bool
    {
        return $this->showPhone;
    }

    public function setShowPhone(bool $showPhone): static
    {
        $this->showPhone = $showPhone;

        return $this;
    }

    public function isShowEmail(): bool
    {
        return $this->showEmail;
    }

    public function setShowEmail(bool $showEmail): static
    {
        $this->showEmail = $showEmail;

        return $this;
    }

    public function isShowFinVin(): bool
    {
        return $this->showFinVin;
    }

    public function setShowFinVin(bool $showFinVin): static
    {
        $this->showFinVin = $showFinVin;

        return $this;
    }

    public function isShowLicensePlate(): bool
    {
        return $this->showLicensePlate;
    }

    public function setShowLicensePlate(bool $showLicensePlate): static
    {
        $this->showLicensePlate = $showLicensePlate;

        return $this;
    }

    public function isShowMileage(): bool
    {
        return $this->showMileage;
    }

    public function setShowMileage(bool $showMileage): static
    {
        $this->showMileage = $showMileage;

        return $this;
    }

    public function isPhoneRequired(): bool
    {
        return $this->phoneRequired;
    }

    public function setPhoneRequired(bool $phoneRequired): static
    {
        $this->phoneRequired = $phoneRequired;

        return $this;
    }

    public function isEmailRequired(): bool
    {
        return $this->emailRequired;
    }

    public function setEmailRequired(bool $emailRequired): static
    {
        $this->emailRequired = $emailRequired;

        return $this;
    }

    public function isPrivacyRequired(): bool
    {
        return $this->privacyRequired;
    }

    public function setPrivacyRequired(bool $privacyRequired): static
    {
        $this->privacyRequired = $privacyRequired;

        return $this;
    }

    public function getPrivacyUrl(): ?string
    {
        return $this->privacyUrl;
    }

    public function setPrivacyUrl(?string $privacyUrl): static
    {
        $this->privacyUrl = trim((string) $privacyUrl) ?: null;

        return $this;
    }

    public function getPrivacyTextHtml(): ?string
    {
        return $this->privacyTextHtml;
    }

    public function setPrivacyTextHtml(?string $privacyTextHtml): static
    {
        $this->privacyTextHtml = $privacyTextHtml;

        return $this;
    }

    public function getRedirectAfterSubmit(): ?string
    {
        return $this->redirectAfterSubmit;
    }

    public function setRedirectAfterSubmit(?string $redirectAfterSubmit): static
    {
        $this->redirectAfterSubmit = trim((string) $redirectAfterSubmit) ?: null;

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
        $this->typeIconColor = in_array($typeIconColor, ['white', 'black'], true) ? $typeIconColor : 'white';

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
        $themeColors = $this->getResolvedWidgetColors();

        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'buttonText' => $this->buttonText,
            'successTitle' => $this->successTitle,
            'successMessage' => $this->successMessage,
            'primaryColor' => $themeColors['primaryColor'],
            'buttonTextColor' => self::getReadableForegroundColor($themeColors['primaryColor']),
            'backgroundColor' => $themeColors['backgroundColor'],
            'textColor' => $themeColors['textColor'],
            'selectBackgroundColor' => $themeColors['selectBackgroundColor'],
            'selectOpenBackgroundColor' => $themeColors['selectOpenBackgroundColor'],
            'customCss' => $this->customCss,
            'fields' => [
                'phone' => ['visible' => $this->showPhone, 'required' => $this->phoneRequired],
                'email' => ['visible' => $this->showEmail, 'required' => $this->emailRequired],
                'finVin' => ['visible' => $this->showFinVin, 'required' => false],
                'licensePlate' => ['visible' => $this->showLicensePlate, 'required' => false],
                'mileage' => ['visible' => $this->showMileage, 'required' => false],
            ],
            'privacy' => [
                'required' => $this->privacyRequired,
                'url' => $this->privacyUrl,
                'textHtml' => $this->privacyTextHtml,
            ],
            'redirectAfterSubmit' => $this->redirectAfterSubmit,
            'sendTrackingEvents' => $this->sendTrackingEvents,
            'showPerformanceChart' => $this->showPerformanceChart,
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

    private function applyPremiumLightDefaults(): void
    {
        if ('#5a2116' === strtolower($this->primaryColor)) {
            $this->primaryColor = '#d53b2a';
        }
    }

    private function applyCatalogLightDefaults(): void
    {
        if ('#5a2116' === strtolower($this->primaryColor)) {
            $this->primaryColor = '#c4933c';
        }

        if ('#ffffff' === strtolower($this->backgroundColor)) {
            $this->backgroundColor = '#ffffff';
        }

        if ('#1a1a1a' === strtolower($this->textColor)) {
            $this->textColor = '#171d2a';
        }

        if ('#ffffff' === strtolower($this->selectBackgroundColor)) {
            $this->selectBackgroundColor = '#ffffff';
        }

        if ('#f3f4f6' === strtolower($this->selectOpenBackgroundColor)) {
            $this->selectOpenBackgroundColor = '#f7f8fb';
        }
    }

    private function applyPremiumDarkDefaults(): void
    {
        if ('#5a2116' === strtolower($this->primaryColor)) {
            $this->primaryColor = '#d53b2a';
        }

        if ('#ffffff' === strtolower($this->backgroundColor)) {
            $this->backgroundColor = '#07090d';
        }

        if ('#1a1a1a' === strtolower($this->textColor)) {
            $this->textColor = '#f4f7fa';
        }

        if ('#ffffff' === strtolower($this->selectBackgroundColor)) {
            $this->selectBackgroundColor = '#111722';
        }

        if ('#f3f4f6' === strtolower($this->selectOpenBackgroundColor)) {
            $this->selectOpenBackgroundColor = '#182131';
        }
    }

    private static function getReadableForegroundColor(string $backgroundColor): string
    {
        $hex = ltrim(trim($backgroundColor), '#');

        if (3 === strlen($hex)) {
            $hex = sprintf(
                '%s%s%s%s%s%s',
                $hex[0],
                $hex[0],
                $hex[1],
                $hex[1],
                $hex[2],
                $hex[2],
            );
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

    /**
     * @return array{primaryColor: string, backgroundColor: string, textColor: string, selectBackgroundColor: string, selectOpenBackgroundColor: string}
     */
    private function getResolvedWidgetColors(): array
    {
        $variant = $this->getTemplateVariant();

        if (VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_DARK === $variant) {
            return [
                'primaryColor' => '#5a2116' === strtolower($this->primaryColor) ? '#d53b2a' : $this->primaryColor,
                'backgroundColor' => '#ffffff' === strtolower($this->backgroundColor) ? '#07090d' : $this->backgroundColor,
                'textColor' => '#1a1a1a' === strtolower($this->textColor) ? '#f4f7fa' : $this->textColor,
                'selectBackgroundColor' => '#ffffff' === strtolower($this->selectBackgroundColor) ? '#111722' : $this->selectBackgroundColor,
                'selectOpenBackgroundColor' => '#f3f4f6' === strtolower($this->selectOpenBackgroundColor) ? '#182131' : $this->selectOpenBackgroundColor,
            ];
        }

        if (VehicleFrontendSettings::TEMPLATE_WIDGET_PREMIUM_LIGHT === $variant) {
            return [
                'primaryColor' => '#5a2116' === strtolower($this->primaryColor) ? '#d53b2a' : $this->primaryColor,
                'backgroundColor' => $this->backgroundColor,
                'textColor' => $this->textColor,
                'selectBackgroundColor' => $this->selectBackgroundColor,
                'selectOpenBackgroundColor' => $this->selectOpenBackgroundColor,
            ];
        }

        if (VehicleFrontendSettings::TEMPLATE_WIDGET_CATALOG_LIGHT === $variant) {
            return [
                'primaryColor' => '#5a2116' === strtolower($this->primaryColor) ? '#c4933c' : $this->primaryColor,
                'backgroundColor' => $this->backgroundColor,
                'textColor' => '#1a1a1a' === strtolower($this->textColor) ? '#171d2a' : $this->textColor,
                'selectBackgroundColor' => $this->selectBackgroundColor,
                'selectOpenBackgroundColor' => '#f3f4f6' === strtolower($this->selectOpenBackgroundColor) ? '#f7f8fb' : $this->selectOpenBackgroundColor,
            ];
        }

        return [
            'primaryColor' => $this->primaryColor,
            'backgroundColor' => $this->backgroundColor,
            'textColor' => $this->textColor,
            'selectBackgroundColor' => $this->selectBackgroundColor,
            'selectOpenBackgroundColor' => $this->selectOpenBackgroundColor,
        ];
    }

    private static function normalizeTemplateVariant(string $variant): string
    {
        $variant = strtolower(trim($variant));

        return VehicleFrontendSettings::TEMPLATE_LAYOUT2 === $variant
            ? VehicleFrontendSettings::TEMPLATE_CONTRAST
            : $variant;
    }
}
