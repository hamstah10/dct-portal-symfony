<?php

namespace App\Controller\Api;

use App\Repository\ApiTokenRepository;
use App\Repository\DynoCustomerRepository;
use App\Repository\DynoSprintRepository;
use App\Repository\DynoTripRepository;
use App\Repository\DynoVehicleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/crm/analytics')]
final class DynoAnalyticsApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly DynoTripRepository $tripRepository,
        private readonly DynoVehicleRepository $vehicleRepository,
        private readonly DynoCustomerRepository $customerRepository,
        private readonly DynoSprintRepository $sprintRepository,
        private readonly ApiTokenRepository $apiTokenRepository,
        #[Autowire('%api_token%')] private readonly string $apiToken,
    ) {
    }

    #[Route('/overview', name: 'api_dyno_analytics_overview', methods: ['GET'])]
    public function overview(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $tripStats = $this->tripRepository->createQueryBuilder('t')
            ->select(
                'COUNT(t.id) AS totalTrips',
                'COALESCE(SUM(t.distanceKm), 0) AS totalKm',
                'COALESCE(SUM(t.durationSeconds), 0) AS totalSeconds',
                'COALESCE(MAX(t.maxSpeedKmh), 0) AS maxSpeed',
                'COALESCE(MAX(t.maxPs), 0) AS maxPs',
                'COALESCE(MAX(t.maxNm), 0) AS maxNm'
            )
            ->getQuery()
            ->getSingleResult();

        $totalSeconds = (float) ($tripStats['totalSeconds'] ?? 0);
        $totalKm = (float) ($tripStats['totalKm'] ?? 0);

        return new JsonResponse([
            'total_trips' => (int) ($tripStats['totalTrips'] ?? 0),
            'total_km' => $totalKm,
            'total_hours' => round($totalSeconds / 3600, 2),
            'max_speed_kmh' => (float) ($tripStats['maxSpeed'] ?? 0),
            'max_ps' => (int) ($tripStats['maxPs'] ?? 0),
            'max_nm' => (int) ($tripStats['maxNm'] ?? 0),
            'total_vehicles' => $this->vehicleRepository->count([]),
            'total_customers' => $this->customerRepository->count([]),
            'total_sprints' => $this->sprintRepository->count([]),
        ]);
    }

    #[Route('/per-vehicle', name: 'api_dyno_analytics_per_vehicle', methods: ['GET'])]
    public function perVehicle(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $rows = $this->tripRepository->createQueryBuilder('t')
            ->leftJoin('t.vehicle', 'v')
            ->select(
                'COALESCE(IDENTITY(t.vehicle), t.vehicleName) AS vehicleKey',
                'MAX(IDENTITY(t.vehicle)) AS vehicleId',
                'MAX(COALESCE(t.vehicleName, v.name)) AS vehicleName',
                'COUNT(t.id) AS totalTrips',
                'COALESCE(SUM(t.distanceKm), 0) AS totalKm',
                'COALESCE(MAX(t.maxSpeedKmh), 0) AS maxSpeed',
                'COALESCE(MAX(t.maxPs), 0) AS maxPs',
                'COALESCE(MAX(t.maxNm), 0) AS maxNm'
            )
            ->where('t.vehicle IS NOT NULL OR t.vehicleName IS NOT NULL')
            ->groupBy('vehicleKey')
            ->orderBy('totalTrips', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $data = array_map(static fn (array $row): array => [
            'vehicle_id' => $row['vehicleId'] ?? null,
            'vehicle_name' => $row['vehicleName'] ?? $row['vehicleKey'] ?? 'Unbekannt',
            'total_trips' => (int) $row['totalTrips'],
            'total_km' => (float) $row['totalKm'],
            'max_speed_kmh' => (float) $row['maxSpeed'],
            'max_ps' => (int) $row['maxPs'],
            'max_nm' => (int) $row['maxNm'],
        ], $rows);

        return new JsonResponse(['data' => $data]);
    }

    #[Route('/per-mode', name: 'api_dyno_analytics_per_mode', methods: ['GET'])]
    public function perMode(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $rows = $this->tripRepository->createQueryBuilder('t')
            ->select(
                't.driveMode AS driveMode',
                'COUNT(t.id) AS totalTrips',
                'COALESCE(AVG(t.avgSpeedKmh), 0) AS avgSpeed',
                'COALESCE(MAX(t.maxSpeedKmh), 0) AS maxSpeed',
                'COALESCE(MAX(t.maxPs), 0) AS maxPs',
                'COALESCE(MAX(t.maxNm), 0) AS maxNm'
            )
            ->where('t.driveMode IS NOT NULL')
            ->groupBy('t.driveMode')
            ->orderBy('totalTrips', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $data = array_map(static fn (array $row): array => [
            'drive_mode' => $row['driveMode'],
            'total_trips' => (int) $row['totalTrips'],
            'avg_speed_kmh' => (float) $row['avgSpeed'],
            'max_speed_kmh' => (float) $row['maxSpeed'],
            'max_ps' => (int) $row['maxPs'],
            'max_nm' => (int) $row['maxNm'],
        ], $rows);

        return new JsonResponse(['data' => $data]);
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
