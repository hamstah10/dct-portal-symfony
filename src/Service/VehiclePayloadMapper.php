<?php

namespace App\Service;

use App\Entity\Ecu;
use App\Entity\EcuOption;
use App\Entity\EcuOptionHint;
use App\Entity\EinbauHinweis;
use App\Entity\Gearbox;
use App\Entity\MediaAsset;
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

    public function mapStagePayload(VehicleStage $stage, bool $showPrices): array
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
        ];
    }

    /**
     * @param array<int, VehicleStage> $stages
     *
     * @return array<int, array<string, mixed>>
     */
    public function mapStagePayloads(array $stages, bool $showPrices): array
    {
        return array_values(array_map(fn (VehicleStage $stage): array => $this->mapStagePayload($stage, $showPrices), $stages));
    }

    /**
     * @param array<int, Ecu> $ecus
     *
     * @return array<int, array<string, mixed>>
     */
    public function mapEcuPayloads(array $ecus, bool $showPrices): array
    {
        return array_values(array_map(fn (Ecu $ecu): array => [
            'id' => $ecu->getId(),
            'name' => $this->resolveEcuName($ecu),
            'type' => $this->resolveEcuType($ecu),
            'controlUnitKind' => $this->resolveEcuControlUnitKind($ecu),
            'manufacturer' => $this->resolveEcuManufacturerName($ecu),
            'manufacturerLogo' => $this->normalizePublicPath($this->resolveEcuManufacturerLogo($ecu)),
            'toolEntries' => array_values(array_map(fn ($entry) => [
                'manufacturer' => $entry->getToolManufacturer()?->getName(),
                'manufacturerLogo' => $this->normalizePublicPath($entry->getToolManufacturer()?->getLogo()),
                'methods' => $entry->getToolMethods()->map(fn ($m) => [
                    'name' => $m->getName(),
                    'icon' => $this->normalizePublicPath($m->getImage()),
                ])->getValues(),
            ], $ecu->getToolEntries()->toArray())),
            'toolManufacturer' => $ecu->getToolManufacturer()?->getName(),
            'toolManufacturerLogo' => $this->normalizePublicPath($ecu->getToolManufacturer()?->getLogo()),
            'toolMethods' => $ecu->getToolMethods()->map(fn ($m) => [
                'name' => $m->getName(),
                'icon' => $this->normalizePublicPath($m->getImage()),
            ])->getValues(),
            'options' => $this->mapEcuOptionPayloads($ecu, $showPrices),
            'optionHints' => $this->mapEcuOptionHints($ecu),
        ], $ecus));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapEcuOptionPayloads(Ecu $ecu, bool $showPrices): array
    {
        $options = array_values(array_filter(
            $ecu->getOptions()->toArray(),
            static fn (mixed $option): bool => $option instanceof EcuOption
                && null !== $option->getId()
                && $option->isActive()
                && $option->getVehicleDataPool()?->getId() === $ecu->getVehicleDataPool()?->getId()
        ));
        usort($options, fn (EcuOption $a, EcuOption $b): int => strnatcasecmp($this->resolveEcuOptionName($a), $this->resolveEcuOptionName($b)));

        return array_values(array_map(fn (EcuOption $option): array => [
            'id' => $option->getId(),
            'name' => $this->resolveEcuOptionName($option),
            'masterOptionName' => $option->getMasterOption()?->getName(),
            'description' => $this->resolveEcuOptionDescription($option),
            'icon' => $this->normalizePublicPath($this->resolveEcuOptionIcon($option)),
            'price' => $showPrices ? $option->getPrice() : null,
            'isMainOption' => $option->isMainOption(),
        ], $options));
    }

    /**
     * @return array<int, array{masterOption: string, hints: array<int, array<string, string|null>>}>
     */
    public function mapEcuOptionHints(Ecu $ecu): array
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
        ], $groups));
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
            'manufacturer' => $gearbox->getManufacturer()?->getName(),
            'manufacturerLogo' => $this->normalizePublicPath($gearbox->getManufacturer()?->getLogo()),
        ], $gearboxes));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapEinbauHinweise(VehicleEngine $engine): array
    {
        $hinweise = $this->einbauHinweisRepository->findForEngine($engine);

        return array_values(array_map(fn (EinbauHinweis $h): array => [
            'id' => $h->getId(),
            'title' => $h->getTitle(),
            'description' => $h->getDescription(),
            'ecuOptionMaster' => $h->getEcuOptionMaster()?->getName(),
            'activeEcuId' => $h->getActiveEcu()?->getId(),
            'activeEcuName' => $h->getActiveEcu()?->getName(),
            'images' => array_values(array_map(
                fn (MediaAsset $a): array => [
                    'src' => $this->normalizePublicPath($a->getFilePath()) ?? '',
                    'annotations' => $a->getAnnotations() ?? [],
                ],
                $h->getImages()->toArray()
            )),
        ], $hinweise));
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
     * @return array<string, mixed>
     */
    public function buildEngineDetailsData(VehicleEngine $engine, \App\Entity\VehicleDataPool $pool, ?string $requestedTemplateVariant = null): array
    {
        $model = $engine->getVehicleModel();
        $series = $model?->getVehicleSeries();
        $brand = $series?->getVehicleBrand();

        $settings = $this->resolveFrontendSettingsForPool($pool);
        $requestedVariant = trim((string) ($requestedTemplateVariant ?? ''));
        $templateVariant = '' !== $requestedVariant ? $requestedVariant : $settings->getTemplateVariant();
        $layoutSettings = $settings->getLayoutSettings($templateVariant);
        $showPrices = (bool) ($layoutSettings['showPrices'] ?? false);

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
                'fuel' => $engine->getFuel(),
                'displacement' => $engine->getDisplacement(),
                'image' => $engine->getImage(),
                'glbModel' => $engine->getGlbModel(),
                'mediaDisplayMode' => $engine->getMediaDisplayMode(),
            ],
            'brand' => [
                'id' => $brand?->getId(),
                'name' => $this->resolveBrandName($brand),
                'image' => $this->normalizePublicPath($this->resolveBrandImage($brand)),
            ],
            'series' => [
                'id' => $series?->getId(),
                'name' => $series?->getName(),
            ],
            'model' => [
                'id' => $model?->getId(),
                'name' => $model?->getName(),
            ],
            'stages' => $this->mapStagePayloads($stages, $showPrices),
            'ecus' => $this->mapEcuPayloads($allEcus, $showPrices),
            'gearboxes' => $this->mapGearboxPayloads($gearboxes),
            'einbauHinweise' => $this->mapEinbauHinweise($engine),
            'settings' => $this->buildFrontendSettingsPayload($settings),
            'pool' => [
                'id' => $pool->getId(),
                'key' => $pool->getPoolKey(),
                'name' => $pool->getName(),
            ],
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
