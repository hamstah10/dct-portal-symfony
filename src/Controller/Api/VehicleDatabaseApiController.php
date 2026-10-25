<?php

namespace App\Controller\Api;

use App\Entity\ApiToken;
use App\Entity\Ecu;
use App\Entity\EcuManufacturer;
use App\Entity\EcuOption;
use App\Entity\EcuOptionMaster;
use App\Entity\EcuToolEntry;
use App\Entity\EinbauHinweis;
use App\Entity\Gearbox;
use App\Entity\GearboxToolEntry;
use App\Entity\GearboxType;
use App\Entity\MediaAsset;
use App\Entity\PerformanceBrand;
use App\Entity\ToolManufacturer;
use App\Entity\ToolMethode;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use App\Entity\VehicleSeries;
use App\Entity\VehicleStage;
use App\Entity\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ApiTokenRepository;
use App\Repository\VehicleBrandRepository;
use App\Repository\VehicleEngineRepository;
use App\Repository\VehicleModelRepository;
use App\Repository\VehicleSeriesRepository;
use App\Repository\VehicleTypeRepository;
use App\Service\Analytics\VehicleUsageTracker;
use App\Service\VehicleDataContextResolver;
use App\Service\VehicleMasterDataService;
use App\Service\VehiclePayloadMapper;
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
        private readonly VehicleUsageTracker $vehicleUsageTracker,
        private readonly VehicleDataContextResolver $vehicleDataContextResolver,
        private readonly VehiclePayloadMapper $vehiclePayloadMapper,
        private readonly EntityManagerInterface $entityManager,
        private readonly VehicleMasterDataService $vehicleMasterDataService,
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
                'POST /api/fahrzeugdatenbank/track',
            ],
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
            'applicationName' => $dbToken->getApplicationName(),
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
                'image' => $this->vehiclePayloadMapper->normalizePublicPath($type->getImage()),
                'icon' => $this->vehiclePayloadMapper->resolveUxIconDataUri($type->getIcon()),
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
            'applicationName' => $dbToken->getApplicationName(),
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
                'name' => $this->vehiclePayloadMapper->resolveBrandName($brand),
                'image' => $this->vehiclePayloadMapper->normalizePublicPath($this->vehiclePayloadMapper->resolveBrandImage($brand)),
                'icon' => $this->vehiclePayloadMapper->normalizePublicPath($this->vehiclePayloadMapper->resolveBrandImage($brand)),
                'showInTopMenu' => $brand->isShowInTopMenu(),
                'synonyms' => $this->resolveBrandSynonyms($brand),
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
            'applicationName' => $dbToken->getApplicationName(),
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
            'applicationName' => $dbToken->getApplicationName(),
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
            'applicationName' => $dbToken->getApplicationName(),
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
            'applicationName' => $dbToken->getApplicationName(),
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

        $requestedVariant = trim((string) $request->query->get('templateVariant', ''));

        return new JsonResponse([
            'data' => $this->vehiclePayloadMapper->buildEngineDetailsData($engine, $pool, $requestedVariant, $featureFlags),
        ]);
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
            'applicationName' => $dbToken->getApplicationName(),
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

    #[Route('/admin/performance-brands', name: 'api_vehicle_database_admin_performance_brands', methods: ['GET'])]
    public function performanceBrands(Request $request): JsonResponse
    {
        [$dbToken] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        $items = array_map(
            static fn (PerformanceBrand $brand): array => ['id' => $brand->getId(), 'name' => $brand->getName()],
            $this->entityManager->getRepository(PerformanceBrand::class)->findBy(['active' => true], ['name' => 'ASC']),
        );

        return new JsonResponse(['data' => $items]);
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
        if ($seriesId <= 0) {
            $brand = $this->entityManager->getRepository(\App\Entity\VehicleBrand::class)->find((int) ($payload['vehicleBrandId'] ?? 0));
            if (!$brand instanceof \App\Entity\VehicleBrand || $brand->getVehicleDataPool()?->getId() !== $pool->getId()) {
                return $this->errorResponse('Marke nicht gefunden.', Response::HTTP_NOT_FOUND);
            }

            $series = (new VehicleSeries())->setVehicleBrand($brand)->setVehicleDataPool($pool);
            $this->entityManager->persist($series);
        } else {
            $series = $this->entityManager->getRepository(VehicleSeries::class)->find($seriesId);
            if (!$series instanceof VehicleSeries || $series->getVehicleDataPool()?->getId() !== $pool->getId()) {
                return $this->errorResponse('Baureihe nicht gefunden.', Response::HTTP_NOT_FOUND);
            }
        }

        $series
            ->setName($this->sanitizeString($payload['name'] ?? $series->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $series->getDescription()))
            ->setSlug($this->sanitizeNullableString($payload['slug'] ?? $series->getSlug()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $series->isActive()))
            ->setSorting($this->sanitizeNullableInt($payload['sorting'] ?? $series->getSorting()) ?? 0)
            ->setImage($this->sanitizeNullableString($payload['image'] ?? $series->getImage()));

        if (array_key_exists('performanceBrandId', $payload)) {
            $series->setPerformanceBrand($this->findPerformanceBrand($payload['performanceBrandId']));
        }

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
        if ($modelId <= 0) {
            $series = $this->entityManager->getRepository(VehicleSeries::class)->find((int) ($payload['vehicleSeriesId'] ?? 0));
            if (!$series instanceof VehicleSeries || $series->getVehicleDataPool()?->getId() !== $pool->getId()) {
                return $this->errorResponse('Baureihe nicht gefunden.', Response::HTTP_NOT_FOUND);
            }

            $model = (new VehicleModel())->setVehicleSeries($series)->setVehicleDataPool($pool);
            $this->entityManager->persist($model);
        } else {
            $model = $this->entityManager->getRepository(VehicleModel::class)->find($modelId);
            if (!$model instanceof VehicleModel || $model->getVehicleDataPool()?->getId() !== $pool->getId()) {
                return $this->errorResponse('Modell nicht gefunden.', Response::HTTP_NOT_FOUND);
            }
        }

        $model
            ->setName($this->sanitizeString($payload['name'] ?? $model->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $model->getDescription()))
            ->setSlug($this->sanitizeNullableString($payload['slug'] ?? $model->getSlug()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $model->isActive()))
            ->setSorting($this->sanitizeNullableInt($payload['sorting'] ?? $model->getSorting()) ?? 0)
            ->setImage($this->sanitizeNullableString($payload['image'] ?? $model->getImage()));

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
        if ($engineId <= 0) {
            $model = $this->entityManager->getRepository(VehicleModel::class)->find((int) ($payload['vehicleModelId'] ?? 0));
            if (!$model instanceof VehicleModel || $model->getVehicleDataPool()?->getId() !== $pool->getId()) {
                return $this->errorResponse('Modell nicht gefunden.', Response::HTTP_NOT_FOUND);
            }

            $engine = (new VehicleEngine())->setVehicleModel($model)->setVehicleDataPool($pool);
            $this->entityManager->persist($engine);
        } else {
            $engine = $this->entityManager->getRepository(VehicleEngine::class)->find($engineId);
            if (!$engine instanceof VehicleEngine || $engine->getVehicleDataPool()?->getId() !== $pool->getId()) {
                return $this->errorResponse('Motor nicht gefunden.', Response::HTTP_NOT_FOUND);
            }
        }

        $engine
            ->setName($this->sanitizeString($payload['name'] ?? $engine->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $engine->getDescription()))
            ->setSlug($this->sanitizeNullableString($payload['slug'] ?? $engine->getSlug()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $engine->isActive()))
            ->setSorting($this->sanitizeNullableInt($payload['sorting'] ?? $engine->getSorting()) ?? 0)
            ->setImage($this->sanitizeNullableString($payload['image'] ?? $engine->getImage()))
            ->setHorsepower($this->sanitizeNullableInt($payload['horsepower'] ?? $engine->getHorsepower()))
            ->setTorque($this->sanitizeNullableInt($payload['torque'] ?? $engine->getTorque()))
            ->setFuel($this->sanitizeNullableString($payload['fuel'] ?? $engine->getFuel()))
            ->setDisplacement($this->sanitizeNullableString($payload['displacement'] ?? $engine->getDisplacement()))
            ->setEcus($this->sanitizeNullableInt($payload['ecus'] ?? $engine->getEcus()))
            ->setCompression($this->sanitizeNullableString($payload['compression'] ?? $engine->getCompression()))
            ->setBoreStroke($this->sanitizeNullableString($payload['boreStroke'] ?? $engine->getBoreStroke()))
            ->setEngineCode($this->sanitizeNullableString($payload['engineCode'] ?? $engine->getEngineCode()))
            ->setVmax($this->sanitizeNullableInt($payload['vmax'] ?? $engine->getVmax()))
            ->setAcceleration0100($this->sanitizeNullableString($payload['acceleration0100'] ?? $engine->getAcceleration0100()))
            ->setAcceleration0200($this->sanitizeNullableString($payload['acceleration0200'] ?? $engine->getAcceleration0200()))
            ->setAcceleration100200($this->sanitizeNullableString($payload['acceleration100200'] ?? $engine->getAcceleration100200()));

        if (array_key_exists('performanceBrandId', $payload)) {
            $engine->setPerformanceBrand($this->findPerformanceBrand($payload['performanceBrandId']));
        }

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
        $isNewEcu = $ecuId <= 0;
        if ($isNewEcu) {
            $ecu = (new Ecu())->setVehicleDataPool($pool);
            $this->entityManager->persist($ecu);
        } else {
            $ecu = $this->findInPool(Ecu::class, $ecuId, $pool);
            if (!$ecu instanceof Ecu) {
                return $this->errorResponse('ECU nicht gefunden.', Response::HTTP_NOT_FOUND);
            }
        }

        $ecu
            ->setName($this->sanitizeString($payload['name'] ?? $ecu->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $ecu->getDescription()))
            ->setType($this->sanitizeNullableString($payload['type'] ?? $ecu->getType()))
            ->setControlUnitKind($this->sanitizeNullableString($payload['controlUnitKind'] ?? $ecu->getControlUnitKind()))
            ->setManufacturer($this->resolveEntityReference(EcuManufacturer::class, $payload['manufacturerId'] ?? null))
            ->setToolManufacturer($this->resolveEntityReference(ToolManufacturer::class, $payload['toolManufacturerId'] ?? null))
            ->setImage($this->sanitizeNullableString($payload['image'] ?? $ecu->getImage()));

        // Neue ECU direkt am Motor verknuepfen, wenn engineId uebergeben wird
        if ($isNewEcu && (int) ($payload['engineId'] ?? 0) > 0) {
            $engine = $this->findInPool(VehicleEngine::class, (int) $payload['engineId'], $pool);
            if (!$engine instanceof VehicleEngine) {
                return $this->errorResponse('Motor nicht gefunden.', Response::HTTP_NOT_FOUND);
            }
            $engine->addEcuRelation($ecu);
        }

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
        $hint = $this->entityManager->getRepository(EinbauHinweis::class)->find($hintId);
        if (!$hint instanceof EinbauHinweis) {
            return $this->errorResponse('Hinweis nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $activeEcu = $this->resolveEntityReference(Ecu::class, $payload['activeEcuId'] ?? null);

        $hint
            ->setTitle($this->sanitizeString($payload['title'] ?? $hint->getTitle(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $hint->getDescription()))
            ->setEcuOptionMaster($this->resolveEntityReference(EcuOptionMaster::class, $payload['ecuOptionMasterId'] ?? null))
            ->setActiveEcu($activeEcu)
            ->setActive($this->sanitizeBool($payload['active'] ?? $hint->isActive()));

        foreach ($hint->getActiveEcus()->toArray() as $ecu) {
            if ($ecu instanceof Ecu) {
                $hint->removeActiveEcu($ecu);
            }
        }
        if ($activeEcu instanceof Ecu) {
            $hint->addActiveEcu($activeEcu);
        }

        $this->syncHintImages($hint, $this->sanitizeIdList($payload['imageIds'] ?? null));

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $hint->getId(),
            'title' => $hint->getTitle(),
            'description' => $hint->getDescription(),
            'ecuOptionMasterId' => $hint->getEcuOptionMaster()?->getId(),
            'activeEcuId' => $hint->getActiveEcu()?->getId(),
            'activeEcuIds' => array_values(array_filter(array_map(
                static fn (Ecu $ecu): ?int => $ecu->getId(),
                $hint->getActiveEcus()->toArray()
            ))),
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

    #[Route('/admin/{entity}/{id}', name: 'api_vehicle_database_admin_delete', requirements: ['entity' => 'series|models|engines|stages', 'id' => '\d+'], methods: ['DELETE'])]
    public function deleteHierarchyEntity(string $entity, int $id, Request $request): JsonResponse
    {
        [$dbToken, $pool, $context, $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        [$class, $childCount, $label] = match ($entity) {
            'series' => [VehicleSeries::class, static fn (object $e): int => $e->getModels()->count(), 'Baureihe'],
            'models' => [VehicleModel::class, static fn (object $e): int => $e->getEngines()->count(), 'Modell'],
            'engines' => [VehicleEngine::class, static fn (object $e): int => $e->getStages()->count(), 'Motor'],
            default => [VehicleStage::class, static fn (object $e): int => 0, 'Stage'],
        };

        $item = $this->entityManager->getRepository($class)->find($id);
        if (!$item instanceof $class || $item->getVehicleDataPool()?->getId() !== $pool->getId()) {
            return $this->errorResponse($label . ' nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $children = $childCount($item);
        if ($children > 0) {
            return $this->errorResponse($label . ' hat noch ' . $children . ' Unterdatensaetze. Bitte zuerst loeschen oder deaktivieren.', Response::HTTP_CONFLICT);
        }

        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['id' => $id, 'deleted' => true]]);
    }

    #[Route('/admin/{entity}/{id}/move', name: 'api_vehicle_database_admin_move', requirements: ['entity' => 'series|models|engines', 'id' => '\d+'], methods: ['POST'])]
    public function moveHierarchyEntity(string $entity, int $id, Request $request): JsonResponse
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

        $config = $this->hierarchyConfig($entity);
        $item = $this->findInPool($config['class'], $id, $pool);
        if (!is_object($item)) {
            return $this->errorResponse($config['label'] . ' nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $parent = $this->findInPool($config['parentClass'], (int) ($payload['parentId'] ?? 0), $pool);
        if (!is_object($parent)) {
            return $this->errorResponse($config['parentLabel'] . ' nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $item->{$config['setParent']}($parent);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['id' => $id, 'parentId' => $parent->getId()]]);
    }

    #[Route('/admin/{entity}/{id}/merge', name: 'api_vehicle_database_admin_merge', requirements: ['entity' => 'series|models|engines', 'id' => '\d+'], methods: ['POST'])]
    public function mergeHierarchyEntity(string $entity, int $id, Request $request): JsonResponse
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

        $config = $this->hierarchyConfig($entity);
        $source = $this->findInPool($config['class'], $id, $pool);
        if (!is_object($source)) {
            return $this->errorResponse($config['label'] . ' nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $targetId = (int) ($payload['targetId'] ?? 0);
        $target = $targetId > 0 && $targetId !== $id ? $this->findInPool($config['class'], $targetId, $pool) : null;
        if (!is_object($target)) {
            return $this->errorResponse('Ziel ' . $config['label'] . ' nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        // Nur Eintraege mit gleichem Elternteil zusammenfuehren, sonst entsteht ein falscher Baum.
        if ($source->{$config['getParent']}()?->getId() !== $target->{$config['getParent']}()?->getId()) {
            return $this->errorResponse('Zusammenfuehren ist nur innerhalb desselben Elternteils moeglich.', Response::HTTP_CONFLICT);
        }

        $moved = 0;
        foreach ($source->{$config['getChildren']}()->toArray() as $child) {
            $child->{$config['childSetParent']}($target);
            ++$moved;
        }

        $this->entityManager->remove($source);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['id' => $id, 'targetId' => $target->getId(), 'movedChildren' => $moved, 'deleted' => true]]);
    }

    #[Route('/admin/brands/{id}', name: 'api_vehicle_database_admin_brands_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function brandDetail(int $id, Request $request): JsonResponse
    {
        [$dbToken, $pool] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        $brand = $this->findInPool(\App\Entity\VehicleBrand::class, $id, $pool);
        if (!$brand instanceof \App\Entity\VehicleBrand) {
            return $this->errorResponse('Marke nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => [
            'id' => $brand->getId(),
            'vehicleTypeId' => $brand->getVehicleType()?->getId(),
            'name' => $brand->getName(),
            'slug' => $brand->getSlug(),
            'description' => $brand->getDescription(),
            'synonyms' => $brand->getSynonyms(),
            'active' => $brand->isActive(),
            'sorting' => $brand->getSorting(),
            'image' => $brand->getImage(),
        ]]);
    }

    #[Route('/admin/types/{id}', name: 'api_vehicle_database_admin_types_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function typeDetail(int $id, Request $request): JsonResponse
    {
        [$dbToken, $pool] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        $type = $this->entityManager->getRepository(VehicleType::class)->find($id);
        if (!$type instanceof VehicleType || !$pool->getVehicleTypes()->contains($type)) {
            return $this->errorResponse('Fahrzeugtyp nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => [
            'id' => $type->getId(),
            'name' => $type->getName(),
            'slug' => $type->getSlug(),
            'description' => $type->getDescription(),
            'active' => $type->isActive(),
            'sorting' => $type->getSorting(),
            'image' => $type->getImage(),
        ]]);
    }

    /** @return array<string, mixed> */
    private function serializeEcu(Ecu $ecu): array
    {
        return [
            'id' => $ecu->getId(),
            'name' => $ecu->getName(),
            'description' => $ecu->getDescription(),
            'type' => $ecu->getType(),
            'controlUnitKind' => $ecu->getControlUnitKind(),
            'image' => $ecu->getImage(),
            'manufacturerId' => $ecu->getManufacturer()?->getId(),
            'toolManufacturerId' => $ecu->getToolManufacturer()?->getId(),
        ];
    }

    #[Route('/admin/ecus', name: 'api_vehicle_database_admin_ecus_list', methods: ['GET'])]
    public function ecuList(Request $request): JsonResponse
    {
        [$dbToken, $pool] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        $ecus = $this->entityManager->getRepository(Ecu::class)->findBy(['vehicleDataPool' => $pool], ['name' => 'ASC']);

        return new JsonResponse(['data' => array_map(fn (Ecu $ecu): array => $this->serializeEcu($ecu), $ecus)]);
    }

    #[Route('/admin/engines/{id}/ecus', name: 'api_vehicle_database_admin_engine_ecus_list', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function engineEcus(int $id, Request $request): JsonResponse
    {
        [$dbToken, $pool] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        $engine = $this->findInPool(VehicleEngine::class, $id, $pool);
        if (!$engine instanceof VehicleEngine) {
            return $this->errorResponse('Motor nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $ecus = array_values(array_filter(
            $engine->getEcuRelations()->toArray(),
            static fn (Ecu $ecu): bool => $ecu->getVehicleDataPool()?->getId() === $pool->getId()
        ));
        usort($ecus, static fn (Ecu $a, Ecu $b): int => strnatcasecmp($a->getName(), $b->getName()));

        return new JsonResponse(['data' => array_map(fn (Ecu $ecu): array => $this->serializeEcu($ecu), $ecus)]);
    }

    #[Route('/admin/engines/{id}/ecus', name: 'api_vehicle_database_admin_engine_ecus_link', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function linkEngineEcu(int $id, Request $request): JsonResponse
    {
        [$dbToken, $pool, , $featureFlags] = $this->resolveEditableScope($request);
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

        $engine = $this->findInPool(VehicleEngine::class, $id, $pool);
        if (!$engine instanceof VehicleEngine) {
            return $this->errorResponse('Motor nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $ecu = $this->findInPool(Ecu::class, (int) ($payload['ecuId'] ?? 0), $pool);
        if (!$ecu instanceof Ecu) {
            return $this->errorResponse('ECU nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        if (!$engine->getEcuRelations()->contains($ecu)) {
            $engine->addEcuRelation($ecu);
            $this->entityManager->flush();
        }

        return new JsonResponse(['data' => ['engineId' => $id, 'ecuId' => $ecu->getId(), 'linked' => true]]);
    }

    #[Route('/admin/engines/{id}/ecus/{ecuId}', name: 'api_vehicle_database_admin_engine_ecus_unlink', requirements: ['id' => '\d+', 'ecuId' => '\d+'], methods: ['DELETE'])]
    public function unlinkEngineEcu(int $id, int $ecuId, Request $request): JsonResponse
    {
        [$dbToken, $pool, , $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        $engine = $this->findInPool(VehicleEngine::class, $id, $pool);
        if (!$engine instanceof VehicleEngine) {
            return $this->errorResponse('Motor nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $ecu = $this->findInPool(Ecu::class, $ecuId, $pool);
        if (!$ecu instanceof Ecu) {
            return $this->errorResponse('ECU nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $engine->removeEcuRelation($ecu);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['engineId' => $id, 'ecuId' => $ecuId, 'linked' => false]]);
    }

    #[Route('/admin/editing-allowed', name: 'api_vehicle_database_admin_editing_allowed', methods: ['GET'])]
    public function editingAllowed(Request $request): JsonResponse
    {
        [$dbToken, , , $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        return new JsonResponse(['data' => ['allowVehicleEditing' => (bool) $featureFlags['allowVehicleEditing']]]);
    }

    #[Route('/admin/{entity}/{id}', name: 'api_vehicle_database_admin_hierarchy_detail', requirements: ['entity' => 'series|models|engines', 'id' => '\d+'], methods: ['GET'])]
    public function hierarchyDetail(string $entity, int $id, Request $request): JsonResponse
    {
        [$dbToken, $pool, , $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        $config = $this->hierarchyConfig($entity);
        $item = $this->findInPool($config['class'], $id, $pool);
        if (!is_object($item)) {
            return $this->errorResponse($config['label'] . ' nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        // Rohdaten statt Listenfelder: sonst wuerde das Speichern im Editor Felder leeren.
        $row = [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'description' => $item->getDescription(),
            'slug' => $item->getSlug(),
            'active' => $item->isActive(),
            'sorting' => $item->getSorting(),
            'image' => $item->getImage(),
        ];
        if ($item instanceof VehicleSeries) {
            $row['vehicleBrandId'] = $item->getVehicleBrand()?->getId();
            $row['performanceBrandId'] = $item->getPerformanceBrand()?->getId();
        } elseif ($item instanceof VehicleModel) {
            $row['vehicleSeriesId'] = $item->getVehicleSeries()?->getId();
        } elseif ($item instanceof VehicleEngine) {
            $row['vehicleModelId'] = $item->getVehicleModel()?->getId();
            $row['horsepower'] = $item->getHorsepower();
            $row['torque'] = $item->getTorque();
            $row['ecus'] = $item->getEcus();
            $row['fuel'] = $item->getFuel();
            $row['displacement'] = $item->getDisplacement();
            $row['performanceBrandId'] = $item->getPerformanceBrand()?->getId();
            $row['compression'] = $item->getCompression();
            $row['boreStroke'] = $item->getBoreStroke();
            $row['engineCode'] = $item->getEngineCode();
            $row['vmax'] = $item->getVmax();
            $row['acceleration0100'] = $item->getAcceleration0100();
            $row['acceleration0200'] = $item->getAcceleration0200();
            $row['acceleration100200'] = $item->getAcceleration100200();
        }

        return new JsonResponse(['data' => $row]);
    }

    #[Route('/admin/brands/save', name: 'api_vehicle_database_admin_brands_save', methods: ['POST'])]
    public function saveBrand(Request $request): JsonResponse
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

        $brandId = (int) ($payload['id'] ?? 0);
        if ($brandId > 0) {
            $brand = $this->findInPool(\App\Entity\VehicleBrand::class, $brandId, $pool);
            if (!$brand instanceof \App\Entity\VehicleBrand) {
                return $this->errorResponse('Marke nicht gefunden.', Response::HTTP_NOT_FOUND);
            }
        } else {
            // Neue Marke: Typ muss zum Pool gehoeren
            $type = $this->entityManager->getRepository(VehicleType::class)->find((int) ($payload['vehicleTypeId'] ?? 0));
            if (!$type instanceof VehicleType || !$pool->getVehicleTypes()->contains($type)) {
                return $this->errorResponse('Fahrzeugtyp nicht gefunden.', Response::HTTP_NOT_FOUND);
            }

            $brand = (new \App\Entity\VehicleBrand())->setVehicleType($type)->setVehicleDataPool($pool);
            $this->entityManager->persist($brand);
        }

        $brand
            ->setName($this->sanitizeString($payload['name'] ?? $brand->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $brand->getDescription()))
            ->setSlug($this->sanitizeNullableString($payload['slug'] ?? $brand->getSlug()))
            ->setSynonyms($this->sanitizeNullableString($payload['synonyms'] ?? $brand->getSynonyms()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $brand->isActive()))
            ->setSorting($this->sanitizeNullableInt($payload['sorting'] ?? $brand->getSorting()) ?? 0)
            ->setImage($this->sanitizeNullableString($payload['image'] ?? $brand->getImage()));

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $brand->getId(),
            'name' => $brand->getName(),
            'slug' => $brand->getSlug(),
            'active' => $brand->isActive(),
            'sorting' => $brand->getSorting(),
        ]]);
    }

    #[Route('/admin/types/save', name: 'api_vehicle_database_admin_types_save', methods: ['POST'])]
    public function saveType(Request $request): JsonResponse
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

        // Typen sind pool-uebergreifend, bearbeitbar nur wenn sie im Pool verknuepft sind
        $type = $this->entityManager->getRepository(VehicleType::class)->find((int) ($payload['id'] ?? 0));
        if (!$type instanceof VehicleType || !$pool->getVehicleTypes()->contains($type)) {
            return $this->errorResponse('Fahrzeugtyp nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $type
            ->setName($this->sanitizeString($payload['name'] ?? $type->getName(), true))
            ->setDescription($this->sanitizeNullableString($payload['description'] ?? $type->getDescription()))
            ->setSlug($this->sanitizeNullableString($payload['slug'] ?? $type->getSlug()))
            ->setActive($this->sanitizeBool($payload['active'] ?? $type->isActive()))
            ->setSorting($this->sanitizeNullableInt($payload['sorting'] ?? $type->getSorting()) ?? 0)
            ->setImage($this->sanitizeNullableString($payload['image'] ?? $type->getImage()));

        $this->entityManager->flush();

        return new JsonResponse(['data' => [
            'id' => $type->getId(),
            'name' => $type->getName(),
            'slug' => $type->getSlug(),
            'active' => $type->isActive(),
            'sorting' => $type->getSorting(),
        ]]);
    }

    #[Route('/admin/brands/reorder', name: 'api_vehicle_database_admin_brands_reorder', methods: ['POST'])]
    public function reorderBrands(Request $request): JsonResponse
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

        $items = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        $updated = [];
        foreach ($items as $item) {
            $brand = $this->findInPool(\App\Entity\VehicleBrand::class, (int) ($item['id'] ?? 0), $pool);
            if (!$brand instanceof \App\Entity\VehicleBrand) {
                return $this->errorResponse('Marke nicht gefunden.', Response::HTTP_NOT_FOUND);
            }

            $brand->setSorting($this->sanitizeNullableInt($item['sorting'] ?? 0) ?? 0);
            $updated[] = $brand->getId();
        }

        $this->entityManager->flush();

        return new JsonResponse(['data' => ['updated' => $updated]]);
    }

    #[Route('/admin/master/{resource}', name: 'api_vehicle_database_admin_master_list', requirements: ['resource' => 'tool-manufacturers|tool-methodes|gearbox-dtc-codes|gearbox-stage-infos|gearbox-stage-options'], methods: ['GET'])]
    public function masterList(string $resource, Request $request): JsonResponse
    {
        [$dbToken] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        return new JsonResponse(['data' => $this->vehicleMasterDataService->list($resource)]);
    }

    #[Route('/admin/master/{resource}/save', name: 'api_vehicle_database_admin_master_save', requirements: ['resource' => 'tool-manufacturers|tool-methodes|gearbox-dtc-codes|gearbox-stage-infos|gearbox-stage-options'], methods: ['POST'])]
    public function masterSave(string $resource, Request $request): JsonResponse
    {
        [$dbToken, , , $featureFlags] = $this->resolveEditableScope($request);
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

        try {
            return new JsonResponse(['data' => $this->vehicleMasterDataService->save($resource, $payload)]);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\OutOfBoundsException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\LogicException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_CONFLICT);
        }
    }

    #[Route('/admin/master/{resource}/{id}', name: 'api_vehicle_database_admin_master_delete', requirements: ['resource' => 'tool-manufacturers|tool-methodes|gearbox-dtc-codes|gearbox-stage-infos|gearbox-stage-options', 'id' => '\d+'], methods: ['DELETE'])]
    public function masterDelete(string $resource, int $id, Request $request): JsonResponse
    {
        [$dbToken, , , $featureFlags] = $this->resolveEditableScope($request);
        if ($dbToken instanceof JsonResponse) {
            return $dbToken;
        }

        if (!$featureFlags['allowVehicleEditing']) {
            return $this->errorResponse('Bearbeiten ist fuer diesen Datenkontext nicht freigegeben.', Response::HTTP_FORBIDDEN);
        }

        try {
            $this->vehicleMasterDataService->delete($resource, $id);
        } catch (\OutOfBoundsException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\LogicException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_CONFLICT);
        }

        return new JsonResponse(['data' => ['id' => $id, 'deleted' => true]]);
    }

    /**
     * @return array{class: class-string, parentClass: class-string, label: string, parentLabel: string, setParent: string, getParent: string, getChildren: string, childSetParent: string}
     */
    private function hierarchyConfig(string $entity): array
    {
        return match ($entity) {
            'series' => [
                'class' => VehicleSeries::class, 'parentClass' => \App\Entity\VehicleBrand::class,
                'label' => 'Baureihe', 'parentLabel' => 'Marke',
                'setParent' => 'setVehicleBrand', 'getParent' => 'getVehicleBrand',
                'getChildren' => 'getModels', 'childSetParent' => 'setVehicleSeries',
            ],
            'models' => [
                'class' => VehicleModel::class, 'parentClass' => VehicleSeries::class,
                'label' => 'Modell', 'parentLabel' => 'Baureihe',
                'setParent' => 'setVehicleSeries', 'getParent' => 'getVehicleSeries',
                'getChildren' => 'getEngines', 'childSetParent' => 'setVehicleModel',
            ],
            default => [
                'class' => VehicleEngine::class, 'parentClass' => VehicleModel::class,
                'label' => 'Motor', 'parentLabel' => 'Modell',
                'setParent' => 'setVehicleModel', 'getParent' => 'getVehicleModel',
                'getChildren' => 'getStages', 'childSetParent' => 'setVehicleEngine',
            ],
        };
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T|null
     */
    private function findInPool(string $class, int $id, VehicleDataPool $pool): ?object
    {
        if ($id <= 0) {
            return null;
        }

        $entity = $this->entityManager->getRepository($class)->find($id);
        if (!is_object($entity) || !method_exists($entity, 'getVehicleDataPool') || $entity->getVehicleDataPool()?->getId() !== $pool->getId()) {
            return null;
        }

        return $entity;
    }

    /**
     * @return array{0: ApiToken|JsonResponse, 1: VehicleDataPool|null, 2: \App\Entity\VehicleDataContext|null, 3: array<string, bool>}
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

        // Ohne Pool gibt es keinen Schreib-/Lesebereich. Ohne diese Pruefung schlagen die Pool-Vergleiche mit 500 fehl.
        if (!$pool instanceof VehicleDataPool) {
            return [$this->errorResponse('Kein Datenpool fuer diesen API-Token verfuegbar.', Response::HTTP_FORBIDDEN), null, null, [
                'showPrices' => false,
                'showHints' => false,
                'showGearbox' => false,
                'showDyno' => false,
                'showEcoStage' => false,
                'showOptionCategories' => false,
                'allowVehicleEditing' => false,
            ]];
        }

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

    private function syncHintImages(EinbauHinweis $hint, array $imageIds): void
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

    /**
     * Synonyme der Marke inkl. der Synonyme des verknuepften Brand-Masters.
     *
     * @return array<int, string>
     */
    private function resolveBrandSynonyms(\App\Entity\VehicleBrand $brand): array
    {
        return array_values(array_unique(array_merge(
            $brand->getSynonymList(),
            $brand->getMasterBrand()?->getSynonymList() ?? []
        )));
    }

    /** @return array{id: int, name: string, logo: ?string}|null */
    private function findPerformanceBrand(mixed $id): ?PerformanceBrand
    {
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }

        $brand = $this->entityManager->getRepository(PerformanceBrand::class)->find($id);

        return $brand instanceof PerformanceBrand ? $brand : null;
    }

    private function mapPerformanceBrand(?PerformanceBrand $performanceBrand): ?array
    {
        if (!$performanceBrand instanceof PerformanceBrand || null === $performanceBrand->getId()) {
            return null;
        }

        return [
            'id' => $performanceBrand->getId(),
            'name' => $performanceBrand->getName(),
            'logo' => $this->vehiclePayloadMapper->normalizePublicPath($performanceBrand->getResolvedImagePath()),
        ];
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
}
