<?php

namespace App\Controller;

use App\Entity\Ecu;
use App\Entity\EcuOption;
use App\Entity\Gearbox;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleEngine;
use App\Entity\VehicleFrontendSettings;
use App\Entity\VehicleStage;
use App\Entity\VehicleType;
use App\Repository\VehicleBrandRepository;
use App\Repository\VehicleDataPoolRepository;
use App\Repository\VehicleFrontendSettingsRepository;
use App\Repository\VehicleEngineRepository;
use App\Repository\VehicleModelRepository;
use App\Repository\VehicleSeriesRepository;
use App\Repository\VehicleTypeRepository;
use App\Service\Analytics\VehicleUsageTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VehicleDatabaseController extends AbstractController
{
    public function __construct(
        private readonly VehicleTypeRepository $vehicleTypeRepository,
        private readonly VehicleDataPoolRepository $vehicleDataPoolRepository,
        private readonly VehicleBrandRepository $vehicleBrandRepository,
        private readonly VehicleSeriesRepository $vehicleSeriesRepository,
        private readonly VehicleModelRepository $vehicleModelRepository,
        private readonly VehicleEngineRepository $vehicleEngineRepository,
        private readonly VehicleFrontendSettingsRepository $vehicleFrontendSettingsRepository,
        private readonly VehicleUsageTracker $vehicleUsageTracker,
    ) {
    }

    #[Route('/chiptuning', name: 'app_vehicle_database', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $pool = $this->resolveRequestedPool($request);

        $this->vehicleUsageTracker->track($request, 'vdb.page_view', [
            'source' => 'website',
            'meta' => ['poolKey' => $pool?->getPoolKey()],
        ]);

        return $this->render('website/vehicle_database.html.twig', [
            'frontendSettings' => $this->buildFrontendSettingsPayload(
                $this->vehicleFrontendSettingsRepository->getCurrentOrDefaultForPool($pool),
                trim((string) $request->query->get('templateVariant', ''))
            ),
            'vehicleDatabaseCssVersion' => $this->getVehicleDatabaseCssVersion(),
            'availableDataPools' => $this->mapDataPools(),
            'currentDataPoolKey' => $pool?->getPoolKey(),
        ]);
    }

    #[Route('/chiptuning/api-test', name: 'app_vehicle_database_api_test', methods: ['GET'])]
    public function apiTest(Request $request): Response
    {
        $pool = $this->resolveRequestedPool($request);

        $this->vehicleUsageTracker->track($request, 'vdb.api_test.page_view', [
            'source' => 'website',
            'meta' => ['poolKey' => $pool?->getPoolKey()],
        ]);

        return $this->render('website/vehicle_database.html.twig', [
            'frontendSettings' => $this->buildFrontendSettingsPayload(
                $this->vehicleFrontendSettingsRepository->getCurrentOrDefaultForPool($pool),
                trim((string) $request->query->get('templateVariant', ''))
            ),
            'vehicleDatabaseCssVersion' => $this->getVehicleDatabaseCssVersion(),
            'apiTesterMode' => true,
            'availableDataPools' => $this->mapDataPools(),
            'currentDataPoolKey' => $pool?->getPoolKey(),
        ]);
    }

    private function getVehicleDatabaseCssVersion(): string
    {
        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $path = $projectDir . '/public/styles/vehicle_database.css';

        if (!is_file($path)) {
            return (string) time();
        }

        $hash = hash_file('sha1', $path);
        if (false === $hash) {
            $mtime = @filemtime($path);

            return false === $mtime ? (string) time() : (string) $mtime;
        }

        return substr($hash, 0, 12);
    }

    #[Route('/chiptuning/api/types', name: 'app_vehicle_database_types', methods: ['GET'])]
    public function types(Request $request): JsonResponse
    {
        $pool = $this->resolveRequestedPool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['message' => 'Datenbestand nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        $this->vehicleUsageTracker->track($request, 'vdb.types.list', [
            'source' => 'website',
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $types = array_values(array_filter(
            $pool->getVehicleTypes()->toArray(),
            static fn (mixed $type): bool => $type instanceof VehicleType && $type->isActive()
        ));
        usort($types, static fn (VehicleType $a, VehicleType $b): int => [$a->getSorting(), $a->getName()] <=> [$b->getSorting(), $b->getName()]);

        return $this->json($this->mapTypes($types));
    }

    #[Route('/chiptuning/api/brands/{typeId}', name: 'app_vehicle_database_brands', requirements: ['typeId' => '\\d+'], methods: ['GET'])]
    public function brands(int $typeId, Request $request): JsonResponse
    {
        $pool = $this->resolveRequestedPool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['message' => 'Datenbestand nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        $this->vehicleUsageTracker->track($request, 'vdb.brands.list', [
            'source' => 'website',
            'vehicleTypeId' => $typeId,
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $brands = $this->vehicleBrandRepository->createQueryBuilder('b')
            ->leftJoin('b.vehicleType', 't')
            ->where('t.id = :typeId')
            ->andWhere('b.vehicleDataPool = :pool')
            ->andWhere('b.active = :active')
            ->setParameter('typeId', $typeId)
            ->setParameter('pool', $pool)
            ->setParameter('active', true)
            ->orderBy('b.sorting', 'ASC')
            ->addOrderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(array_map(fn ($brand): array => [
            'id' => $brand->getId(),
            'name' => $this->resolveBrandName($brand),
            'image' => $this->normalizePublicPath($this->resolveBrandImage($brand)),
            'icon' => $this->normalizePublicPath($this->resolveBrandImage($brand)),
            'showInTopMenu' => $brand->isShowInTopMenu(),
        ], $brands));
    }

    #[Route('/chiptuning/api/series/{brandId}', name: 'app_vehicle_database_series', requirements: ['brandId' => '\\d+'], methods: ['GET'])]
    public function series(int $brandId, Request $request): JsonResponse
    {
        $pool = $this->resolveRequestedPool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['message' => 'Datenbestand nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        $this->vehicleUsageTracker->track($request, 'vdb.series.list', [
            'source' => 'website',
            'brandId' => $brandId,
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $series = $this->vehicleSeriesRepository->createQueryBuilder('s')
            ->leftJoin('s.vehicleBrand', 'b')
            ->where('b.id = :brandId')
            ->andWhere('s.vehicleDataPool = :pool')
            ->andWhere('s.active = :active')
            ->setParameter('brandId', $brandId)
            ->setParameter('pool', $pool)
            ->setParameter('active', true)
            ->orderBy('s.sorting', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(array_map(static fn ($row): array => [
            'id' => $row->getId(),
            'name' => $row->getName(),
        ], $series));
    }

    #[Route('/chiptuning/api/models/{seriesId}', name: 'app_vehicle_database_models', requirements: ['seriesId' => '\\d+'], methods: ['GET'])]
    public function models(int $seriesId, Request $request): JsonResponse
    {
        $pool = $this->resolveRequestedPool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['message' => 'Datenbestand nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        $this->vehicleUsageTracker->track($request, 'vdb.models.list', [
            'source' => 'website',
            'seriesId' => $seriesId,
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $models = $this->vehicleModelRepository->createQueryBuilder('m')
            ->leftJoin('m.vehicleSeries', 's')
            ->where('s.id = :seriesId')
            ->andWhere('m.vehicleDataPool = :pool')
            ->andWhere('m.active = :active')
            ->setParameter('seriesId', $seriesId)
            ->setParameter('pool', $pool)
            ->setParameter('active', true)
            ->orderBy('m.sorting', 'ASC')
            ->addOrderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(array_map(static fn ($row): array => [
            'id' => $row->getId(),
            'name' => $row->getName(),
        ], $models));
    }

    #[Route('/chiptuning/api/engines/{modelId}', name: 'app_vehicle_database_engines', requirements: ['modelId' => '\\d+'], methods: ['GET'])]
    public function engines(int $modelId, Request $request): JsonResponse
    {
        $pool = $this->resolveRequestedPool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['message' => 'Datenbestand nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        $this->vehicleUsageTracker->track($request, 'vdb.engines.list', [
            'source' => 'website',
            'modelId' => $modelId,
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $engines = $this->vehicleEngineRepository->createQueryBuilder('e')
            ->leftJoin('e.vehicleModel', 'm')
            ->where('m.id = :modelId')
            ->andWhere('e.vehicleDataPool = :pool')
            ->andWhere('e.active = :active')
            ->setParameter('modelId', $modelId)
            ->setParameter('pool', $pool)
            ->setParameter('active', true)
            ->orderBy('e.sorting', 'ASC')
            ->addOrderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(array_map(static fn (VehicleEngine $engine): array => [
            'id' => $engine->getId(),
            'name' => $engine->getName(),
            'horsepower' => $engine->getHorsepower(),
            'fuel' => $engine->getFuel(),
        ], $engines));
    }

    #[Route('/chiptuning/api/engine-details/{engineId}', name: 'app_vehicle_database_engine_details', requirements: ['engineId' => '\\d+'], methods: ['GET'])]
    public function engineDetails(int $engineId, Request $request): JsonResponse
    {
        $pool = $this->resolveRequestedPool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['message' => 'Datenbestand nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        $stageId = (int) $request->query->get('stageId', 0);
        $noTrack = (int) $request->query->get('noTrack', 0) === 1;
        if (!$noTrack) {
            $this->vehicleUsageTracker->track($request, 'vdb.engine.details', [
                'source' => 'website',
                'engineId' => $engineId,
                'stageId' => $stageId > 0 ? $stageId : null,
                'meta' => ['poolKey' => $pool->getPoolKey()],
            ]);
        }

        $engine = $this->vehicleEngineRepository->find($engineId);
        if (
            !$engine instanceof VehicleEngine
            || !$engine->isActive()
            || $engine->getVehicleDataPool()?->getId() !== $pool->getId()
        ) {
            return $this->json(['message' => 'Motor nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        $model = $engine->getVehicleModel();
        $series = $model?->getVehicleSeries();
        $brand = $series?->getVehicleBrand();

        $settings = $this->vehicleFrontendSettingsRepository->getCurrentOrDefaultForPool($pool);
        $requestedVariant = trim((string) $request->query->get('templateVariant', ''));
        $templateVariant = '' !== $requestedVariant ? $requestedVariant : $settings->getTemplateVariant();
        $layoutSettings = $settings->getLayoutSettings($templateVariant);
        $showPrices = (bool) ($layoutSettings['showPrices'] ?? false) && $pool->isShowPrices();

        $stages = array_values(array_filter(
            $engine->getStages()->toArray(),
            static fn ($stage): bool => $stage instanceof VehicleStage
                && $stage->isActive()
                && $stage->getVehicleDataPool()?->getId() === $pool->getId()
        ));
        usort($stages, static fn ($a, $b): int => strnatcasecmp((string) $a->getName(), (string) $b->getName()));
        $stages = $this->filterStagesBySettings($stages, (bool) ($layoutSettings['showOnlyStage1'] ?? false));

        $allEcus = array_values(array_filter(
            $engine->getEcuRelations()->toArray(),
            static fn (mixed $ecu): bool => $ecu instanceof Ecu
                && null !== $ecu->getId()
                && $ecu->getVehicleDataPool()?->getId() === $pool->getId()
        ));
        usort($allEcus, static fn (Ecu $a, Ecu $b): int => strnatcasecmp($a->getName(), $b->getName()));

        $gearboxes = array_values(array_filter(
            $engine->getGearboxes()->toArray(),
            static fn (mixed $gearbox): bool => $gearbox instanceof Gearbox
                && null !== $gearbox->getId()
                && $gearbox->isActive()
                && $gearbox->getVehicleDataPool()?->getId() === $pool->getId()
        ));
        usort($gearboxes, static fn (Gearbox $a, Gearbox $b): int => strnatcasecmp($a->getTitle(), $b->getTitle()));

        return $this->json([
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
            'stages' => array_map(
                fn (VehicleStage $stage): array => $this->mapStagePayload($stage, $showPrices, $pool->isShowDyno()),
                $stages
            ),
            'ecus' => $this->mapEcuPayloads($allEcus, $showPrices),
            'gearboxes' => $pool->isShowGearbox() ? $this->mapGearboxPayloads($gearboxes) : [],
            'settings' => $this->buildFrontendSettingsPayload($settings),
            'showPrices' => $showPrices,
            'showEcoStage' => $pool->isShowEcoStage(),
            'pool' => [
                'id' => $pool->getId(),
                'key' => $pool->getPoolKey(),
                'name' => $pool->getName(),
            ],
        ]);
    }

    #[Route('/chiptuning/api/track', name: 'app_vehicle_database_track', methods: ['POST'])]
    public function track(Request $request): JsonResponse
    {
        $pool = $this->resolveRequestedPool($request);

        $payload = [];
        try {
            $payload = $request->toArray();
        } catch (\Throwable) {
            $payload = [];
        }

        $rawEvent = strtolower(trim((string) ($payload['event'] ?? 'client_event')));
        if ('' === $rawEvent || preg_match('/^[a-z0-9._:-]{2,80}$/', $rawEvent) !== 1) {
            $rawEvent = 'client_event';
        }

        $this->vehicleUsageTracker->track($request, 'vdb.client.' . $rawEvent, [
            'source' => 'website',
            'typeId' => $payload['typeId'] ?? null,
            'vehicleTypeId' => $payload['vehicleTypeId'] ?? null,
            'brandId' => $payload['brandId'] ?? null,
            'seriesId' => $payload['seriesId'] ?? null,
            'modelId' => $payload['modelId'] ?? null,
            'engineId' => $payload['engineId'] ?? null,
            'stageId' => $payload['stageId'] ?? null,
            'templateVariant' => $payload['templateVariant'] ?? null,
            'meta' => array_filter([
                'poolKey' => $pool?->getPoolKey(),
                'payloadMeta' => is_array($payload['meta'] ?? null) ? $payload['meta'] : null,
            ], static fn (mixed $value): bool => null !== $value),
        ]);

        return $this->json(['ok' => true]);
    }

    /**
     * @return array{
     *     id: int|null,
     *     name: string,
     *     description: ?string,
     *     price: ?string,
     *     hp: ?int,
     *     nm: ?int,
     *     ecu: ?int,
     *     icon: ?string,
     *     images: array<int, string>,
     *     videos: ?string,
     *     dynoChart: array<string, mixed>|null
     * }
     */
    private function mapStagePayload(VehicleStage $stage, bool $showPrices, bool $showDyno = true): array
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
        ];
    }

    /**
     * @param array<int, Ecu> $ecus
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapEcuPayloads(array $ecus, bool $showPrices): array
    {
        return array_values(array_map(fn (Ecu $ecu): array => [
            'id' => $ecu->getId(),
            'name' => $this->resolveEcuName($ecu),
            'type' => $this->resolveEcuType($ecu),
            'controlUnitKind' => $this->resolveEcuControlUnitKind($ecu),
            'manufacturer' => $this->resolveEcuManufacturerName($ecu),
            'manufacturerLogo' => $this->normalizePublicPath($this->resolveEcuManufacturerLogo($ecu)),
            'options' => $this->mapEcuOptionPayloads($ecu, $showPrices),
            'toolEntries' => $this->mapEcuToolEntries($ecu),
        ], $ecus));
    }

    /**
     * @return array<int, array{id: int|null, toolManufacturer: string|null, toolManufacturerLogo: string|null, methods: array<int, string>}>
     */
    private function mapEcuToolEntries(Ecu $ecu): array
    {
        $entries = $ecu->getToolEntries()->toArray();
        usort($entries, static fn (\App\Entity\EcuToolEntry $a, \App\Entity\EcuToolEntry $b): int => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

        return array_values(array_map(fn (\App\Entity\EcuToolEntry $entry): array => [
            'id' => $entry->getId(),
            'toolManufacturer' => $entry->getToolManufacturer()?->getName(),
            'toolManufacturerLogo' => $this->normalizePublicPath($entry->getToolManufacturer()?->getLogo()),
            'methods' => array_values(array_map(
                static fn (\App\Entity\ToolMethode $m): string => $m->getName(),
                $entry->getToolMethods()->toArray()
            )),
        ], $entries));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapEcuOptionPayloads(Ecu $ecu, bool $showPrices): array
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
            'description' => $this->resolveEcuOptionDescription($option),
            'icon' => $this->normalizePublicPath($this->resolveEcuOptionIcon($option)),
            'price' => $showPrices ? $option->getPrice() : null,
            'isMainOption' => $option->isMainOption(),
        ], $options));
    }

    /**
     * @param array<int, Gearbox> $gearboxes
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapGearboxPayloads(array $gearboxes): array
    {
        return array_values(array_map(static fn (Gearbox $gearbox): array => [
            'id' => $gearbox->getId(),
            'title' => $gearbox->getTitle(),
            'description' => $gearbox->getDescription(),
            'icon' => $this->normalizePublicPath($gearbox->getResolvedIconPath()),
            'images' => self::toPathList($gearbox->getImages()),
            'type' => $gearbox->getGearboxType()?->getTitle(),
            'manufacturer' => $gearbox->getManufacturer()?->getName(),
            'manufacturerLogo' => $this->normalizePublicPath($gearbox->getManufacturer()?->getLogo()),
            'toolEntries' => $this->mapGearboxToolEntries($gearbox),
        ], $gearboxes));
    }

    /**
     * @return array<int, array{id: int|null, toolManufacturer: string|null, toolManufacturerLogo: string|null, methods: array<int, string>}>
     */
    private function mapGearboxToolEntries(Gearbox $gearbox): array
    {
        $entries = $gearbox->getToolEntries()->toArray();
        usort($entries, static fn (\App\Entity\GearboxToolEntry $a, \App\Entity\GearboxToolEntry $b): int => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

        return array_values(array_map(fn (\App\Entity\GearboxToolEntry $entry): array => [
            'id' => $entry->getId(),
            'toolManufacturer' => $entry->getToolManufacturer()?->getName(),
            'toolManufacturerLogo' => $this->normalizePublicPath($entry->getToolManufacturer()?->getLogo()),
            'methods' => array_values(array_map(
                static fn (\App\Entity\ToolMethode $m): string => $m->getName(),
                $entry->getToolMethods()->toArray()
            )),
        ], $entries));
    }

    /**
     * @param array<int, VehicleType> $types
     * @return array<int, array{id:int|null,name:string,image:?string,icon:?string}>
     */
    private function mapTypes(array $types): array
    {
        return array_map(fn (VehicleType $type): array => [
            'id' => $type->getId(),
            'name' => $type->getName(),
            'image' => $this->normalizePublicPath($type->getImage()),
            'icon' => $this->normalizePublicPath($type->getImage()),
        ], $types);
    }

    /**
     * @param array<int, object> $stages
     * @return array<int, object>
     */
    private function filterStagesBySettings(array $stages, bool $showOnlyStage1): array
    {
        if (!$showOnlyStage1 || [] === $stages) {
            return $stages;
        }

        foreach ($stages as $stage) {
            $name = strtolower((string) $stage->getName());
            if (preg_match('/\bstage\s*1\b/', $name) === 1) {
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
    private function buildFrontendSettingsPayload(?VehicleFrontendSettings $settings = null, ?string $templateVariant = null): array
    {
        $resolved = $settings ?? $this->vehicleFrontendSettingsRepository->getCurrentOrDefault();
        $requestedVariant = trim((string) $templateVariant);

        return [
            'layoutSwitcherEnabled' => $resolved->isLayoutSwitcherEnabled(),
            'templateVariant' => VehicleFrontendSettings::isValidTemplateVariant($requestedVariant)
                ? $requestedVariant
                : $resolved->getTemplateVariant(),
            'layoutSettings' => $resolved->getAllLayoutSettings(),
        ];
    }

    /**
     * @return array<int, array{key:string,name:string,description:?string}>
     */
    private function mapDataPools(): array
    {
        $pools = $this->vehicleDataPoolRepository->findBy(['active' => true], ['name' => 'ASC']);

        return array_values(array_map(static fn (VehicleDataPool $pool): array => [
            'key' => $pool->getPoolKey(),
            'name' => $pool->getName(),
            'description' => $pool->getDescription(),
        ], $pools));
    }

    private function resolveRequestedPool(Request $request): ?VehicleDataPool
    {
        $requestedPoolKey = trim((string) $request->query->get('poolKey', ''));
        if ('' !== $requestedPoolKey) {
            return $this->vehicleDataPoolRepository->findActiveByPoolKey($requestedPoolKey);
        }

        return $this->vehicleDataPoolRepository->findActiveByPoolKey('live');
    }

    /**
     * @return array<int, string>
     */
    private static function toPathList(?string $value): array
    {
        if (null === $value || '' === trim($value)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(array_filter(array_map(static fn (string $line): string => trim($line), $lines)));
    }

    private function resolveBrandName(mixed $brand): ?string
    {
        if (!is_object($brand) || !method_exists($brand, 'getName')) {
            return null;
        }

        return $this->preferText(
            $brand->getMasterBrand()?->getName(),
            $brand->getName()
        );
    }

    private function resolveBrandImage(mixed $brand): ?string
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

    private function resolveEcuName(Ecu $ecu): string
    {
        return $this->preferText(
            $ecu->getMasterEcu()?->getName(),
            $ecu->getName()
        ) ?? '';
    }

    private function resolveEcuType(Ecu $ecu): ?string
    {
        return $this->preferText(
            $ecu->getMasterEcu()?->getType(),
            $ecu->getType()
        );
    }

    private function resolveEcuControlUnitKind(Ecu $ecu): ?string
    {
        return $this->preferText(
            $ecu->getMasterEcu()?->getControlUnitKind(),
            $ecu->getControlUnitKind()
        );
    }

    private function resolveEcuManufacturerName(Ecu $ecu): ?string
    {
        return $this->preferText(
            $ecu->getManufacturer()?->getMasterManufacturer()?->getName(),
            $ecu->getMasterEcu()?->getManufacturerMaster()?->getName(),
            $ecu->getManufacturer()?->getName()
        );
    }

    private function resolveEcuManufacturerLogo(Ecu $ecu): ?string
    {
        return $this->preferText(
            $ecu->getManufacturer()?->getMasterManufacturer()?->getLogo(),
            $ecu->getMasterEcu()?->getManufacturerMaster()?->getLogo(),
            $ecu->getManufacturer()?->getLogo()
        );
    }

    private function resolveEcuOptionName(EcuOption $option): string
    {
        return $this->preferText(
            $option->getMasterOption()?->getName(),
            $option->getName()
        ) ?? '';
    }

    private function resolveEcuOptionDescription(EcuOption $option): ?string
    {
        return $this->preferText(
            $option->getMasterOption()?->getDescription(),
            $option->getDescription()
        );
    }

    private function resolveEcuOptionIcon(EcuOption $option): ?string
    {
        return $this->preferText(
            $option->getMasterOption()?->getResolvedIconPath(),
            $option->getResolvedIconPath()
        );
    }

    private function preferText(?string ...$values): ?string
    {
        foreach ($values as $value) {
            $trimmed = trim((string) ($value ?? ''));
            if ('' !== $trimmed) {
                return $trimmed;
            }
        }

        return null;
    }

    private function normalizePublicPath(?string $path): ?string
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
}
