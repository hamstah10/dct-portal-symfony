<?php

namespace App\Controller\Api;

use App\Entity\ApiToken;
use App\Entity\Ecu;
use App\Entity\EcuManufacturer;
use App\Entity\EcuOption;
use App\Entity\EcuOptionMaster;
use App\Entity\EcuOptionHint;
use App\Entity\EcuToolEntry;
use App\Entity\MediaAsset;
use App\Entity\GearboxToolEntry;
use App\Entity\Gearbox;
use App\Entity\GearboxType;
use App\Entity\PerformanceBrand;
use App\Entity\ToolManufacturer;
use App\Entity\ToolMethode;
use App\Entity\VehicleDataContext;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleEngine;
use App\Entity\VehicleFrontendSettings;
use App\Entity\VehicleSeries;
use App\Entity\VehicleStage;
use App\Entity\VehicleType;
use App\Repository\ApiTokenRepository;
use App\Repository\EinbauHinweisRepository;
use App\Repository\VehicleBrandRepository;
use App\Repository\VehicleEngineRepository;
use App\Repository\VehicleFrontendSettingsRepository;
use App\Repository\VehicleModelRepository;
use App\Repository\VehicleSeriesRepository;
use App\Repository\VehicleTypeRepository;
use App\Service\BrandMasterImageResolver;
use App\Service\Analytics\VehicleUsageTracker;
use App\Service\EcuHintsResolver;
use App\Service\VehicleDataContextResolver;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\SearchManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/fahrzeugdatenbank')]
final class VehicleDatabaseApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly VehicleTypeRepository $vehicleTypeRepository,
        private readonly VehicleBrandRepository $vehicleBrandRepository,
        private readonly VehicleSeriesRepository $vehicleSeriesRepository,
        private readonly VehicleModelRepository $vehicleModelRepository,
        private readonly VehicleEngineRepository $vehicleEngineRepository,
        private readonly VehicleFrontendSettingsRepository $vehicleFrontendSettingsRepository,
        private readonly VehicleUsageTracker $vehicleUsageTracker,
        private readonly VehicleDataContextResolver $vehicleDataContextResolver,
        private readonly EcuHintsResolver $ecuHintsResolver,
        private readonly EinbauHinweisRepository $einbauHinweisRepository,
        private readonly SearchManagerInterface $searchManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly BrandMasterImageResolver $brandMasterImageResolver,
    ) {
    }

    #[Route('', name: 'api_vehicle_database_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        if (($dbToken = $this->authorizeVehicleApi($request)) instanceof JsonResponse) {
            return $dbToken;
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);

        return new JsonResponse([
            'message' => 'Fahrzeugdatenbank API',
            'pool' => [
                'id' => $pool?->getId(),
                'key' => $pool?->getPoolKey(),
                'name' => $pool?->getName(),
            ],
            'endpoints' => [
                'GET /api/fahrzeugdatenbank/types',
                'GET /api/fahrzeugdatenbank/brands/{typeId}',
                'GET /api/fahrzeugdatenbank/series/{brandId}',
                'GET /api/fahrzeugdatenbank/models/{seriesId}',
                'GET /api/fahrzeugdatenbank/engines/{modelId}',
                'GET /api/fahrzeugdatenbank/engine-details/{engineId}',
                'GET /api/fahrzeugdatenbank/search?q=...',
                'GET /api/fahrzeugdatenbank/suggest?q=...',
                'POST /api/fahrzeugdatenbank/track',
            ],
        ]);
    }

    #[Route('/search', name: 'api_vehicle_database_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        if (($dbToken = $this->authorizeVehicleApi($request)) instanceof JsonResponse) {
            return $dbToken;
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

        $query = trim((string) $request->query->get('q', ''));
        if ('' === $query) {
            return $this->errorResponse('Parameter "q" darf nicht leer sein.', Response::HTTP_BAD_REQUEST);
        }

        $limit = max(1, min(50, (int) $request->query->get('limit', 20)));

        $filterParts = [
            sprintf('active = true AND dataPoolKey = %s', json_encode($pool->getPoolKey())),
        ];
        $typeId = $request->query->get('typeId');
        if (null !== $typeId && '' !== $typeId) {
            $vehicleType = $this->vehicleTypeRepository->find((int) $typeId);
            if ($vehicleType instanceof VehicleType) {
                $filterParts[] = 'vehicleTypeName = ' . json_encode($vehicleType->getName());
            }
        }

        $brand = trim((string) $request->query->get('brand', ''));
        if ('' !== $brand) {
            $filterParts[] = 'brand = ' . json_encode($brand);
        }

        $fuel = trim((string) $request->query->get('fuel', ''));
        if ('' !== $fuel) {
            $filterParts[] = 'fuel = ' . json_encode($fuel);
        }

        $ecuManufacturer = trim((string) $request->query->get('ecuManufacturer', ''));
        if ('' !== $ecuManufacturer) {
            $filterParts[] = 'ecuManufacturer = ' . json_encode($ecuManufacturer);
        }

        $horsepowerMin = $request->query->getInt('horsepowerMin', 0);
        $horsepowerMax = $request->query->getInt('horsepowerMax', 0);
        if ($horsepowerMin > 0) {
            $filterParts[] = 'horsepower >= ' . $horsepowerMin;
        }
        if ($horsepowerMax > 0) {
            $filterParts[] = 'horsepower <= ' . $horsepowerMax;
        }

        $yearMin = $request->query->getInt('yearMin', 0);
        $yearMax = $request->query->getInt('yearMax', 0);
        if ($yearMin > 0) {
            $filterParts[] = 'yearFrom >= ' . $yearMin;
        }
        if ($yearMax > 0) {
            $filterParts[] = 'yearFrom <= ' . $yearMax;
        }

        $sort = trim((string) $request->query->get('sort', ''));
        $allowedSorts = [
            'horsepower:asc',
            'horsepower:desc',
            'yearFrom:asc',
            'yearFrom:desc',
            'brand:asc',
            'brand:desc',
        ];
        $sortOptions = in_array($sort, $allowedSorts, true) ? [$sort] : [];

        $this->vehicleUsageTracker->track($request, 'vdb.api.search', [
            'source' => 'api',
            'meta' => ['poolKey' => $pool->getPoolKey(), 'query' => $query],
        ]);

        $hits = $this->searchManager->rawSearch(VehicleEngine::class, $query, [
            'filter' => implode(' AND ', $filterParts),
            'limit' => $limit,
            ...($sortOptions !== [] ? ['sort' => $sortOptions] : []),
        ]);

        $hitRows = $hits['hits'] ?? [];
        $engineIds = array_values(array_unique(array_filter(array_map(
            static fn (array $hit): ?int => isset($hit['id']) ? (int) $hit['id'] : null,
            $hitRows
        ))));

        $brandImageByEngineId = [];
        $brandImageByName = [];
        if ([] !== $engineIds) {
            $engineEntities = $this->vehicleEngineRepository->createQueryBuilder('e')
                ->leftJoin('e.vehicleModel', 'm')
                ->leftJoin('m.vehicleSeries', 's')
                ->leftJoin('s.vehicleBrand', 'b')
                ->addSelect('m', 's', 'b')
                ->where('e.id IN (:ids)')
                ->setParameter('ids', $engineIds)
                ->getQuery()
                ->getResult();

            foreach ($engineEntities as $engineEntity) {
                if (!$engineEntity instanceof VehicleEngine) {
                    continue;
                }

                $engineId = $engineEntity->getId();
                if (null === $engineId) {
                    continue;
                }

                $brand = $engineEntity->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand();
                $brandImageByEngineId[$engineId] = $this->normalizePublicPath(
                    $this->resolveBrandImage($brand)
                );

                $brandName = trim((string) ($brand?->getName() ?? ''));
                if ('' !== $brandName && !isset($brandImageByName[$brandName])) {
                    $brandImageByName[$brandName] = $brandImageByEngineId[$engineId];
                }
            }
        }

        return new JsonResponse([
            'data' => array_map(static function (array $hit) use ($brandImageByEngineId): array {
                $engineId = isset($hit['id']) ? (int) $hit['id'] : null;

                return [
                    'engineId' => $engineId,
                    'name' => $hit['name'] ?? null,
                    'brandName' => $hit['brandName'] ?? null,
                    'brandImage' => null !== $engineId
                        ? ($brandImageByEngineId[$engineId] ?? ($brandImageByName[trim((string) ($hit['brandName'] ?? ''))] ?? null))
                        : ($brandImageByName[trim((string) ($hit['brandName'] ?? ''))] ?? null),
                    'seriesName' => $hit['seriesName'] ?? null,
                    'modelName' => $hit['modelName'] ?? null,
                    'horsepower' => $hit['horsepower'] ?? null,
                    'fuel' => $hit['fuel'] ?? null,
                    'ecuNames' => $hit['ecuNames'] ?? [],
                    'ecuManufacturerNames' => $hit['ecuManufacturerNames'] ?? [],
                ];
            }, $hitRows),
        ]);
    }

    #[Route('/suggest', name: 'api_vehicle_database_suggest', methods: ['GET'])]
    public function suggest(Request $request): JsonResponse
    {
        if (($dbToken = $this->authorizeVehicleApi($request)) instanceof JsonResponse) {
            return $dbToken;
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

        $query = trim((string) $request->query->get('q', ''));
        if ('' === $query) {
            return new JsonResponse(['items' => []]);
        }

        $typeId = $request->query->get('typeId');
        $filter = sprintf('active = true AND dataPoolKey = %s', json_encode($pool->getPoolKey()));
        if (null !== $typeId && '' !== $typeId) {
            $vehicleType = $this->vehicleTypeRepository->find((int) $typeId);
            if ($vehicleType instanceof VehicleType) {
                $filter .= ' AND vehicleTypeName = ' . json_encode($vehicleType->getName());
            }
        }

        $hits = $this->searchManager->rawSearch(VehicleEngine::class, $query, [
            'filter' => $filter,
            'limit' => 8,
            'attributesToRetrieve' => [
                'brandName',
                'seriesName',
                'modelName',
                'name',
                'engineCode',
                'ecuNames',
                'ecuManufacturerNames',
                'fuel',
            ],
        ]);

        $items = [];
        foreach (($hits['hits'] ?? []) as $hit) {
            if (!is_array($hit)) {
                continue;
            }

            $candidates = [
                [$hit['brandName'] ?? null, 'Hersteller'],
                [trim((string) (($hit['brandName'] ?? '') . ' ' . ($hit['seriesName'] ?? ''))), 'Baureihe'],
                [trim((string) (($hit['brandName'] ?? '') . ' ' . ($hit['modelName'] ?? ''))), 'Modell'],
                [$hit['name'] ?? null, 'Motor'],
                [$hit['engineCode'] ?? null, 'Motorcode'],
                [$hit['fuel'] ?? null, 'Kraftstoff'],
            ];

            foreach ($candidates as [$label, $type]) {
                $label = trim((string) $label);
                $type = trim((string) $type);
                if ('' === $label || '' === $type) {
                    continue;
                }

                $key = mb_strtolower($type . '|' . $label);
                $items[$key] = [
                    'label' => $label,
                    'type' => $type,
                ];
            }

            foreach (array_slice(array_values(array_filter((array) ($hit['ecuNames'] ?? []))), 0, 4) as $ecuName) {
                $label = trim((string) $ecuName);
                if ('' === $label) {
                    continue;
                }

                $items[mb_strtolower('ECU|' . $label)] = [
                    'label' => $label,
                    'type' => 'ECU',
                ];
            }

            foreach (array_slice(array_values(array_filter((array) ($hit['ecuManufacturerNames'] ?? []))), 0, 4) as $ecuManufacturerName) {
                $label = trim((string) $ecuManufacturerName);
                if ('' === $label) {
                    continue;
                }

                $items[mb_strtolower('ECU-Hersteller|' . $label)] = [
                    'label' => $label,
                    'type' => 'ECU-Hersteller',
                ];
            }
        }

        return new JsonResponse([
            'items' => array_slice(array_values($items), 0, 8),
        ]);
    }

    #[Route('/types', name: 'api_vehicle_database_types', methods: ['GET'])]
    public function types(Request $request): JsonResponse
    {
        if (($dbToken = $this->authorizeVehicleApi($request)) instanceof JsonResponse) {
            return $dbToken;
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

        $this->vehicleUsageTracker->track($request, 'vdb.api.types.list', [
            'source' => 'api',
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $types = $pool->getVehicleTypes()
            ->filter(static fn (mixed $type): bool => $type instanceof VehicleType && $type->isActive())
            ->toArray();
        usort($types, static fn (VehicleType $a, VehicleType $b): int => [$a->getSorting(), $a->getName()] <=> [$b->getSorting(), $b->getName()]);

        return new JsonResponse([
            'data' => array_map(fn (VehicleType $type): array => [
                'id' => $type->getId(),
                'name' => $type->getName(),
                'slug' => $type->getSlug(),
                'image' => $this->normalizePublicPath($type->getIcon() !== null
                    ? $this->resolveUxIconPublicPath($type->getIcon())
                    : $type->getResolvedImagePath()),
                'icon' => $this->normalizePublicPath($type->getIcon() !== null
                    ? $this->resolveUxIconPublicPath($type->getIcon())
                    : $type->getResolvedImagePath()),
            ], $types),
        ]);
    }

    #[Route('/brands/{typeId}', name: 'api_vehicle_database_brands', requirements: ['typeId' => '\d+'], methods: ['GET'])]
    public function brands(int $typeId, Request $request): JsonResponse
    {
        if (($dbToken = $this->authorizeVehicleApi($request)) instanceof JsonResponse) {
            return $dbToken;
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

        $this->vehicleUsageTracker->track($request, 'vdb.api.brands.list', [
            'source' => 'api',
            'vehicleTypeId' => $typeId,
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $brands = $this->vehicleBrandRepository->createQueryBuilder('b')
            ->leftJoin('b.vehicleType', 't')
            ->where('t.id = :typeId')
            ->andWhere('b.vehicleDataPool = :vehicleDataPool')
            ->andWhere('b.active = :active')
            ->setParameter('typeId', $typeId)
            ->setParameter('vehicleDataPool', $pool)
            ->setParameter('active', true)
            ->orderBy('b.sorting', 'ASC')
            ->addOrderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'data' => array_map(fn ($brand): array => [
                'id' => $brand->getId(),
                'name' => $this->resolveBrandName($brand),
                'image' => $this->normalizePublicPath($this->resolveBrandImage($brand)),
                'icon' => $this->normalizePublicPath($this->resolveBrandImage($brand)),
                'showInTopMenu' => $brand->isShowInTopMenu(),
            ], $brands),
        ]);
    }

    #[Route('/series/{brandId}', name: 'api_vehicle_database_series', requirements: ['brandId' => '\d+'], methods: ['GET'])]
    public function series(int $brandId, Request $request): JsonResponse
    {
        if (($dbToken = $this->authorizeVehicleApi($request)) instanceof JsonResponse) {
            return $dbToken;
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

        $this->vehicleUsageTracker->track($request, 'vdb.api.series.list', [
            'source' => 'api',
            'brandId' => $brandId,
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $series = $this->vehicleSeriesRepository->createQueryBuilder('s')
            ->leftJoin('s.vehicleBrand', 'b')
            ->where('b.id = :brandId')
            ->andWhere('s.vehicleDataPool = :vehicleDataPool')
            ->andWhere('s.active = :active')
            ->setParameter('brandId', $brandId)
            ->setParameter('vehicleDataPool', $pool)
            ->setParameter('active', true)
            ->orderBy('s.sorting', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'data' => array_map(fn ($item): array => [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'performanceBrand' => $this->mapPerformanceBrand($item->getPerformanceBrand()),
            ], $series),
        ]);
    }

    #[Route('/models/{seriesId}', name: 'api_vehicle_database_models', requirements: ['seriesId' => '\d+'], methods: ['GET'])]
    public function models(int $seriesId, Request $request): JsonResponse
    {
        if (($dbToken = $this->authorizeVehicleApi($request)) instanceof JsonResponse) {
            return $dbToken;
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

        $this->vehicleUsageTracker->track($request, 'vdb.api.models.list', [
            'source' => 'api',
            'seriesId' => $seriesId,
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $models = $this->vehicleModelRepository->createQueryBuilder('m')
            ->leftJoin('m.vehicleSeries', 's')
            ->where('s.id = :seriesId')
            ->andWhere('m.vehicleDataPool = :vehicleDataPool')
            ->andWhere('m.active = :active')
            ->setParameter('seriesId', $seriesId)
            ->setParameter('vehicleDataPool', $pool)
            ->setParameter('active', true)
            ->orderBy('m.sorting', 'ASC')
            ->addOrderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'data' => array_map(fn ($item): array => [
                'id' => $item->getId(),
                'name' => $item->getName(),
            ], $models),
        ]);
    }

    #[Route('/engines/{modelId}', name: 'api_vehicle_database_engines', requirements: ['modelId' => '\d+'], methods: ['GET'])]
    public function engines(int $modelId, Request $request): JsonResponse
    {
        if (($dbToken = $this->authorizeVehicleApi($request)) instanceof JsonResponse) {
            return $dbToken;
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

        $this->vehicleUsageTracker->track($request, 'vdb.api.engines.list', [
            'source' => 'api',
            'modelId' => $modelId,
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $engines = $this->vehicleEngineRepository->createQueryBuilder('e')
            ->leftJoin('e.vehicleModel', 'm')
            ->where('m.id = :modelId')
            ->andWhere('e.vehicleDataPool = :vehicleDataPool')
            ->andWhere('e.active = :active')
            ->setParameter('modelId', $modelId)
            ->setParameter('vehicleDataPool', $pool)
            ->setParameter('active', true)
            ->orderBy('e.sorting', 'ASC')
            ->addOrderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'data' => array_map(fn (VehicleEngine $engine): array => [
                'id' => $engine->getId(),
                'name' => $engine->getName(),
                'horsepower' => $engine->getHorsepower(),
                'fuel' => $engine->getFuel(),
                'performanceBrand' => $this->mapPerformanceBrand($engine->getPerformanceBrand()),
            ], $engines),
        ]);
    }

    #[Route('/engine-details/{engineId}', name: 'api_vehicle_database_engine_details', requirements: ['engineId' => '\d+'], methods: ['GET'])]
    public function engineDetails(int $engineId, Request $request): JsonResponse
    {
        if (($dbToken = $this->authorizeVehicleApi($request)) instanceof JsonResponse) {
            return $dbToken;
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

        $context = $this->vehicleDataContextResolver->resolveContextForRequest($request, $dbToken);
        $featureFlags = $this->vehicleDataContextResolver->resolveFeatureFlags($pool, $context);

        $stageId = (int) $request->query->get('stageId', 0);
        $this->vehicleUsageTracker->track($request, 'vdb.api.engine.details', [
            'source' => 'api',
            'engineId' => $engineId,
            'stageId' => $stageId > 0 ? $stageId : null,
            'meta' => ['poolKey' => $pool->getPoolKey()],
        ]);

        $engine = $this->vehicleEngineRepository->find($engineId);
        if (
            !$engine instanceof VehicleEngine
            || !$engine->isActive()
            || $engine->getVehicleDataPool()?->getId() !== $pool->getId()
        ) {
            return $this->errorResponse('Motor nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $model = $engine->getVehicleModel();
        $series = $model?->getVehicleSeries();
        $brand = $series?->getVehicleBrand();

        $settings = $this->vehicleFrontendSettingsRepository->getCurrentOrDefaultForPool($pool);
        $requestedVariant = trim((string) $request->query->get('templateVariant', ''));
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

        $data = [
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
            'stages' => array_map(
                fn (VehicleStage $stage): array => $this->mapStagePayload($stage, $showPrices, $featureFlags['showDyno']),
                $stages
            ),
            'ecus' => $this->mapEcuPayloads($allEcus, $showPrices, $featureFlags['showHints'], $featureFlags['showOptionCategories']),
            'engineOptions' => $this->mapEngineOptionPayloads($engine, $showPrices, $featureFlags['showOptionCategories']),
            'gearboxes' => $featureFlags['showGearbox'] ? $this->mapGearboxPayloads($gearboxes) : [],
            'einbauHinweise' => $featureFlags['showHints'] ? $this->mapEinbauHinweise($engine) : [],
            'editOptions' => $featureFlags['allowVehicleEditing'] ? $this->buildEditOptionsPayload($allEcus) : [
                'ecus' => [],
            ],
            'showPrices' => $showPrices,
            'showEcoStage' => $featureFlags['showEcoStage'],
            'showOptionCategories' => $featureFlags['showOptionCategories'],
            'pool' => [
                'id' => $pool->getId(),
                'key' => $pool->getPoolKey(),
                'name' => $pool->getName(),
            ],
        ];

        if ($featureFlags['allowVehicleEditing']) {
            $data['editingAllowed'] = true;
        }

        return new JsonResponse(['data' => $data]);
    }

    #[Route('/track', name: 'api_vehicle_database_track', methods: ['POST'])]
    public function track(Request $request): JsonResponse
    {
        if (($dbToken = $this->authorizeVehicleApi($request)) instanceof JsonResponse) {
            return $dbToken;
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException) {
            $payload = [];
        }

        $rawEvent = strtolower(trim((string) ($payload['event'] ?? 'client_event')));
        if ('' === $rawEvent || preg_match('/^[a-z0-9._:-]{2,80}$/', $rawEvent) !== 1) {
            $rawEvent = 'client_event';
        }

        $this->vehicleUsageTracker->track($request, 'vdb.api.client.' . $rawEvent, [
            'source' => 'api',
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

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/admin/stages/save', name: 'api_vehicle_database_admin_stages_save', methods: ['POST'])]
    public function saveStage(Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException) {
            return $this->errorResponse('Ungueltiger JSON-Body.', Response::HTTP_BAD_REQUEST);
        }

        $stageId = (int) ($payload['id'] ?? 0);
        if ($stageId <= 0) {
            $engineId = (int) ($payload['engineId'] ?? 0);
            $engine = $this->entityManager->getRepository(VehicleEngine::class)->find($engineId);
            if (!$engine instanceof VehicleEngine || $engine->getVehicleDataPool()?->getId() !== $pool->getId()) {
                return $this->errorResponse('Motor nicht gefunden.', Response::HTTP_NOT_FOUND);
            }

            if ($this->countStagesForEngine($engine) >= 2) {
                return $this->errorResponse('Es sind bereits 2 Stages vorhanden. Neue Stage kann nicht angelegt werden.', Response::HTTP_CONFLICT);
            }
        }

        $stage = $this->resolveStageFromPayload($payload, $pool);
        if (!$stage instanceof VehicleStage) {
            return $this->errorResponse('Stage nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $stage
            ->setName($this->sanitizeString($payload['name'] ?? $stage->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $stage->getDescription()))
            ->setPrice($this->sanitizeNullableString($payload['price'] ?? $stage->getPrice()))
            ->setHp($this->sanitizeNullableInt($payload['hp'] ?? $stage->getHp()))
            ->setNm($this->sanitizeNullableInt($payload['nm'] ?? $stage->getNm()))
            ->setEcu($this->sanitizeNullableInt($payload['ecu'] ?? $stage->getEcu()))
            ->setSlug($this->sanitizeNullableString($payload['slug'] ?? $stage->getSlug()))
            ->setOriginalPs($this->sanitizeNullableInt($payload['originalPs'] ?? $stage->getOriginalPs()))
            ->setOriginalNm($this->sanitizeNullableInt($payload['originalNm'] ?? $stage->getOriginalNm()))
            ->setRpmStart($this->sanitizeNullableInt($payload['rpmStart'] ?? $stage->getRpmStart()))
            ->setRpmEnd($this->sanitizeNullableInt($payload['rpmEnd'] ?? $stage->getRpmEnd()))
            ->setEngineType($this->sanitizeNullableString($payload['engineType'] ?? $stage->getEngineType()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $stage->isActive()));

        $this->entityManager->persist($stage);
        $this->entityManager->flush();

        return new JsonResponse([
            'data' => [
                'id' => $stage->getId(),
                'engineId' => $stage->getVehicleEngine()?->getId(),
                'vehicleDataPoolId' => $stage->getVehicleDataPool()?->getId(),
                'name' => $stage->getName(),
                'description' => $stage->getDescription(),
                'price' => $stage->getPrice(),
                'hp' => $stage->getHp(),
                'nm' => $stage->getNm(),
                'ecu' => $stage->getEcu(),
                'slug' => $stage->getSlug(),
                'originalPs' => $stage->getOriginalPs(),
                'originalNm' => $stage->getOriginalNm(),
                'rpmStart' => $stage->getRpmStart(),
                'rpmEnd' => $stage->getRpmEnd(),
                'engineType' => $stage->getEngineType(),
                'active' => $stage->isActive(),
            ],
        ]);
    }

    #[Route('/admin/series/save', name: 'api_vehicle_database_admin_series_save', methods: ['POST'])]
    public function saveSeries(Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException) {
            return $this->errorResponse('Ungueltiger JSON-Body.', Response::HTTP_BAD_REQUEST);
        }

        $seriesId = (int) ($payload['id'] ?? 0);
        $series = $this->entityManager->getRepository(VehicleSeries::class)->find($seriesId);
        if (!$series instanceof \App\Entity\VehicleSeries || $series->getVehicleDataPool()?->getId() !== $pool->getId()) {
            return $this->errorResponse('Baureihe nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $series
            ->setName($this->sanitizeString($payload['name'] ?? $series->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $series->getDescription()))
            ->setSlug($this->sanitizeNullableString($payload['slug'] ?? $series->getSlug()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $series->isActive()))
            ->setSorting($this->sanitizeNullableInt($payload['sorting'] ?? $series->getSorting()) ?? 0);

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $series->getId(),
            'name' => $series->getName(),
            'description' => $series->getDescription(),
            'slug' => $series->getSlug(),
            'active' => $series->isActive(),
            'sorting' => $series->getSorting(),
        ]]);
    }

    #[Route('/admin/models/save', name: 'api_vehicle_database_admin_models_save', methods: ['POST'])]
    public function saveModel(Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException) {
            return $this->errorResponse('Ungueltiger JSON-Body.', Response::HTTP_BAD_REQUEST);
        }

        $modelId = (int) ($payload['id'] ?? 0);
        $model = $this->entityManager->getRepository(VehicleModel::class)->find($modelId);
        if (!$model instanceof \App\Entity\VehicleModel || $model->getVehicleDataPool()?->getId() !== $pool->getId()) {
            return $this->errorResponse('Modell nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $model
            ->setName($this->sanitizeString($payload['name'] ?? $model->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $model->getDescription()))
            ->setSlug($this->sanitizeNullableString($payload['slug'] ?? $model->getSlug()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $model->isActive()))
            ->setSorting($this->sanitizeNullableInt($payload['sorting'] ?? $model->getSorting()) ?? 0);

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $model->getId(),
            'name' => $model->getName(),
            'description' => $model->getDescription(),
            'slug' => $model->getSlug(),
            'active' => $model->isActive(),
            'sorting' => $model->getSorting(),
        ]]);
    }

    #[Route('/admin/engines/save', name: 'api_vehicle_database_admin_engines_save', methods: ['POST'])]
    public function saveEngine(Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException) {
            return $this->errorResponse('Ungueltiger JSON-Body.', Response::HTTP_BAD_REQUEST);
        }

        $engineId = (int) ($payload['id'] ?? 0);
        $engine = $this->entityManager->getRepository(VehicleEngine::class)->find($engineId);
        if (!$engine instanceof VehicleEngine || $engine->getVehicleDataPool()?->getId() !== $pool->getId()) {
            return $this->errorResponse('Motor nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $engine
            ->setName($this->sanitizeString($payload['name'] ?? $engine->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $engine->getDescription()))
            ->setSlug($this->sanitizeNullableString($payload['slug'] ?? $engine->getSlug()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $engine->isActive()))
            ->setSorting($this->sanitizeNullableInt($payload['sorting'] ?? $engine->getSorting()) ?? 0)
            ->setHorsepower($this->sanitizeNullableInt($payload['horsepower'] ?? $engine->getHorsepower()))
            ->setTorque($this->sanitizeNullableInt($payload['torque'] ?? $engine->getTorque()))
            ->setFuel($this->sanitizeNullableString($payload['fuel'] ?? $engine->getFuel()))
            ->setDisplacement($this->sanitizeNullableString($payload['displacement'] ?? $engine->getDisplacement()))
            ->setEcus($this->sanitizeNullableInt($payload['ecus'] ?? $engine->getEcus()));

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $engine->getId(),
            'name' => $engine->getName(),
            'description' => $engine->getDescription(),
            'slug' => $engine->getSlug(),
            'active' => $engine->isActive(),
            'sorting' => $engine->getSorting(),
            'horsepower' => $engine->getHorsepower(),
            'torque' => $engine->getTorque(),
            'fuel' => $engine->getFuel(),
            'displacement' => $engine->getDisplacement(),
            'ecus' => $engine->getEcus(),
        ]]);
    }

    #[Route('/admin/options/save', name: 'api_vehicle_database_admin_options_save', methods: ['POST'])]
    public function saveOption(Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException) {
            return $this->errorResponse('Ungueltiger JSON-Body.', Response::HTTP_BAD_REQUEST);
        }

        $optionId = (int) ($payload['id'] ?? 0);
        $option = $this->entityManager->getRepository(EcuOption::class)->find($optionId);
        if (!$option instanceof EcuOption || $option->getVehicleDataPool()?->getId() !== $pool->getId()) {
            return $this->errorResponse('Option nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $option
            ->setName($this->sanitizeString($payload['name'] ?? $option->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $option->getDescription()))
            ->setTooltipText($this->sanitizeNullableString($payload['tooltipText'] ?? $option->getTooltipText()))
            ->setSlug($this->sanitizeNullableString($payload['slug'] ?? $option->getSlug()))
            ->setPrice($this->sanitizeNullableString($payload['price'] ?? $option->getPrice()))
            ->setIcon($this->sanitizeNullableString($payload['icon'] ?? $option->getIcon()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $option->isActive()))
            ->setIsMainOption($this->sanitizeBool($payload['isMainOption'] ?? $option->isMainOption()));

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $option->getId(),
            'name' => $option->getName(),
            'description' => $option->getDescription(),
            'tooltipText' => $option->getTooltipText(),
            'slug' => $option->getSlug(),
            'price' => $option->getPrice(),
            'icon' => $option->getIcon(),
            'active' => $option->isActive(),
            'isMainOption' => $option->isMainOption(),
        ]]);
    }

    #[Route('/admin/ecus/save', name: 'api_vehicle_database_admin_ecus_save', methods: ['POST'])]
    public function saveEcu(Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException) {
            return $this->errorResponse('Ungueltiger JSON-Body.', Response::HTTP_BAD_REQUEST);
        }

        $ecuId = (int) ($payload['id'] ?? 0);
        $ecu = $this->entityManager->getRepository(Ecu::class)->find($ecuId);
        if (!$ecu instanceof Ecu || $ecu->getVehicleDataPool()?->getId() !== $pool->getId()) {
            return $this->errorResponse('ECU nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $ecu
            ->setName($this->sanitizeString($payload['name'] ?? $ecu->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $ecu->getDescription()))
            ->setType($this->sanitizeNullableString($payload['type'] ?? $ecu->getType()))
            ->setControlUnitKind($this->sanitizeNullableString($payload['controlUnitKind'] ?? $ecu->getControlUnitKind()))
            ->setManufacturer($this->resolveEntityReference(EcuManufacturer::class, $payload['manufacturerId'] ?? null))
            ->setToolManufacturer($this->resolveEntityReference(ToolManufacturer::class, $payload['toolManufacturerId'] ?? null))
            ->setImage($this->sanitizeNullableString($payload['image'] ?? $ecu->getImage()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $ecu->isActive()));

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $ecu->getId(),
            'name' => $ecu->getName(),
            'description' => $ecu->getDescription(),
            'type' => $ecu->getType(),
            'controlUnitKind' => $ecu->getControlUnitKind(),
            'manufacturerId' => $ecu->getManufacturer()?->getId(),
            'toolManufacturerId' => $ecu->getToolManufacturer()?->getId(),
            'image' => $ecu->getImage(),
            'active' => $ecu->isActive(),
        ]]);
    }

    #[Route('/admin/gearboxes/save', name: 'api_vehicle_database_admin_gearboxes_save', methods: ['POST'])]
    public function saveGearbox(Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException) {
            return $this->errorResponse('Ungueltiger JSON-Body.', Response::HTTP_BAD_REQUEST);
        }

        $gearboxId = (int) ($payload['id'] ?? 0);
        $gearbox = $this->entityManager->getRepository(Gearbox::class)->find($gearboxId);
        if (!$gearbox instanceof Gearbox || $gearbox->getVehicleDataPool()?->getId() !== $pool->getId()) {
            return $this->errorResponse('Getriebe nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $gearbox
            ->setTitle($this->sanitizeString($payload['title'] ?? $gearbox->getTitle(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $gearbox->getDescription()))
            ->setIcon($this->sanitizeNullableString($payload['icon'] ?? $gearbox->getIcon()))
            ->setGearboxType($this->resolveEntityReference(GearboxType::class, $payload['gearboxTypeId'] ?? null))
            ->setManufacturer($this->resolveEntityReference(EcuManufacturer::class, $payload['manufacturerId'] ?? null))
            ->setSorting($this->sanitizeNullableInt($payload['sorting'] ?? $gearbox->getSorting()) ?? 0)
            ->setActive($this->sanitizeBool($payload['active'] ?? $gearbox->isActive()));

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $gearbox->getId(),
            'title' => $gearbox->getTitle(),
            'description' => $gearbox->getDescription(),
            'icon' => $gearbox->getIcon(),
            'gearboxTypeId' => $gearbox->getGearboxType()?->getId(),
            'manufacturerId' => $gearbox->getManufacturer()?->getId(),
            'sorting' => $gearbox->getSorting(),
            'active' => $gearbox->isActive(),
        ]]);
    }

    #[Route('/admin/hints/save', name: 'api_vehicle_database_admin_hints_save', methods: ['POST'])]
    public function saveHint(Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException) {
            return $this->errorResponse('Ungueltiger JSON-Body.', Response::HTTP_BAD_REQUEST);
        }

        $hintId = (int) ($payload['id'] ?? 0);
        $hint = $this->entityManager->getRepository(\App\Entity\EinbauHinweis::class)->find($hintId);
        if (!$hint instanceof \App\Entity\EinbauHinweis) {
            return $this->errorResponse('Hinweis nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $hint
            ->setTitle($this->sanitizeString($payload['title'] ?? $hint->getTitle(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $hint->getDescription()))
            ->setEcuOptionMaster($this->resolveEntityReference(EcuOptionMaster::class, $payload['ecuOptionMasterId'] ?? null))
            ->setActiveEcu($this->resolveEntityReference(Ecu::class, $payload['activeEcuId'] ?? null))
            ->setActive($this->sanitizeBool($payload['active'] ?? $hint->isActive()));

        $this->syncHintImages($hint, $this->sanitizeIdList($payload['imageIds'] ?? null));

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $hint->getId(),
            'title' => $hint->getTitle(),
            'description' => $hint->getDescription(),
            'ecuOptionMasterId' => $hint->getEcuOptionMaster()?->getId(),
            'activeEcuId' => $hint->getActiveEcu()?->getId(),
            'active' => $hint->isActive(),
        ]]);
    }

    #[Route('/admin/tool-entries/save', name: 'api_vehicle_database_admin_tool_entries_save', methods: ['POST'])]
    public function saveToolEntry(Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException) {
            return $this->errorResponse('Ungueltiger JSON-Body.', Response::HTTP_BAD_REQUEST);
        }

        $entryId = (int) ($payload['id'] ?? 0);
        $kind = strtolower((string) ($payload['kind'] ?? 'ecu'));

        if ('gearbox' === $kind) {
            $entry = $this->entityManager->getRepository(GearboxToolEntry::class)->find($entryId);
            if (!$entry instanceof GearboxToolEntry || $entry->getGearbox()?->getVehicleDataPool()?->getId() !== $pool->getId()) {
                return $this->errorResponse('Werkzeug-Eintrag nicht gefunden.', Response::HTTP_NOT_FOUND);
            }

            $entry->setToolManufacturer($this->resolveEntityReference(ToolManufacturer::class, $payload['toolManufacturerId'] ?? null));
            $entry->setPosition($this->sanitizeNullableInt($payload['position'] ?? $entry->getPosition()));
            $this->syncToolMethods($entry, $this->sanitizeIdList($payload['methodIds'] ?? null));
        } else {
            $entry = $this->entityManager->getRepository(EcuToolEntry::class)->find($entryId);
            if (!$entry instanceof EcuToolEntry || $entry->getEcu()?->getVehicleDataPool()?->getId() !== $pool->getId()) {
                return $this->errorResponse('Werkzeug-Eintrag nicht gefunden.', Response::HTTP_NOT_FOUND);
            }

            $entry->setToolManufacturer($this->resolveEntityReference(ToolManufacturer::class, $payload['toolManufacturerId'] ?? null));
            $entry->setPosition($this->sanitizeNullableInt($payload['position'] ?? $entry->getPosition()));
            $this->syncToolMethods($entry, $this->sanitizeIdList($payload['methodIds'] ?? null));
        }

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $entryId,
            'position' => $this->sanitizeNullableInt($payload['position'] ?? null),
            'toolManufacturerId' => $entry->getToolManufacturer()?->getId(),
        ]]);
    }

    #[Route('/admin/stages/{id}/inactive', name: 'api_vehicle_database_admin_stages_inactive', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deactivateStage(int $id, Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        $stage = $this->entityManager->getRepository(VehicleStage::class)->find($id);
        if (!$stage instanceof VehicleStage || $stage->getVehicleDataPool()?->getId() !== $pool->getId()) {
            return $this->errorResponse('Stage nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $stage->setActive(false);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['id' => $stage->getId(), 'active' => false]]);
    }

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
     * @param array<int, Ecu> $ecus
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapEcuPayloads(array $ecus, bool $showPrices, bool $showHints = true, bool $showOptionCategories = true): array
    {
        return array_values(array_map(function (Ecu $ecu) use ($showPrices, $showHints, $showOptionCategories): array {
            $controlUnitKind = $this->resolveEcuControlUnitKind($ecu);
            $isTransmissionControlUnit = 'TCU' === strtoupper(trim((string) ($controlUnitKind ?? '')));

            return [
                'id' => $ecu->getId(),
                'name' => $this->resolveEcuName($ecu),
                'type' => $this->resolveEcuType($ecu),
                'controlUnitKind' => $controlUnitKind,
                'manufacturer' => $this->resolveEcuManufacturerName($ecu),
                'manufacturerId' => $ecu->getManufacturer()?->getId(),
                'toolManufacturerId' => $ecu->getToolManufacturer()?->getId(),
                'manufacturerLogo' => $this->normalizePublicPath($this->resolveEcuManufacturerLogo($ecu)),
                'options' => $isTransmissionControlUnit ? [] : $this->mapEcuOptionPayloads($ecu, $showPrices, $showOptionCategories),
                'optionHints' => $showHints ? $this->mapEcuOptionHints($ecu) : [],
                'toolEntries' => $this->mapEcuToolEntries($ecu),
                'description' => $ecu->getDescription(),
                'image' => $this->normalizePublicPath($ecu->getImage()),
                'active' => true,
            ];
        }, $ecus));
    }

    /**
     * @return array<int, array{id: int|null, toolManufacturer: string|null, toolManufacturerLogo: string|null, methodIds: array<int, int>, methods: array<int, string>, kind: string}>
     */
    private function mapEcuToolEntries(Ecu $ecu): array
    {
        $entries = $ecu->getToolEntries()->toArray();
        usort($entries, static fn (\App\Entity\EcuToolEntry $a, \App\Entity\EcuToolEntry $b): int => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

        return array_values(array_map(fn (\App\Entity\EcuToolEntry $entry): array => [
            'id' => $entry->getId(),
            'toolManufacturer' => $entry->getToolManufacturer()?->getName(),
            'toolManufacturerId' => $entry->getToolManufacturer()?->getId(),
            'toolManufacturerLogo' => $this->normalizePublicPath($entry->getToolManufacturer()?->getLogo()),
            'methodIds' => array_values(array_map(
                static fn (ToolMethode $m): int => $m->getId() ?? 0,
                $entry->getToolMethods()->toArray()
            )),
            'methods' => array_values(array_map(
                static fn (ToolMethode $m): string => $m->getName(),
                $entry->getToolMethods()->toArray()
            )),
            'position' => $entry->getPosition(),
            'kind' => 'ecu',
        ], $entries));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapEcuOptionPayloads(Ecu $ecu, bool $showPrices, bool $showOptionCategories): array
    {
        $options = array_values(array_filter(
            $ecu->getOptions()->toArray(),
            static fn (mixed $option): bool => $option instanceof EcuOption
                && null !== $option->getId()
                && $option->isActive()
                && $option->getVehicleDataPool()?->getId() === $ecu->getVehicleDataPool()?->getId()
        ));

        return $this->mapOptionPayloads($options, $showPrices, $showOptionCategories);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapEngineOptionPayloads(VehicleEngine $engine, bool $showPrices, bool $showOptionCategories): array
    {
        $options = array_values(array_filter(
            $engine->getOptions()->toArray(),
            static fn (mixed $option): bool => $option instanceof EcuOption
                && null !== $option->getId()
                && $option->isActive()
                && $option->getVehicleDataPool()?->getId() === $engine->getVehicleDataPool()?->getId()
        ));

        return $this->mapOptionPayloads($options, $showPrices, $showOptionCategories);
    }

    /**
     * @param array<int, EcuOption> $options
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
    private function mapEcuOptionHints(Ecu $ecu): array
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
    private function mapGearboxPayloads(array $gearboxes): array
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
            'toolEntries' => $this->mapGearboxToolEntries($gearbox),
            'sorting' => $gearbox->getSorting(),
            'active' => $gearbox->isActive(),
        ], $gearboxes));
    }

    /**
     * @return array<int, array{id: int|null, toolManufacturer: string|null, toolManufacturerLogo: string|null, methodIds: array<int, int>, methods: array<int, string>, kind: string}>
     */
    private function mapGearboxToolEntries(Gearbox $gearbox): array
    {
        $entries = $gearbox->getToolEntries()->toArray();
        usort($entries, static fn (\App\Entity\GearboxToolEntry $a, \App\Entity\GearboxToolEntry $b): int => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

        return array_values(array_map(fn (\App\Entity\GearboxToolEntry $entry): array => [
            'id' => $entry->getId(),
            'toolManufacturer' => $entry->getToolManufacturer()?->getName(),
            'toolManufacturerId' => $entry->getToolManufacturer()?->getId(),
            'toolManufacturerLogo' => $this->normalizePublicPath($entry->getToolManufacturer()?->getLogo()),
            'methodIds' => array_values(array_map(
                static fn (ToolMethode $m): int => $m->getId() ?? 0,
                $entry->getToolMethods()->toArray()
            )),
            'methods' => array_values(array_map(
                static fn (ToolMethode $m): string => $m->getName(),
                $entry->getToolMethods()->toArray()
            )),
            'position' => $entry->getPosition(),
            'kind' => 'gearbox',
        ], $entries));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapEinbauHinweise(VehicleEngine $engine): array
    {
        $hinweise = $this->einbauHinweisRepository->findForEngine($engine);

        return array_values(array_map(fn (\App\Entity\EinbauHinweis $h): array => [
            'id' => $h->getId(),
            'title' => $h->getTitle(),
            'description' => $h->getDescription(),
            'ecuOptionMaster' => $h->getEcuOptionMaster()?->getName(),
            'ecuOptionMasterId' => $h->getEcuOptionMaster()?->getId(),
            'activeEcuId' => $h->getActiveEcu()?->getId(),
            'images' => array_values(array_map(
                fn (\App\Entity\MediaAsset $a): string => $this->normalizePublicPath($a->getFilePath()) ?? '',
                $h->getImages()->toArray()
            )),
            'imageIds' => array_values(array_map(
                static fn (MediaAsset $a): int => $a->getId() ?? 0,
                $h->getImages()->toArray()
            )),
            'active' => $h->isActive(),
        ], $hinweise));
    }

    /**
     * @param array<int, VehicleStage> $stages
     *
     * @return array<int, VehicleStage>
     */
    private function filterStagesBySettings(array $stages, bool $showOnlyStage1): array
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
     * @return array<int, string>
     */
    private static function toPathList(?string $value): array
    {
        if (null === $value || '' === trim($value)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(array_filter(array_map(fn (string $line): string => trim($line), $lines)));
    }

    private function authorizeVehicleApi(Request $request): ApiToken|JsonResponse
    {
        $provided = $this->getBearerToken($request);
        if (null === $provided) {
            return $this->errorResponse('Bearer-Token fehlt.', Response::HTTP_UNAUTHORIZED);
        }

        $tokenIdentifier = trim((string) $request->headers->get('X-Token-Id', ''));
        if ('' === $tokenIdentifier) {
            return $this->errorResponse('X-Token-Id fehlt.', Response::HTTP_UNAUTHORIZED);
        }

        $dbToken = $this->apiTokenRepository->findValidByTokenAndIdentifier($provided, $tokenIdentifier);

        if (null === $dbToken) {
            return $this->errorResponse('API-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        return $dbToken;
    }

    /**
     * @return array{0: ApiToken|JsonResponse, 1: VehicleDataPool|null, 2: VehicleDataContext|null, 3: array{showPrices: bool, showHints: bool, showGearbox: bool, showDyno: bool, showEcoStage: bool, showOptionCategories: bool, allowVehicleEditing: bool}}
     */
    private function resolveEditableScope(Request $request): array
    {
        $dbToken = $this->authorizeVehicleApi($request);
        if ($dbToken instanceof JsonResponse) {
            return [$dbToken, null, null, [
                'showPrices' => false,
                'showHints' => false,
                'showGearbox' => false,
                'showDyno' => false,
                'showEcoStage' => false,
                'showOptionCategories' => false,
                'allowVehicleEditing' => false,
            ]];
        }

        $pool = $this->vehicleDataContextResolver->resolvePoolForRequest($request, $dbToken);
        $context = $this->vehicleDataContextResolver->resolveContextForRequest($request, $dbToken);
        $featureFlags = $this->vehicleDataContextResolver->resolveFeatureFlags($pool, $context);

        return [$dbToken, $pool, $context, $featureFlags];
    }

    private function resolveStageFromPayload(array $payload, ?VehicleDataPool $pool): ?VehicleStage
    {
        $stageId = (int) ($payload['id'] ?? 0);
        if ($stageId > 0) {
            $stage = $this->entityManager->getRepository(VehicleStage::class)->find($stageId);
            if ($stage instanceof VehicleStage && $stage->getVehicleDataPool()?->getId() === $pool?->getId()) {
                return $stage;
            }

            return null;
        }

        $engineId = (int) ($payload['engineId'] ?? 0);
        if ($engineId <= 0) {
            return null;
        }

        $engine = $this->entityManager->getRepository(VehicleEngine::class)->find($engineId);
        if (!$engine instanceof VehicleEngine || $engine->getVehicleDataPool()?->getId() !== $pool?->getId()) {
            return null;
        }

        $stage = new VehicleStage();
        $stage->setVehicleEngine($engine);
        $stage->setVehicleDataPool($engine->getVehicleDataPool());

        $copyFromStageId = (int) ($payload['copyFromStageId'] ?? 0);
        if ($copyFromStageId > 0) {
            $source = $this->entityManager->getRepository(VehicleStage::class)->find($copyFromStageId);
            if ($source instanceof VehicleStage && $source->getVehicleDataPool()?->getId() === $pool?->getId()) {
                $stage
                    ->setName($source->getName())
                    ->setDescription($source->getDescription())
                    ->setPrice($source->getPrice())
                    ->setHp($source->getHp())
                    ->setNm($source->getNm())
                    ->setImages($source->getImages())
                    ->setVideos($source->getVideos())
                    ->setEcu($source->getEcu())
                    ->setIcon($source->getIcon())
                    ->setIconMediaAsset($source->getIconMediaAsset())
                    ->setOriginalPs($source->getOriginalPs())
                    ->setOriginalNm($source->getOriginalNm())
                    ->setRpmStart($source->getRpmStart())
                    ->setRpmEnd($source->getRpmEnd())
                    ->setDynoChart($source->getDynoChart())
                    ->setEngineType($source->getEngineType())
                    ->setActive($source->isActive());
            }
        }

        return $stage;
    }

    private function countStagesForEngine(VehicleEngine $engine): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(stage.id)')
            ->from(VehicleStage::class, 'stage')
            ->where('stage.vehicleEngine = :engine')
            ->andWhere('stage.vehicleDataPool = :pool')
            ->setParameter('engine', $engine)
            ->setParameter('pool', $engine->getVehicleDataPool())
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function sanitizeString(mixed $value, bool $fallbackEmpty = false): string
    {
        $text = trim((string) ($value ?? ''));
        if ('' !== $text) {
            return $text;
        }

        return $fallbackEmpty ? '' : '';
    }

    private function sanitizeNullableString(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return '' !== $text ? $text : null;
    }

    private function sanitizeNullableInt(mixed $value): ?int
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function sanitizeBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value !== 0;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @return array<int, int>
     */
    private function sanitizeIdList(mixed $value): array
    {
        $items = [];
        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value) && '' !== trim($value)) {
            $items = preg_split('/[,\s;]+/', $value) ?: [];
        }

        $ids = [];
        foreach ($items as $item) {
            $id = $this->sanitizeNullableInt($item);
            if (null !== $id && $id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function syncToolMethods(object $entry, array $methodIds): void
    {
        if (!method_exists($entry, 'getToolMethods') || !method_exists($entry, 'addToolMethod') || !method_exists($entry, 'removeToolMethod')) {
            return;
        }

        $selected = [];
        foreach ($methodIds as $methodId) {
            $method = $this->entityManager->getRepository(ToolMethode::class)->find($methodId);
            if ($method instanceof ToolMethode) {
                $selected[$method->getId() ?? $methodId] = $method;
            }
        }

        foreach ($entry->getToolMethods()->toArray() as $method) {
            if ($method instanceof ToolMethode && !isset($selected[$method->getId() ?? 0])) {
                $entry->removeToolMethod($method);
            }
        }

        foreach ($selected as $method) {
            $entry->addToolMethod($method);
        }
    }

    private function syncHintImages(\App\Entity\EinbauHinweis $hint, array $imageIds): void
    {
        $selected = [];
        foreach ($imageIds as $imageId) {
            $asset = $this->entityManager->getRepository(MediaAsset::class)->find($imageId);
            if ($asset instanceof MediaAsset) {
                $selected[$asset->getId() ?? $imageId] = $asset;
            }
        }

        foreach ($hint->getImages()->toArray() as $image) {
            if ($image instanceof MediaAsset && !isset($selected[$image->getId() ?? 0])) {
                $hint->removeImage($image);
            }
        }

        foreach ($selected as $asset) {
            $hint->addImage($asset);
        }
    }

    /**
     * @return array{
     *     ecus: array<int, array{id: int|null, name: string}>
     * }
     */
    private function buildEditOptionsPayload(array $currentEcus): array
    {
        $mapItems = static function (array $items): array {
            return array_values(array_map(static fn (object $item): array => [
                'id' => method_exists($item, 'getId') ? $item->getId() : null,
                'name' => method_exists($item, 'getName') ? $item->getName() : (method_exists($item, 'getTitle') ? $item->getTitle() : ''),
            ], $items));
        };

        return [
            'ecus' => $mapItems($currentEcus),
        ];
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T|null
     */
    private function resolveEntityReference(string $class, mixed $value): ?object
    {
        $id = $this->sanitizeNullableInt($value);
        if (null === $id || $id <= 0) {
            return null;
        }

        $entity = $this->entityManager->getRepository($class)->find($id);

        return is_object($entity) ? $entity : null;
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

    private function resolveBrandIconPath(mixed $brand): ?string
    {
        if (!is_object($brand) || !method_exists($brand, 'getSlug') || !method_exists($brand, 'getName')) {
            return null;
        }

        $resolved = $this->brandMasterImageResolver->resolve(
            (string) $brand->getSlug(),
            (string) $brand->getName(),
            method_exists($brand, 'getImage') ? $brand->getImage() : null
        );

        return is_string($resolved) && '' !== trim($resolved) ? $resolved : null;
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

    private function resolveUxIconPublicPath(?string $iconId): ?string
    {
        $iconId = trim((string) ($iconId ?? ''));
        if ('' === $iconId || !str_contains($iconId, ':')) {
            return null;
        }

        [$collection, $iconName] = explode(':', $iconId, 2);
        $collection = trim($collection);
        $iconName = trim($iconName);
        if ('' === $collection || '' === $iconName) {
            return null;
        }

        $relativePath = sprintf('assets/icons/%s/%s.svg', $collection, $iconName);
        $fullPath = $this->getParameter('kernel.project_dir') . '/' . $relativePath;

        return is_file((string) $fullPath) ? '/' . $relativePath : null;
    }

    /** @return array{id: int, name: string, logo: ?string}|null */
    private function mapPerformanceBrand(?PerformanceBrand $performanceBrand): ?array
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
