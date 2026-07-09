<?php

namespace App\Entity;

use App\Repository\VehicleFrontendSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleFrontendSettingsRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_vehicle_frontend_settings')]
class VehicleFrontendSettings
{
    public const TEMPLATE_PERFORMANCE = 'performance';
    public const TEMPLATE_MINIMAL = 'minimal';
    public const TEMPLATE_CONTRAST = 'contrast';
    public const TEMPLATE_LAYOUT2 = 'layout2';
    public const TEMPLATE_LAYOUT3 = 'layout3';
    public const TEMPLATE_LAYOUT4 = 'layout4';
    public const TEMPLATE_LAYOUT5 = 'layout5';
    public const TEMPLATE_LAYOUT6 = 'layout6';
    public const TEMPLATE_WIDGET_PREMIUM_DARK = 'widget_premium_dark';
    public const TEMPLATE_WIDGET_PREMIUM_LIGHT = 'widget_premium_light';
    public const TEMPLATE_WIDGET_CATALOG_LIGHT = 'widget_catalog_light';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_data_pool', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $vehicleDataPool = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(name: 'show_only_stage1')]
    private bool $showOnlyStage1 = false;

    #[ORM\Column(name: 'show_prices')]
    private bool $showPrices = true;

    #[ORM\Column(name: 'layout_switcher_enabled')]
    private bool $layoutSwitcherEnabled = false;

    #[ORM\Column(name: 'template_variant', length: 50)]
    private string $templateVariant = self::TEMPLATE_PERFORMANCE;

    #[ORM\Column(name: 'performance_glb_autorotate')]
    private bool $performanceGlbAutorotate = false;

    #[ORM\Column(name: 'performance_show_prices')]
    private bool $performanceShowPrices = true;

    #[ORM\Column(name: 'performance_show_logo')]
    private bool $performanceShowLogo = true;

    #[ORM\Column(name: 'performance_show_only_stage1')]
    private bool $performanceShowOnlyStage1 = false;

    #[ORM\Column(name: 'minimal_glb_autorotate')]
    private bool $minimalGlbAutorotate = false;

    #[ORM\Column(name: 'minimal_show_prices')]
    private bool $minimalShowPrices = true;

    #[ORM\Column(name: 'minimal_show_logo')]
    private bool $minimalShowLogo = true;

    #[ORM\Column(name: 'minimal_show_only_stage1')]
    private bool $minimalShowOnlyStage1 = false;

    #[ORM\Column(name: 'contrast_glb_autorotate')]
    private bool $contrastGlbAutorotate = false;

    #[ORM\Column(name: 'contrast_show_prices')]
    private bool $contrastShowPrices = true;

    #[ORM\Column(name: 'contrast_show_logo')]
    private bool $contrastShowLogo = false;

    #[ORM\Column(name: 'contrast_show_only_stage1')]
    private bool $contrastShowOnlyStage1 = false;

    #[ORM\Column(name: 'layout3_glb_autorotate')]
    private bool $layout3GlbAutorotate = false;

    #[ORM\Column(name: 'layout3_show_prices')]
    private bool $layout3ShowPrices = true;

    #[ORM\Column(name: 'layout3_show_logo')]
    private bool $layout3ShowLogo = false;

    #[ORM\Column(name: 'layout3_show_only_stage1')]
    private bool $layout3ShowOnlyStage1 = false;

    #[ORM\Column(name: 'layout4_glb_autorotate')]
    private bool $layout4GlbAutorotate = false;

    #[ORM\Column(name: 'layout4_show_prices')]
    private bool $layout4ShowPrices = true;

    #[ORM\Column(name: 'layout4_show_logo')]
    private bool $layout4ShowLogo = false;

    #[ORM\Column(name: 'layout4_show_only_stage1')]
    private bool $layout4ShowOnlyStage1 = false;

    public function __toString(): string
    {
        return $this->name ?: 'Frontend-Einstellungen';
    }

