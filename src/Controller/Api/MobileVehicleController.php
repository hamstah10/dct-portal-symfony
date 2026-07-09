<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleEngine;
use App\Entity\VehicleType;
use App\Repository\VehicleBrandRepository;
use App\Repository\VehicleDataPoolRepository;
use App\Repository\VehicleEngineRepository;
use App\Repository\VehicleModelRepository;
use App\Repository\VehicleSeriesRepository;
use App\Service\Api\MobileApiAuthService;
use App\Service\VehicleDataContextResolver;
use App\Service\VehiclePayloadMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/mobile/vehicle')]
final class MobileVehicleController extends AbstractController
{
    use ApiControllerTrait;
    use MobileAuthTrait;

    private const DEFAULT_MOBILE_POOL_KEY = 'complete';

    public function __construct(
        private readonly VehicleBrandRepository $vehicleBrandRepository,
        private readonly VehicleSeriesRepository $vehicleSeriesRepository,
        private readonly VehicleModelRepository $vehicleModelRepository,
        private readonly VehicleEngineRepository $vehicleEngineRepository,
        private readonly MobileApiAuthService $authService,
        private readonly VehicleDataContextResolver $vehicleDataContextResolver,
        private readonly VehicleDataPoolRepository $vehicleDataPoolRepository,
        private readonly VehiclePayloadMapper $vehiclePayloadMapper,
    ) {
    }

    #[Route('/types', name: 'api_mobile_vehicle_types', methods: ['GET'])]
    public function types(Request $request): JsonResponse
    {
        if (($error = $this->requireUserOrError($request)) instanceof JsonResponse) {
            return $error;
        }

        $pool = $this->resolvePool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

        $types = $pool->getVehicleTypes()
            ->filter(static fn (mixed $type): bool => $type instanceof VehicleType && $type->isActive())
            ->toArray();
        usort($types, static fn (VehicleType $a, VehicleType $b): int => [$a->getSorting(), $a->getName()] <=> [$b->getSorting(), $b->getName()]);

        return new JsonResponse(array_map(fn (VehicleType $type): array => [
            'id' => $type->getId(),
            'name' => $type->getName(),
            'icon' => $type->getIcon(),
            'image' => $type->getImage(),
        ], $types));
    }

    #[Route('/brands/{typeId<\d+>}', name: 'api_mobile_vehicle_brands', methods: ['GET'])]
    public function brands(int $typeId, Request $request): JsonResponse
    {
        if (($error = $this->requireUserOrError($request)) instanceof JsonResponse) {
            return $error;
        }

        $pool = $this->resolvePool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

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

        return new JsonResponse(array_map(fn ($brand): array => [
            'id' => $brand->getId(),
            'name' => $brand->getName(),
            'logo' => $brand->getImage(),
        ], $brands));
    }

    #[Route('/series/{brandId<\d+>}', name: 'api_mobile_vehicle_series', methods: ['GET'])]
    public function series(int $brandId, Request $request): JsonResponse
    {
        if (($error = $this->requireUserOrError($request)) instanceof JsonResponse) {
            return $error;
        }

        $pool = $this->resolvePool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

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

        return new JsonResponse(array_map(fn ($item): array => [
            'id' => $item->getId(),
            'name' => $item->getName(),
        ], $series));
    }

    #[Route('/models/{seriesId<\d+>}', name: 'api_mobile_vehicle_models', methods: ['GET'])]
    public function models(int $seriesId, Request $request): JsonResponse
    {
        if (($error = $this->requireUserOrError($request)) instanceof JsonResponse) {
            return $error;
        }

        $pool = $this->resolvePool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

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

        return new JsonResponse(array_map(fn ($item): array => [
            'id' => $item->getId(),
            'name' => $item->getName(),
        ], $models));
    }

    #[Route('/engines/{modelId<\d+>}', name: 'api_mobile_vehicle_engines', methods: ['GET'])]
    public function engines(int $modelId, Request $request): JsonResponse
    {
        if (($error = $this->requireUserOrError($request)) instanceof JsonResponse) {
            return $error;
        }

        $pool = $this->resolvePool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

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

        return new JsonResponse(array_map(fn (VehicleEngine $engine): array => [
            'id' => $engine->getId(),
            'name' => $engine->getName(),
            'horsepower' => $engine->getHorsepower(),
            'fuel' => $engine->getFuel(),
        ], $engines));
    }

    #[Route('/engine-details/{engineId<\d+>}', name: 'api_mobile_vehicle_engine_details', methods: ['GET'])]
    public function engineDetails(int $engineId, Request $request): JsonResponse
    {
        if (($error = $this->requireUserOrError($request)) instanceof JsonResponse) {
            return $error;
        }

        $pool = $this->resolvePool($request);
        if (!$pool instanceof VehicleDataPool) {
            return $this->errorResponse('Kein gueltiger Datenbestand gefunden.', Response::HTTP_NOT_FOUND);
        }

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

    private function requireUserOrError(Request $request): ?JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }

    private function resolvePool(Request $request): ?VehicleDataPool
    {
        $hasExplicitSelection = '' !== trim((string) (
            $request->headers->get(VehicleDataContextResolver::HEADER_POOL_KEY)
            ?? $request->query->get('poolKey', '')
        )) || '' !== trim((string) (
            $request->headers->get(VehicleDataContextResolver::HEADER_CONTEXT_KEY)
            ?? $request->query->get('contextKey', '')
        ));

        if (!$hasExplicitSelection) {
            return $this->vehicleDataPoolRepository->findActiveByPoolKey(self::DEFAULT_MOBILE_POOL_KEY);
        }

        return $this->vehicleDataContextResolver->resolvePoolForRequest($request);
    }
}
