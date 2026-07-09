<?php

namespace App\Controller\Admin;

use App\Entity\DynoSprint;
use App\Repository\DynoCustomerRepository;
use App\Repository\DynoDtcRepository;
use App\Repository\DynoSprintRepository;
use App\Repository\DynoTripRepository;
use App\Repository\DynoVehicleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/dyno')]
final class DynoStatsController extends AbstractController
{
    public function __construct(
        private readonly DynoTripRepository $tripRepository,
        private readonly DynoVehicleRepository $vehicleRepository,
        private readonly DynoCustomerRepository $customerRepository,
        private readonly DynoSprintRepository $sprintRepository,
        private readonly DynoDtcRepository $dtcRepository,
    ) {
    }

    #[Route('/stats', name: 'admin_dyno_stats', methods: ['GET'])]
    public function index(Request $request, bool $embed = false): Response
    {
        $embed = $embed || $request->query->getBoolean('embed');
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

        $overview = [
            'totalTrips' => (int) ($tripStats['totalTrips'] ?? 0),
            'totalKm' => (float) ($tripStats['totalKm'] ?? 0),
            'totalHours' => round($totalSeconds / 3600, 2),
            'maxSpeedKmh' => (float) ($tripStats['maxSpeed'] ?? 0),
            'maxPs' => (int) ($tripStats['maxPs'] ?? 0),
            'maxNm' => (int) ($tripStats['maxNm'] ?? 0),
            'totalVehicles' => $this->vehicleRepository->count([]),
            'totalCustomers' => $this->customerRepository->count([]),
            'totalSprints' => $this->sprintRepository->count([]),
        ];

        $perVehicleRows = $this->tripRepository->createQueryBuilder('t')
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

        $perVehicle = array_map(static fn (array $row): array => [
            'vehicle_id' => $row['vehicleId'] ?? null,
            'vehicle_name' => $row['vehicleName'] ?? $row['vehicleKey'] ?? 'Unbekannt',
            'total_trips' => (int) $row['totalTrips'],
            'total_km' => (float) $row['totalKm'],
            'max_speed_kmh' => (float) $row['maxSpeed'],
            'max_ps' => (int) $row['maxPs'],
            'max_nm' => (int) $row['maxNm'],
        ], $perVehicleRows);

        $bestlist = $this->buildSprintBestlist($this->sprintRepository->findBy([], ['timeSeconds' => 'ASC']));
        $dtcs = $this->dtcRepository->findBy([], ['createdAt' => 'DESC'], 20);

        $template = $embed ? 'admin/statistics/_dyno.html.twig' : 'admin/dyno/stats.html.twig';

        return $this->render($template, [
            'overview' => $overview,
            'perVehicle' => $perVehicle,
            'bestlist' => $bestlist,
            'dtcs' => $dtcs,
            'embedded' => $embed,
        ]);
    }

    /**
     * @param array<int, DynoSprint> $sprints
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildSprintBestlist(array $sprints): array
    {
        $best = [];

        foreach ($sprints as $sprint) {
            $vehicleId = $sprint->getVehicle()?->getId() ?? '';
            if ('' === $vehicleId) {
                continue;
            }

            $key = $vehicleId . '::' . $sprint->getSprintType();
            if (isset($best[$key])) {
                continue;
            }

            $vehicleName = $sprint->getVehicleName()
                ?? $sprint->getVehicle()?->getName()
                ?? $vehicleId;

            $best[$key] = [
                'vehicle_id' => $vehicleId,
                'vehicle_name' => $vehicleName,
                'sprint_type' => $sprint->getSprintType(),
                'time_seconds' => (float) $sprint->getTimeSeconds(),
                'distance_m' => $this->resolveSprintDistanceMeters($sprint),
                'max_ps' => $sprint->getMaxPs(),
                'max_nm' => $sprint->getMaxNm(),
                'max_rpm' => $sprint->getMaxRpm(),
                'drive_mode' => $sprint->getDriveMode(),
                'created_at' => $sprint->getCreatedAt(),
            ];
        }

        return array_values($best);
    }

    private function resolveSprintDistanceMeters(DynoSprint $sprint): float
    {
        $tripId = $sprint->getTripId();
        if (null !== $tripId && '' !== trim($tripId)) {
            $trip = $this->tripRepository->find($tripId);
            if (null !== $trip) {
                return (float) $trip->getDistanceKm() * 1000;
            }
        }

        return (float) $sprint->getDistanceMeters();
    }
}
