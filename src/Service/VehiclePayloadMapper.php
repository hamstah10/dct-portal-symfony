<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Ecu;
use App\Entity\ToolManufacturer;
use App\Entity\ToolMethode;
use App\Entity\EcuOption;
use App\Entity\EcuOptionHint;
use App\Entity\EinbauHinweis;
use App\Entity\Gearbox;
use App\Entity\MediaAsset;
use App\Entity\PerformanceBrand;
use App\Entity\VehicleEngine;
use App\Entity\VehicleFrontendSettings;
use App\Entity\VehicleStage;
use App\Repository\EinbauHinweisRepository;
use App\Repository\VehicleFrontendSettingsRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class VehiclePayloadMapper
{
    public function __construct(
        private readonly VehicleFrontendSettingsRepository $vehicleFrontendSettingsRepository,
        private readonly EcuHintsResolver $ecuHintsResolver,
        private readonly EinbauHinweisRepository $einbauHinweisRepository,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir = '',
    ) {
    }

    public function mapStagePayload(VehicleStage $stage, bool $showPrices, bool $showDyno = true): array
    {
        return [
            'id' => $stage->getId(),
            'name' => $stage->getName(),
            'description' => $stage->getDescription(),
            'price' => $showPrices ? $stage->getPrice() : null,
            'hp' => $stage->getHp(),
            'nm' => $stage->getNm(),
            'ecu' => $stage->getEcu(),
            'icon' => $stage->getResolvedIconPath(),
            'images' => self::toPathList($stage->getImages()),
            'videos' => $stage->getVideos(),
            'dynoChart' => $showDyno ? $stage->getDynoChart() : null,
            'slug' => $stage->getSlug(),
            'originalPs' => $stage->getOriginalPs(),
            'originalNm' => $stage->getOriginalNm(),
            'rpmStart' => $stage->getRpmStart(),
            'rpmEnd' => $stage->getRpmEnd(),
            'engineType' => $stage->getEngineType(),
            'active' => $stage->isActive(),
        ];
    }

    /**
     * @param array<int, VehicleStage> $stages
     *
     * @return array<int, array<string, mixed>>
     */
    public function mapStagePayloads(array $stages, bool $showPrices, bool $showDyno = true): array
    {
        return array_values(array_map(fn (VehicleStage $stage): array => $this->mapStagePayload($stage, $showPrices, $showDyno), $stages));
    }

    /**
     * @param array<int, Ecu> $ecus
     *
     * @return array<int, array<string, mixed>>
     */
    public function mapEcuPayloads(array $ecus, bool $showPrices, bool $showHints = true, bool $showOptionCategories = true, ?VehicleEngine $engine = null): array
    {
        return array_values(array_map(fn (Ecu $ecu): array => [
            'id' => $ecu->getId(),
            'name' => $this->resolveEcuName($ecu),
            'type' => $this->resolveEcuType($ecu),
            'controlUnitKind' => $this->resolveEcuControlUnitKind($ecu),
            'manufacturer' => $this->resolveEcuManufacturerName($ecu),
            'manufacturerId' => $ecu->getManufacturer()?->getId(),
            'toolManufacturerId' => $this->activeToolManufacturer($ecu->getToolManufacturer())?->getId(),
            'manufacturerLogo' => $this->normalizePublicPath($this->resolveEcuManufacturerLogo($ecu)),
            'description' => $ecu->getDescription(),
            'image' => $this->normalizePublicPath($ecu->getImage()),
            'toolEntries' => array_values(array_map(fn ($entry) => [
                'id' => $entry->getId(),
                'manufacturer' => $this->activeToolManufacturer($entry->getToolManufacturer())?->getName(),
                'manufacturerLogo' => $this->normalizePublicPath($this->activeToolManufacturer($entry->getToolManufacturer())?->getLogo()),
                'toolManufacturerId' => $this->activeToolManufacturer($entry->getToolManufacturer())?->getId(),
                'methodIds' => $this->activeToolMethods($entry->getToolMethods())->map(fn ($m) => $m->getId())->getValues(),
                'methods' => $this->activeToolMethods($entry->getToolMethods())->map(fn ($m) => [
                    'name' => $m->getName(),
                    'icon' => $this->normalizePublicPath($m->getImage()),
                ])->getValues(),
                'position' => $entry->getPosition(),
                'kind' => 'ecu',
            ], $this->activeToolEntries($ecu->getToolEntries()->toArray()))),
            'toolManufacturer' => $this->activeToolManufacturer($ecu->getToolManufacturer())?->getName(),
            'toolManufacturerLogo' => $this->normalizePublicPath($this->activeToolManufacturer($ecu->getToolManufacturer())?->getLogo()),
            'toolMethods' => $this->activeToolMethods($ecu->getToolMethods())->map(fn ($m) => [
                'name' => $m->getName(),
                'icon' => $this->normalizePublicPath($m->getImage()),
            ])->getValues(),
            'options' => $this->mapEcuOptionPayloads($ecu, $showPrices, $showOptionCategories, $engine),
            'optionHints' => $showHints ? $this->mapEcuOptionHints($ecu, $engine) : [],
        ], $ecus));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapEcuOptionPayloads(Ecu $ecu, bool $showPrices, bool $showOptionCategories = true, ?VehicleEngine $engine = null): array
    {
        $options = array_values(array_filter(
            $ecu->getOptions()->toArray(),
            static fn (mixed $option): bool => $option instanceof EcuOption
                && null !== $option->getId()
                && $option->isActive()
                && $option->getVehicleDataPool()?->getId() === $ecu->getVehicleDataPool()?->getId()
        ));
        $options = $this->filterOptionsForEngineFuel($options, $engine);

        return $this->mapOptionPayloads($options, $showPrices, $showOptionCategories);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapEngineOptionPayloads(VehicleEngine $engine, bool $showPrices, bool $showOptionCategories = true): array
    {
        $options = array_values(array_filter(
            $engine->getOptions()->toArray(),
            static fn (mixed $option): bool => $option instanceof EcuOption
                && null !== $option->getId()
                && $option->isActive()
                && $option->getVehicleDataPool()?->getId() === $engine->getVehicleDataPool()?->getId()
        ));
        $options = $this->filterOptionsForEngineFuel($options, $engine);

        return $this->mapOptionPayloads($options, $showPrices, $showOptionCategories);
    }

    /**
     * @param array<int, EcuOption> $options
     *
     * @return array<int, EcuOption>
     */
    private function filterOptionsForEngineFuel(array $options, ?VehicleEngine $engine): array
    {
        if (!$this->isPetrolEngine($engine) && !$this->isDieselEngine($engine)) {
            return $options;
        }

        return array_values(array_filter(
            $options,
            fn (EcuOption $option): bool => !(
                ($this->isPetrolEngine($engine) && $this->isDpfOption($option))
                || ($this->isDieselEngine($engine) && $this->isPopsAndBangsOption($option))
            )
        ));
    }

    private function isPetrolEngine(?VehicleEngine $engine): bool
    {
        $fuel = mb_strtolower(trim((string) $engine?->getFuel()));
        if ('' === $fuel || str_contains($fuel, 'diesel')) {
            return false;
        }

        return str_contains($fuel, 'benzin')
            || str_contains($fuel, 'petrol')
            || str_contains($fuel, 'gasoline')
            || str_contains($fuel, 'otto')
            || str_contains($fuel, 'super');
    }

    private function isDieselEngine(?VehicleEngine $engine): bool
    {
        return str_contains(mb_strtolower(trim((string) $engine?->getFuel())), 'diesel');
    }

    private function isDpfOption(EcuOption $option): bool
    {
        $master = $option->getMasterOption();
        $haystack = implode(' ', array_filter([
            $option->getName(),
            $option->getSlug(),
            $master?->getName(),
            $master?->getSlug(),
            $master?->getModuleSlug(),
        ], static fn (?string $value): bool => null !== $value && '' !== trim($value)));

        return $this->containsDpfReference($haystack);
    }

    private function isPopsAndBangsOption(EcuOption $option): bool
    {
        $master = $option->getMasterOption();
        $haystack = implode(' ', array_filter([
            $option->getName(),
            $option->getSlug(),
            $master?->getName(),
            $master?->getSlug(),
            $master?->getModuleSlug(),
        ], static fn (?string $value): bool => null !== $value && '' !== trim($value)));

        return $this->containsPopsAndBangsReference($haystack);
    }

    private function isDpfMasterOption(\App\Entity\EcuOptionMaster $master): bool
    {
        $haystack = implode(' ', array_filter([
            $master->getName(),
            $master->getSlug(),
            $master->getModuleSlug(),
        ], static fn (?string $value): bool => null !== $value && '' !== trim($value)));

        return $this->containsDpfReference($haystack);
    }

    private function isPopsAndBangsMasterOption(\App\Entity\EcuOptionMaster $master): bool
    {
        $haystack = implode(' ', array_filter([
            $master->getName(),
            $master->getSlug(),
            $master->getModuleSlug(),
        ], static fn (?string $value): bool => null !== $value && '' !== trim($value)));

        return $this->containsPopsAndBangsReference($haystack);
    }

    private function containsDpfReference(string $haystack): bool
    {
        return preg_match('/(^|[^a-z0-9])dpf([^a-z0-9]|$)/i', $haystack) === 1
            || preg_match('/diesel\\s*[- ]?\\s*partikelfilter/i', $haystack) === 1;
    }

    private function containsPopsAndBangsReference(string $haystack): bool
    {
        return preg_match('/pop\\s*&?\\s*bang/i', $haystack) === 1
            || preg_match('/pops\\s*&?\\s*bangs/i', $haystack) === 1
            || preg_match('/popn?bang/i', $haystack) === 1;
    }

    /**
     * @param array<int, EcuOption> $options
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapOptionPayloads(array $options, bool $showPrices, bool $showOptionCategories): array
    {
        usort($options, fn (EcuOption $a, EcuOption $b): int => strnatcasecmp($this->resolveEcuOptionName($a), $this->resolveEcuOptionName($b)));

        return array_values(array_map(function (EcuOption $option) use ($showPrices, $showOptionCategories): array {
            $payload = [
                'id' => $option->getId(),
                'name' => $this->resolveEcuOptionName($option),
                'masterOptionName' => $option->getMasterOption()?->getName(),
                'description' => $this->resolveEcuOptionDescription($option),
                'icon' => $this->normalizePublicPath($this->resolveEcuOptionIcon($option)),
                'price' => $showPrices ? $option->getPrice() : null,
                'isMainOption' => $option->isMainOption(),
                'slug' => $option->getSlug(),
                'tooltipText' => $option->getTooltipText(),
                'active' => $option->isActive(),
            ];

            if ($showOptionCategories) {
                $category = $option->getCategoryMaster() ?? $option->getMasterOption()?->getCategoryMaster();
                $payload['category'] = $category instanceof \App\Entity\EcuOptionCategoryMaster ? [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'slug' => $category->getSlug(),
                    'icon' => $category->getResolvedIconPath(),
                    'sorting' => $category->getSorting(),
                ] : null;
            }

            return $payload;
        }, $options));
    }

    /**
     * @return array<int, array{masterOption: string, hints: array<int, array<string, string|null>>}>
     */
    public function mapEcuOptionHints(Ecu $ecu, ?VehicleEngine $engine = null): array
    {
        $groups = $this->ecuHintsResolver->resolveForEcu($ecu);

        return array_values(array_map(fn (array $group): array => [
            'masterOption' => $group['master']->getName(),
            'hints' => array_values(array_map(fn (EcuOptionHint $h): array => [
                'brandName' => $h->getBrandName(),
                'manufacturer' => $h->getManufacturer(),
                'model' => $h->getModel(),
                'notes' => $h->getNotesDe() ?? $h->getNotes(),
            ], $group['hints'])),
        ], array_filter(
            $groups,
            fn (array $group): bool => !(
                ($this->isPetrolEngine($engine) && $this->isDpfMasterOption($group['master']))
                || ($this->isDieselEngine($engine) && $this->isPopsAndBangsMasterOption($group['master']))
            )
        )));
    }

    /**
     * @param array<int, Gearbox> $gearboxes
     *
     * @return array<int, array<string, mixed>>
     */
    public function mapGearboxPayloads(array $gearboxes): array
    {
        return array_values(array_map(fn (Gearbox $gearbox): array => [
            'id' => $gearbox->getId(),
            'title' => $gearbox->getTitle(),
            'description' => $gearbox->getDescription(),
            'icon' => $this->normalizePublicPath($gearbox->getResolvedIconPath()),
            'images' => self::toPathList($gearbox->getImages()),
            'type' => $gearbox->getGearboxType()?->getTitle(),
            'typeId' => $gearbox->getGearboxType()?->getId(),
            'manufacturer' => $gearbox->getManufacturer()?->getName(),
            'manufacturerId' => $gearbox->getManufacturer()?->getId(),
            'manufacturerLogo' => $this->normalizePublicPath($gearbox->getManufacturer()?->getLogo()),
            'sorting' => $gearbox->getSorting(),
            'active' => $gearbox->isActive(),
            'toolEntries' => array_values(array_map(fn ($entry) => [
                'id' => $entry->getId(),
                'manufacturer' => $this->activeToolManufacturer($entry->getToolManufacturer())?->getName(),
                'manufacturerLogo' => $this->normalizePublicPath($this->activeToolManufacturer($entry->getToolManufacturer())?->getLogo()),
                'toolManufacturerId' => $this->activeToolManufacturer($entry->getToolManufacturer())?->getId(),
                'methodIds' => $this->activeToolMethods($entry->getToolMethods())->map(fn ($m) => $m->getId())->getValues(),
                'methods' => $this->activeToolMethods($entry->getToolMethods())->map(fn ($m) => [
                    'name' => $m->getName(),
                    'icon' => $this->normalizePublicPath($m->getImage()),
                ])->getValues(),
                'position' => $entry->getPosition(),
                'kind' => 'gearbox',
            ], $this->activeToolEntries($gearbox->getToolEntries()->toArray()))),
        ], $gearboxes));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapEinbauHinweise(VehicleEngine $engine): array
    {
        $hinweise = $this->einbauHinweisRepository->findForEngine($engine);

        return array_values(array_map(function (EinbauHinweis $h): array {
            $activeEcus = [];
            foreach ($h->getActiveEcus() as $ecu) {
                if ($ecu instanceof \App\Entity\Ecu && null !== $ecu->getId()) {
                    $activeEcus[$ecu->getId()] = [
                        'id' => $ecu->getId(),
                        'name' => $ecu->getName(),
                    ];
                }
            }

            $legacyEcu = $h->getActiveEcu();
            if ($legacyEcu instanceof \App\Entity\Ecu && null !== $legacyEcu->getId()) {
                $activeEcus[$legacyEcu->getId()] = [
                    'id' => $legacyEcu->getId(),
                    'name' => $legacyEcu->getName(),
                ];
            }

            $firstActiveEcu = reset($activeEcus) ?: null;

            return [
                'id' => $h->getId(),
                'title' => $h->getTitle(),
                'description' => $h->getDescription(),
                'ecuOptionMaster' => $h->getEcuOptionMaster()?->getName(),
                'activeEcuId' => $firstActiveEcu['id'] ?? null,
                'activeEcuName' => $firstActiveEcu['name'] ?? null,
                'activeEcus' => array_values($activeEcus),
                'images' => array_values(array_map(
                    fn (MediaAsset $a): array => [
                        'src' => $this->normalizePublicPath($a->getFilePath()) ?? '',
                        'annotations' => $a->getAnnotations() ?? [],
                    ],
                    $h->getImages()->toArray()
                )),
            ];
        }, $hinweise));
    }

    /**
     * @param array<int, VehicleStage> $stages
     *
     * @return array<int, VehicleStage>
     */
    public function filterStagesBySettings(array $stages, bool $showOnlyStage1): array
    {
        if (!$showOnlyStage1 || [] === $stages) {
            return $stages;
        }

        foreach ($stages as $stage) {
            if (preg_match('/\bstage\s*1\b/i', $stage->getName()) === 1) {
                return [$stage];
            }
        }

        return [$stages[0]];
    }

    /**
     * @return array{
     *     layoutSwitcherEnabled: bool,
     *     templateVariant: string,
     *     layoutSettings: array<string, array{
     *         glbAutorotate: bool,
     *         showPrices: bool,
     *         showLogo: bool,
     *         showOnlyStage1: bool
     *     }>
     * }
     */
    public function buildFrontendSettingsPayload(?VehicleFrontendSettings $settings = null): array
    {
        $resolved = $settings ?? $this->vehicleFrontendSettingsRepository->getCurrentOrDefault();

        return [
            'layoutSwitcherEnabled' => $resolved->isLayoutSwitcherEnabled(),
            'templateVariant' => $resolved->getTemplateVariant(),
            'layoutSettings' => $resolved->getAllLayoutSettings(),
        ];
    }

    public function resolveFrontendSettingsForPool(mixed $pool): VehicleFrontendSettings
    {
        return $this->vehicleFrontendSettingsRepository->getCurrentOrDefaultForPool($pool);
    }

    /**
     * @param array{showPrices?: bool, showHints?: bool, showGearbox?: bool, showDyno?: bool, showEcoStage?: bool, showOptionCategories?: bool, allowVehicleEditing?: bool}|null $featureFlags
     *
     * @return array<string, mixed>
     */
    public function buildEngineDetailsData(VehicleEngine $engine, \App\Entity\VehicleDataPool $pool, ?string $requestedTemplateVariant = null, ?array $featureFlags = null): array
    {
        $featureFlags = array_merge([
            'showPrices' => true,
            'showHints' => true,
            'showGearbox' => true,
            'showDyno' => true,
            'showEcoStage' => true,
            'showOptionCategories' => true,
            'allowVehicleEditing' => false,
        ], $featureFlags ?? []);

        $model = $engine->getVehicleModel();
        $series = $model?->getVehicleSeries();
        $brand = $series?->getVehicleBrand();

        $settings = $this->resolveFrontendSettingsForPool($pool);
        $requestedVariant = trim((string) ($requestedTemplateVariant ?? ''));
        $templateVariant = '' !== $requestedVariant ? $requestedVariant : $settings->getTemplateVariant();
        $layoutSettings = $settings->getLayoutSettings($templateVariant);
        $showPrices = (bool) ($layoutSettings['showPrices'] ?? false) && $featureFlags['showPrices'];

        $stages = array_values(array_filter(
            $engine->getStages()->toArray(),
            static fn (VehicleStage $stage): bool => $stage->isActive() && $stage->getVehicleDataPool()?->getId() === $pool->getId()
        ));
        usort($stages, static fn (VehicleStage $a, VehicleStage $b): int => strnatcasecmp($a->getName(), $b->getName()));
        $stages = $this->filterStagesBySettings($stages, (bool) ($layoutSettings['showOnlyStage1'] ?? false));

        $allEcus = array_values(array_filter(
            $engine->getEcuRelations()->toArray(),
            static fn (mixed $ecu): bool => $ecu instanceof Ecu && null !== $ecu->getId() && $ecu->getVehicleDataPool()?->getId() === $pool->getId()
        ));
        usort($allEcus, static fn (Ecu $a, Ecu $b): int => strnatcasecmp($a->getName(), $b->getName()));

        $gearboxes = array_values(array_filter(
            $engine->getGearboxes()->toArray(),
            static fn (mixed $gearbox): bool => $gearbox instanceof Gearbox && null !== $gearbox->getId() && $gearbox->isActive() && $gearbox->getVehicleDataPool()?->getId() === $pool->getId()
        ));
        usort($gearboxes, static fn (Gearbox $a, Gearbox $b): int => strnatcasecmp($a->getTitle(), $b->getTitle()));

        return [
            'engine' => [
                'id' => $engine->getId(),
                'name' => $engine->getName(),
                'horsepower' => $engine->getHorsepower(),
                'torque' => $engine->getTorque(),
                'weight' => $engine->getWeight(),
                'drivetrain' => $engine->getDrivetrain(),
                'turbocharger' => $engine->hasTurbocharger(),
                'fuel' => $engine->getFuel(),
                'displacement' => $engine->getDisplacement(),
                'compression' => $engine->getCompression(),
                'boreStroke' => $engine->getBoreStroke(),
                'engineCode' => $engine->getEngineCode(),
                'image' => $engine->getImage(),
                'glbModel' => $engine->getGlbModel(),
                'mediaDisplayMode' => $engine->getMediaDisplayMode(),
                'performanceBrand' => $this->mapPerformanceBrand($engine->getPerformanceBrand()),
            ],
            'brand' => [
                'id' => $brand?->getId(),
                'name' => $this->resolveBrandName($brand),
                'image' => $this->normalizePublicPath($this->resolveBrandImage($brand)),
            ],
            'series' => [
                'id' => $series?->getId(),
                'name' => $series?->getName(),
                'description' => $series?->getDescription(),
                'slug' => $series?->getSlug(),
                'active' => $series?->isActive(),
                'sorting' => $series?->getSorting(),
                'performanceBrand' => $this->mapPerformanceBrand($series?->getPerformanceBrand()),
            ],
            'model' => [
                'id' => $model?->getId(),
                'name' => $model?->getName(),
                'description' => $model?->getDescription(),
                'slug' => $model?->getSlug(),
                'active' => $model?->isActive(),
                'sorting' => $model?->getSorting(),
            ],
            'stages' => $this->mapStagePayloads($stages, $showPrices, $featureFlags['showDyno']),
            'ecus' => $this->mapEcuPayloads($allEcus, $showPrices, $featureFlags['showHints'], $featureFlags['showOptionCategories'], $engine),
            'engineOptions' => $this->mapEngineOptionPayloads($engine, $showPrices, $featureFlags['showOptionCategories']),
            'gearboxes' => $featureFlags['showGearbox'] ? $this->mapGearboxPayloads($gearboxes) : [],
            'einbauHinweise' => $featureFlags['showHints'] ? $this->mapEinbauHinweise($engine) : [],
            'settings' => $this->buildFrontendSettingsPayload($settings),
            'editOptions' => $featureFlags['allowVehicleEditing'] ? [
                'ecus' => array_values(array_map(static fn (Ecu $item): array => [
                    'id' => $item->getId(),
                    'name' => $item->getName(),
                ], $allEcus)),
            ] : ['ecus' => []],
            'showPrices' => $showPrices,
            'showEcoStage' => $featureFlags['showEcoStage'],
            'showOptionCategories' => $featureFlags['showOptionCategories'],
            'allowVehicleEditing' => $featureFlags['allowVehicleEditing'],
            'editingAllowed' => $featureFlags['allowVehicleEditing'],
            'pool' => [
                'id' => $pool->getId(),
                'key' => $pool->getPoolKey(),
                'name' => $pool->getName(),
            ],
        ];
    }

    /** @return array{id: int, name: string, logo: ?string}|null */
    public function mapPerformanceBrand(?PerformanceBrand $performanceBrand): ?array
    {
        if (!$performanceBrand instanceof PerformanceBrand || null === $performanceBrand->getId()) {
            return null;
        }

        return [
            'id' => $performanceBrand->getId(),
            'name' => $performanceBrand->getName(),
            'logo' => $this->normalizePublicPath($performanceBrand->getResolvedImagePath()),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function toPathList(?string $value): array
    {
        if (null === $value || '' === trim($value)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(array_filter(array_map(fn (string $line): string => trim($line), $lines)));
    }

    public function resolveBrandName(mixed $brand): ?string
    {
        if (!is_object($brand) || !method_exists($brand, 'getName')) {
            return null;
        }

        return $this->preferText(
            $brand->getMasterBrand()?->getName(),
            $brand->getName()
        );
    }

    public function resolveBrandImage(mixed $brand): ?string
    {
        if (!is_object($brand) || !method_exists($brand, 'getImage')) {
            return null;
        }

        return $this->preferText(
            $brand->getMasterBrand()?->getResolvedImagePath(),
            $brand->getMasterBrand()?->getImage(),
            $brand->getImage()
        );
    }

    public function resolveEcuName(Ecu $ecu): string
    {
        return $this->preferText(
            $ecu->getMasterEcu()?->getName(),
            $ecu->getName()
        ) ?? '';
    }

    public function resolveEcuType(Ecu $ecu): ?string
    {
        return $this->preferText(
            $ecu->getMasterEcu()?->getType(),
            $ecu->getType()
        );
    }

    public function resolveEcuControlUnitKind(Ecu $ecu): ?string
    {
        return $this->preferText(
            $ecu->getMasterEcu()?->getControlUnitKind(),
            $ecu->getControlUnitKind()
        );
    }

    public function resolveEcuManufacturerName(Ecu $ecu): ?string
    {
        return $this->preferText(
            $ecu->getManufacturer()?->getMasterManufacturer()?->getName(),
            $ecu->getMasterEcu()?->getManufacturerMaster()?->getName(),
            $ecu->getManufacturer()?->getName()
        );
    }

    public function resolveEcuManufacturerLogo(Ecu $ecu): ?string
    {
        return $this->preferText(
            $ecu->getManufacturer()?->getMasterManufacturer()?->getLogo(),
            $ecu->getMasterEcu()?->getManufacturerMaster()?->getLogo(),
            $ecu->getManufacturer()?->getLogo()
        );
    }

    public function resolveEcuOptionName(EcuOption $option): string
    {
        return $this->preferText(
            $option->getMasterOption()?->getName(),
            $option->getName()
        ) ?? '';
    }

    public function resolveEcuOptionDescription(EcuOption $option): ?string
    {
        return $this->preferText(
            $option->getMasterOption()?->getDescription(),
            $option->getDescription()
        );
    }

    public function resolveEcuOptionIcon(EcuOption $option): ?string
    {
        return $this->preferText(
            $option->getMasterOption()?->getResolvedIconPath(),
            $option->getResolvedIconPath()
        );
    }

    public function preferText(?string ...$values): ?string
    {
        foreach ($values as $value) {
            $trimmed = trim((string) ($value ?? ''));
            if ('' !== $trimmed) {
                return $trimmed;
            }
        }

        return null;
    }

    /** Nur aktive Tool-Methoden ausgeben. */
    private function activeToolMethods(Collection $methods): Collection
    {
        return new ArrayCollection(array_values(array_filter(
            $methods->toArray(),
            static fn (mixed $method): bool => $method instanceof ToolMethode && $method->isActive()
        )));
    }

    /** Tool-Hersteller nur ausgeben, wenn er aktiv ist. */
    private function activeToolManufacturer(?ToolManufacturer $manufacturer): ?ToolManufacturer
    {
        return $manufacturer instanceof ToolManufacturer && $manufacturer->isActive() ? $manufacturer : null;
    }

    /**
     * Tool-Einträge inaktiver Hersteller entfallen, die ECU/das Getriebe bleibt erhalten.
     *
     * @param array<int, mixed> $entries
     * @return array<int, mixed>
     */
    private function activeToolEntries(array $entries): array
    {
        return array_values(array_filter(
            $entries,
            fn (mixed $entry): bool => $entry->getToolManufacturer() === null || $this->activeToolManufacturer($entry->getToolManufacturer()) !== null
        ));
    }

    public function normalizePublicPath(?string $path): ?string
    {
        $trimmed = trim((string) ($path ?? ''));
        if ('' === $trimmed) {
            return null;
        }

        if (preg_match('#^https?://#i', $trimmed) === 1) {
            return $trimmed;
        }

        if (str_starts_with($trimmed, '//') || str_contains($trimmed, '://')) {
            return null;
        }

        if (str_starts_with($trimmed, '/')) {
            return $trimmed;
        }

        return '/' . ltrim($trimmed, '/');
    }

    public function resolveUxIconDataUri(?string $iconId): ?string
    {
        $iconId = trim((string) ($iconId ?? ''));
        if ('' === $iconId || !str_contains($iconId, ':')) {
            return null;
        }

        [$collection, $iconName] = explode(':', $iconId, 2);
        if ('' === trim($collection) || '' === trim($iconName)) {
            return null;
        }

        $path = sprintf('%s/assets/icons/%s/%s.svg', $this->projectDir, trim($collection), trim($iconName));
        if (!is_file($path)) {
            return null;
        }

        $svg = @file_get_contents($path);
        if (false === $svg || '' === trim($svg)) {
            return null;
        }

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