    /**
     * @return array<string, string>
     */
    public static function getTemplateChoices(): array
    {
        return [
            'Performance (Standard)' => self::TEMPLATE_PERFORMANCE,
            'Minimal' => self::TEMPLATE_MINIMAL,
            'Contrast (Neu)' => self::TEMPLATE_CONTRAST,
            'V3 (Sidebar)' => self::TEMPLATE_LAYOUT3,
            'V4 (Download)' => self::TEMPLATE_LAYOUT4,
            'V5 (Clean Table)' => self::TEMPLATE_LAYOUT5,
            'V6 (Sidebar Light)' => self::TEMPLATE_LAYOUT6,
        ];
    }

    public static function isValidTemplateVariant(string $variant): bool
    {
        return in_array(self::normalizeTemplateVariant($variant), self::getSupportedTemplateVariants(), true);
    }

    /**
     * @return list<string>
     */
    public static function getSupportedTemplateVariants(): array
    {
        return [
            self::TEMPLATE_PERFORMANCE,
            self::TEMPLATE_MINIMAL,
            self::TEMPLATE_CONTRAST,
            self::TEMPLATE_LAYOUT3,
            self::TEMPLATE_LAYOUT4,
            self::TEMPLATE_LAYOUT5,
            self::TEMPLATE_LAYOUT6,
            self::TEMPLATE_WIDGET_PREMIUM_DARK,
            self::TEMPLATE_WIDGET_PREMIUM_LIGHT,
            self::TEMPLATE_WIDGET_CATALOG_LIGHT,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
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

    public function isShowOnlyStage1(): bool
    {
        return $this->showOnlyStage1;
    }

    public function setShowOnlyStage1(bool $showOnlyStage1): static
    {
        $this->showOnlyStage1 = $showOnlyStage1;

        return $this;
    }

    public function isShowPrices(): bool
    {
        return $this->showPrices;
    }

    public function setShowPrices(bool $showPrices): static
    {
        $this->showPrices = $showPrices;

        return $this;
    }

    public function isLayoutSwitcherEnabled(): bool
    {
        return $this->layoutSwitcherEnabled;
    }

    public function setLayoutSwitcherEnabled(bool $layoutSwitcherEnabled): static
    {
        $this->layoutSwitcherEnabled = $layoutSwitcherEnabled;

        return $this;
    }

    public function getTemplateVariant(): string
    {
        $variant = self::normalizeTemplateVariant($this->templateVariant);

        if (!self::isValidTemplateVariant($variant)) {
            return self::TEMPLATE_PERFORMANCE;
        }

        return $variant;
    }

    public function setTemplateVariant(string $templateVariant): static
    {
        $variant = self::normalizeTemplateVariant($templateVariant);

        $this->templateVariant = self::isValidTemplateVariant($variant)
            ? $variant
            : self::TEMPLATE_PERFORMANCE;

        return $this;
    }

    public function isPerformanceGlbAutorotate(): bool
    {
        return $this->performanceGlbAutorotate;
    }

    public function setPerformanceGlbAutorotate(bool $performanceGlbAutorotate): static
    {
        $this->performanceGlbAutorotate = $performanceGlbAutorotate;

        return $this;
    }

    public function isPerformanceShowPrices(): bool
    {
        return $this->performanceShowPrices;
    }

    public function setPerformanceShowPrices(bool $performanceShowPrices): static
    {
        $this->performanceShowPrices = $performanceShowPrices;

        return $this;
    }

    public function isPerformanceShowLogo(): bool
    {
        return $this->performanceShowLogo;
    }

    public function setPerformanceShowLogo(bool $performanceShowLogo): static
    {
        $this->performanceShowLogo = $performanceShowLogo;

        return $this;
    }

    public function isPerformanceShowOnlyStage1(): bool
    {
        return $this->performanceShowOnlyStage1;
    }

    public function setPerformanceShowOnlyStage1(bool $performanceShowOnlyStage1): static
    {
        $this->performanceShowOnlyStage1 = $performanceShowOnlyStage1;

        return $this;
    }

    public function isMinimalGlbAutorotate(): bool
    {
        return $this->minimalGlbAutorotate;
    }

    public function setMinimalGlbAutorotate(bool $minimalGlbAutorotate): static
    {
        $this->minimalGlbAutorotate = $minimalGlbAutorotate;

        return $this;
    }

    public function isMinimalShowPrices(): bool
    {
        return $this->minimalShowPrices;
    }

    public function setMinimalShowPrices(bool $minimalShowPrices): static
    {
        $this->minimalShowPrices = $minimalShowPrices;

        return $this;
    }

    public function isMinimalShowLogo(): bool
    {
        return $this->minimalShowLogo;
    }

    public function setMinimalShowLogo(bool $minimalShowLogo): static
    {
        $this->minimalShowLogo = $minimalShowLogo;

        return $this;
    }

    public function isMinimalShowOnlyStage1(): bool
    {
        return $this->minimalShowOnlyStage1;
    }

    public function setMinimalShowOnlyStage1(bool $minimalShowOnlyStage1): static
    {
        $this->minimalShowOnlyStage1 = $minimalShowOnlyStage1;

        return $this;
    }

    public function isContrastGlbAutorotate(): bool
    {
        return $this->contrastGlbAutorotate;
    }

    public function setContrastGlbAutorotate(bool $contrastGlbAutorotate): static
    {
        $this->contrastGlbAutorotate = $contrastGlbAutorotate;

        return $this;
    }

    public function isContrastShowPrices(): bool
    {
        return $this->contrastShowPrices;
    }

    public function setContrastShowPrices(bool $contrastShowPrices): static
    {
        $this->contrastShowPrices = $contrastShowPrices;

        return $this;
    }

    public function isContrastShowLogo(): bool
    {
        return $this->contrastShowLogo;
    }

    public function setContrastShowLogo(bool $contrastShowLogo): static
    {
        $this->contrastShowLogo = $contrastShowLogo;

        return $this;
    }

    public function isContrastShowOnlyStage1(): bool
    {
        return $this->contrastShowOnlyStage1;
    }

    public function setContrastShowOnlyStage1(bool $contrastShowOnlyStage1): static
    {
        $this->contrastShowOnlyStage1 = $contrastShowOnlyStage1;

        return $this;
    }

    public function isLayout3GlbAutorotate(): bool
    {
        return $this->layout3GlbAutorotate;
    }

    public function setLayout3GlbAutorotate(bool $layout3GlbAutorotate): static
    {
        $this->layout3GlbAutorotate = $layout3GlbAutorotate;

        return $this;
    }

    public function isLayout3ShowPrices(): bool
    {
        return $this->layout3ShowPrices;
    }

    public function setLayout3ShowPrices(bool $layout3ShowPrices): static
    {
        $this->layout3ShowPrices = $layout3ShowPrices;

        return $this;
    }

    public function isLayout3ShowLogo(): bool
    {
        return $this->layout3ShowLogo;
    }

    public function setLayout3ShowLogo(bool $layout3ShowLogo): static
    {
        $this->layout3ShowLogo = $layout3ShowLogo;

        return $this;
    }

    public function isLayout3ShowOnlyStage1(): bool
    {
        return $this->layout3ShowOnlyStage1;
    }

    public function setLayout3ShowOnlyStage1(bool $layout3ShowOnlyStage1): static
    {
        $this->layout3ShowOnlyStage1 = $layout3ShowOnlyStage1;

        return $this;
    }

    public function isLayout4GlbAutorotate(): bool
    {
        return $this->layout4GlbAutorotate;
    }

    public function setLayout4GlbAutorotate(bool $layout4GlbAutorotate): static
    {
        $this->layout4GlbAutorotate = $layout4GlbAutorotate;

        return $this;
    }

    public function isLayout4ShowPrices(): bool
    {
        return $this->layout4ShowPrices;
    }

    public function setLayout4ShowPrices(bool $layout4ShowPrices): static
    {
        $this->layout4ShowPrices = $layout4ShowPrices;

        return $this;
    }

    public function isLayout4ShowLogo(): bool
    {
        return $this->layout4ShowLogo;
    }

    public function setLayout4ShowLogo(bool $layout4ShowLogo): static
    {
        $this->layout4ShowLogo = $layout4ShowLogo;

        return $this;
    }

    public function isLayout4ShowOnlyStage1(): bool
    {
        return $this->layout4ShowOnlyStage1;
    }

    public function setLayout4ShowOnlyStage1(bool $layout4ShowOnlyStage1): static
    {
        $this->layout4ShowOnlyStage1 = $layout4ShowOnlyStage1;

        return $this;
    }

    /**
     * @return array{
     *     glbAutorotate: bool,
     *     showPrices: bool,
     *     showLogo: bool,
     *     showOnlyStage1: bool
     * }
     */
    public function getLayoutSettings(string $variant): array
    {
        $normalized = self::normalizeTemplateVariant($variant);

        return match ($normalized) {
            self::TEMPLATE_MINIMAL => [
                'glbAutorotate' => $this->minimalGlbAutorotate,
                'showPrices' => $this->minimalShowPrices,
                'showLogo' => $this->minimalShowLogo,
                'showOnlyStage1' => $this->minimalShowOnlyStage1,
            ],
            self::TEMPLATE_CONTRAST => [
                'glbAutorotate' => $this->contrastGlbAutorotate,
                'showPrices' => $this->contrastShowPrices,
                'showLogo' => $this->contrastShowLogo,
                'showOnlyStage1' => $this->contrastShowOnlyStage1,
            ],
            self::TEMPLATE_LAYOUT3 => [
                'glbAutorotate' => $this->layout3GlbAutorotate,
                'showPrices' => $this->layout3ShowPrices,
                'showLogo' => $this->layout3ShowLogo,
                'showOnlyStage1' => $this->layout3ShowOnlyStage1,
            ],
            self::TEMPLATE_LAYOUT4 => [
                'glbAutorotate' => $this->layout4GlbAutorotate,
                'showPrices' => $this->layout4ShowPrices,
                'showLogo' => $this->layout4ShowLogo,
                'showOnlyStage1' => $this->layout4ShowOnlyStage1,
            ],
            self::TEMPLATE_LAYOUT5,
            self::TEMPLATE_LAYOUT6 => [
                'glbAutorotate' => $this->layout4GlbAutorotate,
                'showPrices' => $this->layout4ShowPrices,
                'showLogo' => $this->layout4ShowLogo,
                'showOnlyStage1' => $this->layout4ShowOnlyStage1,
            ],
            default => [
                'glbAutorotate' => $this->performanceGlbAutorotate,
                'showPrices' => $this->performanceShowPrices,
                'showLogo' => $this->performanceShowLogo,
                'showOnlyStage1' => $this->performanceShowOnlyStage1,
            ],
        };
    }

    /**
     * @return array<string, array{
     *     glbAutorotate: bool,
     *     showPrices: bool,
     *     showLogo: bool,
     *     showOnlyStage1: bool
     * }>
     */
    public function getAllLayoutSettings(): array
    {
        return [
            self::TEMPLATE_PERFORMANCE => $this->getLayoutSettings(self::TEMPLATE_PERFORMANCE),
            self::TEMPLATE_MINIMAL => $this->getLayoutSettings(self::TEMPLATE_MINIMAL),
            self::TEMPLATE_CONTRAST => $this->getLayoutSettings(self::TEMPLATE_CONTRAST),
            self::TEMPLATE_LAYOUT3 => $this->getLayoutSettings(self::TEMPLATE_LAYOUT3),
            self::TEMPLATE_LAYOUT4 => $this->getLayoutSettings(self::TEMPLATE_LAYOUT4),
            self::TEMPLATE_LAYOUT5 => $this->getLayoutSettings(self::TEMPLATE_LAYOUT5),
            self::TEMPLATE_LAYOUT6 => $this->getLayoutSettings(self::TEMPLATE_LAYOUT6),
        ];
    }

    private static function normalizeTemplateVariant(string $variant): string
    {
        $normalized = strtolower(trim($variant));

        if (self::TEMPLATE_LAYOUT2 === $normalized) {
            return self::TEMPLATE_CONTRAST;
        }

        return $normalized;
    }
}
