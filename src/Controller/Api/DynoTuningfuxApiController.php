<?php

namespace App\Controller\Api;

use App\Entity\VehicleBrand;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use App\Entity\VehicleSeries;
use App\Entity\VehicleStage;
use App\Entity\VehicleType;
use App\Repository\ApiTokenRepository;
use App\Repository\VehicleBrandRepository;
use App\Repository\VehicleEngineRepository;
use App\Repository\VehicleModelRepository;
use App\Repository\VehicleSeriesRepository;
use App\Repository\VehicleStageRepository;
use App\Repository\VehicleTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tuningfux')]
final class DynoTuningfuxApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly VehicleTypeRepository $vehicleTypeRepository,
        private readonly VehicleBrandRepository $vehicleBrandRepository,
        private readonly VehicleSeriesRepository $vehicleSeriesRepository,
        private readonly VehicleModelRepository $vehicleModelRepository,
        private readonly VehicleEngineRepository $vehicleEngineRepository,
        private readonly VehicleStageRepository $vehicleStageRepository,
        #[Autowire('%api_token%')] private readonly string $apiToken,
    ) {
    }

    #[Route('/types', name: 'api_tuningfux_types', methods: ['GET'])]
    public function types(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $types = $this->vehicleTypeRepository->findBy(['active' => true], ['sorting' => 'ASC', 'name' => 'ASC']);

        return new JsonResponse([
            'data' => array_map(static fn (VehicleType $type): array => [
                'id' => $type->getId(),
                'name' => $type->getName(),
                'image' => $type->getImage(),
                'icon' => $type->getImage(),
            ], $types),
        ]);
    }

    #[Route('/brands/{typeId}', name: 'api_tuningfux_brands', requirements: ['typeId' => '\d+'], methods: ['GET'])]
    public function brands(int $typeId, Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $brands = $this->vehicleBrandRepository->createQueryBuilder('b')
            ->leftJoin('b.vehicleType', 't')
            ->where('t.id = :typeId')
            ->andWhere('b.active = :active')
            ->setParameter('typeId', $typeId)
            ->setParameter('active', true)
            ->orderBy('b.sorting', 'ASC')
            ->addOrderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'data' => array_map(static fn (VehicleBrand $brand): array => [
                'id' => $brand->getId(),
                'name' => $brand->getName(),
                'image' => $brand->getImage(),
                'icon' => $brand->getImage(),
                'showInTopMenu' => $brand->isShowInTopMenu(),
            ], $brands),
        ]);
    }

    #[Route('/series/{brandId}', name: 'api_tuningfux_series', requirements: ['brandId' => '\d+'], methods: ['GET'])]
    public function series(int $brandId, Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $series = $this->vehicleSeriesRepository->createQueryBuilder('s')
            ->leftJoin('s.vehicleBrand', 'b')
            ->where('b.id = :brandId')
            ->andWhere('s.active = :active')
            ->setParameter('brandId', $brandId)
            ->setParameter('active', true)
            ->orderBy('s.sorting', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'data' => array_map(static fn (VehicleSeries $row): array => [
                'id' => $row->getId(),
                'name' => $row->getName(),
            ], $series),
        ]);
    }

    #[Route('/models/{seriesId}', name: 'api_tuningfux_models', requirements: ['seriesId' => '\d+'], methods: ['GET'])]
    public function models(int $seriesId, Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $models = $this->vehicleModelRepository->createQueryBuilder('m')
            ->leftJoin('m.vehicleSeries', 's')
            ->where('s.id = :seriesId')
            ->andWhere('m.active = :active')
            ->setParameter('seriesId', $seriesId)
            ->setParameter('active', true)
            ->orderBy('m.sorting', 'ASC')
            ->addOrderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'data' => array_map(static fn (VehicleModel $row): array => [
                'id' => $row->getId(),
                'name' => $row->getName(),
            ], $models),
        ]);
    }

    #[Route('/engines/{modelId}', name: 'api_tuningfux_engines', requirements: ['modelId' => '\d+'], methods: ['GET'])]
    public function engines(int $modelId, Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $engines = $this->vehicleEngineRepository->createQueryBuilder('e')
            ->leftJoin('e.vehicleModel', 'm')
            ->where('m.id = :modelId')
            ->andWhere('e.active = :active')
            ->setParameter('modelId', $modelId)
            ->setParameter('active', true)
            ->orderBy('e.sorting', 'ASC')
            ->addOrderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'data' => array_map(static fn (VehicleEngine $engine): array => [
                'id' => $engine->getId(),
                'name' => $engine->getName(),
                'horsepower' => $engine->getHorsepower(),
                'fuel' => $engine->getFuel(),
            ], $engines),
        ]);
    }

    #[Route('/engine-details/{engineId}', name: 'api_tuningfux_engine_details', requirements: ['engineId' => '\d+'], methods: ['GET'])]
    public function engineDetails(int $engineId, Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $engine = $this->vehicleEngineRepository->find($engineId);
        if (!$engine instanceof VehicleEngine || !$engine->isActive()) {
            return $this->errorResponse('Motor nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $stages = $this->vehicleStageRepository->createQueryBuilder('s')
            ->where('s.vehicleEngine = :engine')
            ->andWhere('s.active = :active')
            ->setParameter('engine', $engine)
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'data' => [
                'engine' => [
                    'id' => $engine->getId(),
                    'name' => $engine->getName(),
                    'horsepower' => $engine->getHorsepower(),
                    'torque' => $engine->getTorque(),
                    'fuel' => $engine->getFuel(),
                    'displacement' => $engine->getDisplacement(),
                ],
                'stages' => array_map(static fn (VehicleStage $stage): array => [
                    'id' => $stage->getId(),
                    'name' => $stage->getName(),
                    'hp' => $stage->getHp(),
                    'nm' => $stage->getNm(),
                    'price' => $stage->getPrice(),
                ], $stages),
            ],
        ]);
    }

    private function authorizeDynoApi(Request $request): ?JsonResponse
    {
        $provided = $this->getBearerToken($request);
        if (null === $provided) {
            return $this->errorResponse('API-Token fehlt.', Response::HTTP_UNAUTHORIZED);
        }

        $envTokenMatches = '' !== trim($this->apiToken) && hash_equals($this->apiToken, $provided);
        $dbTokenMatches = null !== $this->apiTokenRepository->findOneBy(['apiToken' => $provided]);

        if (!$envTokenMatches && !$dbTokenMatches) {
            return $this->errorResponse('API-Token ist ungueltig.', Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }
}
