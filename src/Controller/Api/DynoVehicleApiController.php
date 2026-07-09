<?php

namespace App\Controller\Api;

use App\Entity\DynoVehicle;
use App\Entity\VehicleEngine;
use App\Entity\VehicleStage;
use App\Repository\DynoVehicleRepository;
use App\Repository\VehicleEngineRepository;
use App\Repository\VehicleStageRepository;
use App\Util\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vehicles')]
final class DynoVehicleApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly DynoVehicleRepository $vehicleRepository,
        private readonly VehicleEngineRepository $engineRepository,
        private readonly VehicleStageRepository $stageRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'api_dyno_vehicles_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $vehicles = $this->vehicleRepository->findBy([], ['name' => 'ASC']);

        return new JsonResponse([
            'data' => array_map(fn (DynoVehicle $vehicle): array => $this->normalizeVehicle($vehicle), $vehicles),
        ]);
    }

    #[Route('', name: 'api_dyno_vehicles_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $payload = $this->readJsonPayload($request);
            $vehicle = new DynoVehicle();
            $this->applyVehiclePayload($vehicle, $payload);
            $this->entityManager->persist($vehicle);
            $this->entityManager->flush();
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->normalizeVehicle($vehicle), Response::HTTP_CREATED);
    }

    #[Route('/{id}/activate', name: 'api_dyno_vehicles_activate', methods: ['PUT'])]
    public function activate(Request $request, string $id): JsonResponse
    {
        $vehicle = $this->vehicleRepository->find($id);
        if (!$vehicle instanceof DynoVehicle) {
            return $this->errorResponse('Fahrzeug nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $vehicle->setIsActive(true);
        $this->entityManager->flush();

        return new JsonResponse($this->normalizeVehicle($vehicle));
    }

    #[Route('/{id}', name: 'api_dyno_vehicles_delete', methods: ['DELETE'])]
    public function delete(Request $request, string $id): Response
    {
        $vehicle = $this->vehicleRepository->find($id);
        if (!$vehicle instanceof DynoVehicle) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $this->entityManager->remove($vehicle);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/import-tuningfux', name: 'api_dyno_vehicles_import', methods: ['POST'])]
    public function importFromTuningfux(Request $request): JsonResponse
    {
        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $engineId = (int) ($payload['engine_id'] ?? 0);
        if ($engineId <= 0) {
            return $this->errorResponse('engine_id fehlt.', Response::HTTP_BAD_REQUEST);
        }

        $engine = $this->engineRepository->find($engineId);
        if (!$engine instanceof VehicleEngine) {
            return $this->errorResponse('Motor nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $stageId = (int) ($payload['stage_id'] ?? 0);
        $stage = $stageId > 0 ? $this->stageRepository->find($stageId) : null;
        if ($stageId > 0 && !$stage instanceof VehicleStage) {
            return $this->errorResponse('Stage nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $vehicle = new DynoVehicle();
        $providedId = trim((string) ($payload['id'] ?? ''));
        if ('' !== $providedId) {
            if (null !== $this->vehicleRepository->find($providedId)) {
                return $this->errorResponse('Fahrzeug-ID existiert bereits.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $vehicle->setId($providedId);
        }

        $model = $engine->getVehicleModel();
        $series = $model?->getVehicleSeries();
        $brand = $series?->getVehicleBrand();

        $brandName = trim((string) ($brand?->getName() ?? ''));
        $modelName = trim((string) ($model?->getName() ?? ''));
        $engineName = trim((string) $engine->getName());

        $fallbackName = trim(sprintf('%s %s %s', $brandName, $modelName, $engineName));
        $vehicleName = trim((string) ($payload['name'] ?? $fallbackName));
        if ('' === $vehicleName) {
            $vehicleName = 'Dyno Vehicle ' . Uuid::v4();
        }

        $vehicle->setName($vehicleName);
        $vehicle->setMake('' !== $brandName ? $brandName : null);
        $vehicle->setModel('' !== $modelName ? $modelName : null);
        $vehicle->setYear(isset($payload['year']) ? (int) $payload['year'] : null);
        $vehicle->setStage(isset($payload['stage']) ? (string) $payload['stage'] : ($stage?->getName()));
        $vehicle->setMaxPs(isset($payload['max_ps']) ? (int) $payload['max_ps'] : ($stage?->getHp() ?? $engine->getHorsepower()));
        $vehicle->setMaxNm(isset($payload['max_nm']) ? (int) $payload['max_nm'] : ($stage?->getNm() ?? $engine->getTorque()));
        $vehicle->setMaxRpm(isset($payload['max_rpm']) ? (int) $payload['max_rpm'] : null);
        $vehicle->setIdleRpm(isset($payload['idle_rpm']) ? (int) $payload['idle_rpm'] : null);
        $vehicle->setEngineCc(isset($payload['engine_cc']) ? (int) $payload['engine_cc'] : null);
        $vehicle->setCylinders(isset($payload['cylinders']) ? (int) $payload['cylinders'] : null);
        $vehicle->setFuelType(isset($payload['fuel_type']) ? (string) $payload['fuel_type'] : $engine->getFuel());

        $this->entityManager->persist($vehicle);
        $this->entityManager->flush();

        return new JsonResponse($this->normalizeVehicle($vehicle), Response::HTTP_CREATED);
    }

    private function applyVehiclePayload(DynoVehicle $vehicle, array $payload): void
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ('' === $name) {
            throw new \InvalidArgumentException('Name fehlt.');
        }

        $providedId = trim((string) ($payload['id'] ?? ''));
        if ('' !== $providedId) {
            if (null !== $this->vehicleRepository->find($providedId)) {
                throw new \InvalidArgumentException('Fahrzeug-ID existiert bereits.');
            }
            $vehicle->setId($providedId);
        }

        $vehicle->setName($name);
        $vehicle->setMake(isset($payload['make']) ? (string) $payload['make'] : null);
        $vehicle->setModel(isset($payload['model']) ? (string) $payload['model'] : null);
        $vehicle->setYear(isset($payload['year']) ? (int) $payload['year'] : null);
        $vehicle->setStage(isset($payload['stage']) ? (string) $payload['stage'] : null);
        $vehicle->setMaxPs(isset($payload['max_ps']) ? (int) $payload['max_ps'] : null);
        $vehicle->setMaxNm(isset($payload['max_nm']) ? (int) $payload['max_nm'] : null);
        $vehicle->setMaxRpm(isset($payload['max_rpm']) ? (int) $payload['max_rpm'] : null);
        $vehicle->setIdleRpm(isset($payload['idle_rpm']) ? (int) $payload['idle_rpm'] : null);
        $vehicle->setEngineCc(isset($payload['engine_cc']) ? (int) $payload['engine_cc'] : null);
        $vehicle->setCylinders(isset($payload['cylinders']) ? (int) $payload['cylinders'] : null);
        $vehicle->setFuelType(isset($payload['fuel_type']) ? (string) $payload['fuel_type'] : null);
        if (array_key_exists('is_active', $payload)) {
            $vehicle->setIsActive((bool) $payload['is_active']);
        }
    }

    private function normalizeVehicle(DynoVehicle $vehicle): array
    {
        return [
            'id' => $vehicle->getId(),
            'name' => $vehicle->getName(),
            'make' => $vehicle->getMake(),
            'model' => $vehicle->getModel(),
            'year' => $vehicle->getYear(),
            'stage' => $vehicle->getStage(),
            'max_ps' => $vehicle->getMaxPs(),
            'max_nm' => $vehicle->getMaxNm(),
            'max_rpm' => $vehicle->getMaxRpm(),
            'idle_rpm' => $vehicle->getIdleRpm(),
            'engine_cc' => $vehicle->getEngineCc(),
            'cylinders' => $vehicle->getCylinders(),
            'fuel_type' => $vehicle->getFuelType(),
            'is_active' => $vehicle->isActive(),
        ];
    }

    // /api/vehicles endpoints are intentionally public (no token required).
}
