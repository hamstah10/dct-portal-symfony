<?php

namespace App\Controller\Api;

use App\Entity\ApiToken;
use App\Entity\PerformanceBrand;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleEngine;
use App\Entity\VehicleType;
use App\Repository\ApiTokenRepository;
use App\Repository\VehicleBrandRepository;
use App\Repository\VehicleEngineRepository;
use App\Repository\VehicleModelRepository;
use App\Repository\VehicleSeriesRepository;
use App\Repository\VehicleTypeRepository;
use App\Service\Analytics\VehicleUsageTracker;
use App\Service\VehicleDataContextResolver;
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
            'data' => $this->vehiclePayloadMapper->buildEngineDetailsData($engine, $pool, $requestedVariant),
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

    /** @return array{id: int, name: string, logo: ?string}|null */
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
