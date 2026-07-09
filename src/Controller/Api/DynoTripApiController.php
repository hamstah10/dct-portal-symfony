<?php

namespace App\Controller\Api;

use App\Entity\DynoTrip;
use App\Entity\DynoVehicle;
use App\Repository\ApiTokenRepository;
use App\Repository\DynoTripRepository;
use App\Repository\DynoVehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class DynoTripApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly DynoTripRepository $tripRepository,
        private readonly DynoVehicleRepository $vehicleRepository,
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%api_token%')] private readonly string $apiToken,
    ) {
    }

    #[Route('/trips', name: 'api_dyno_trips_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        return $this->buildTripListResponse();
    }

    #[Route('/crm/trips', name: 'api_dyno_crm_trips_list', methods: ['GET'])]
    public function crmList(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        return $this->buildTripListResponse();
    }

    #[Route('/trips', name: 'api_dyno_trips_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $payload = $this->readJsonPayload($request);
            $trip = new DynoTrip();
            $this->applyTripPayload($trip, $payload);
            $this->entityManager->persist($trip);
            $this->entityManager->flush();
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->normalizeTrip($trip, false), Response::HTTP_CREATED);
    }

    #[Route('/trips/{id}', name: 'api_dyno_trips_delete', methods: ['DELETE'])]
    public function delete(Request $request, string $id): Response
    {
        return $this->deleteTrip($id);
    }

    #[Route('/crm/trips/{id}', name: 'api_dyno_crm_trips_delete', methods: ['DELETE'])]
    public function crmDelete(Request $request, string $id): Response
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        return $this->deleteTrip($id);
    }

    #[Route('/trips/export/csv', name: 'api_dyno_trips_export', methods: ['GET'])]
    public function exportCsv(Request $request): Response
    {
        $trips = $this->tripRepository->findBy([], ['startTime' => 'DESC']);

        $handle = fopen('php://temp', 'rb+');
        fputcsv($handle, [
            'id',
            'start_time',
            'end_time',
            'duration_seconds',
            'distance_km',
            'max_speed_kmh',
            'avg_speed_kmh',
            'max_rpm',
            'max_nm',
            'max_ps',
            'drive_mode',
            'vehicle_id',
            'vehicle_name',
            'created_at',
        ]);

        foreach ($trips as $trip) {
            fputcsv($handle, [
                $trip->getId(),
                $trip->getStartTime()->format(DATE_ATOM),
                $trip->getEndTime()->format(DATE_ATOM),
                $trip->getDurationSeconds(),
                $trip->getDistanceKm(),
                $trip->getMaxSpeedKmh(),
                $trip->getAvgSpeedKmh(),
                $trip->getMaxRpm(),
                $trip->getMaxNm(),
                $trip->getMaxPs(),
                $trip->getDriveMode(),
                $trip->getVehicle()?->getId(),
                $trip->getVehicleName(),
                $trip->getCreatedAt()->format(DATE_ATOM),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="dyno-trips.csv"');

        return $response;
    }

    private function buildTripListResponse(): JsonResponse
    {
        $trips = $this->tripRepository->findBy([], ['startTime' => 'DESC']);

        return new JsonResponse([
            'data' => array_map(fn (DynoTrip $trip): array => $this->normalizeTrip($trip, false), $trips),
        ]);
    }

    private function deleteTrip(string $id): Response
    {
        $trip = $this->tripRepository->find($id);
        if (!$trip instanceof DynoTrip) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $this->entityManager->remove($trip);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/crm/trips/{id}/datapoints', name: 'api_dyno_trips_datapoints', methods: ['GET'])]
    public function datapoints(Request $request, string $id): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $trip = $this->tripRepository->find($id);
        if (!$trip instanceof DynoTrip) {
            return $this->errorResponse('Fahrt nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $trip->getId(),
            'data_points' => $trip->getDataPoints() ?? [],
        ]);
    }

    private function applyTripPayload(DynoTrip $trip, array $payload): void
    {
        $trip->setStartTime($this->parseDateTime($payload['start_time'] ?? null, $trip->getStartTime()));
        $trip->setEndTime($this->parseDateTime($payload['end_time'] ?? null, $trip->getEndTime()));

        $duration = isset($payload['duration_seconds']) ? (int) $payload['duration_seconds'] : null;
        if (null === $duration) {
            $duration = max(0, $trip->getEndTime()->getTimestamp() - $trip->getStartTime()->getTimestamp());
        }
        $trip->setDurationSeconds($duration);

        $trip->setDistanceKm($this->normalizeDecimal($payload['distance_km'] ?? $trip->getDistanceKm()));
        $trip->setMaxSpeedKmh($this->normalizeDecimal($payload['max_speed_kmh'] ?? $trip->getMaxSpeedKmh()));
        $trip->setAvgSpeedKmh($this->normalizeDecimal($payload['avg_speed_kmh'] ?? $trip->getAvgSpeedKmh()));
        $trip->setMaxRpm(isset($payload['max_rpm']) ? (int) $payload['max_rpm'] : $trip->getMaxRpm());
        $trip->setMaxNm(isset($payload['max_nm']) ? (int) $payload['max_nm'] : $trip->getMaxNm());
        $trip->setMaxPs(isset($payload['max_ps']) ? (int) $payload['max_ps'] : $trip->getMaxPs());
        $trip->setDriveMode(isset($payload['drive_mode']) ? (string) $payload['drive_mode'] : $trip->getDriveMode());

        if (array_key_exists('data_points', $payload)) {
            if (!is_array($payload['data_points'])) {
                throw new \InvalidArgumentException('data_points muss ein Array sein.');
            }
            $trip->setDataPoints($payload['data_points']);
        }

        if (array_key_exists('vehicle_id', $payload)) {
            $vehicleId = trim((string) $payload['vehicle_id']);
            if ('' === $vehicleId) {
                $trip->setVehicle(null);
            } else {
                $vehicle = $this->vehicleRepository->find($vehicleId);
                if (!$vehicle instanceof DynoVehicle) {
                    throw new \InvalidArgumentException('Fahrzeug nicht gefunden.');
                }
                $trip->setVehicle($vehicle);
                if (null === $trip->getVehicleName() || '' === trim((string) $trip->getVehicleName())) {
                    $trip->setVehicleName($vehicle->getName());
                }
            }
        }

        if (array_key_exists('vehicle_name', $payload)) {
            $trip->setVehicleName('' === trim((string) $payload['vehicle_name']) ? null : (string) $payload['vehicle_name']);
        }

        if (array_key_exists('created_at', $payload)) {
            $trip->setCreatedAt($this->parseDateTime($payload['created_at'], $trip->getCreatedAt()));
        }
    }

    private function normalizeTrip(DynoTrip $trip, bool $withDataPoints): array
    {
        $payload = [
            'id' => $trip->getId(),
            'start_time' => $trip->getStartTime()->format(DATE_ATOM),
            'end_time' => $trip->getEndTime()->format(DATE_ATOM),
            'duration_seconds' => $trip->getDurationSeconds(),
            'distance_km' => (float) $trip->getDistanceKm(),
            'max_speed_kmh' => (float) $trip->getMaxSpeedKmh(),
            'avg_speed_kmh' => (float) $trip->getAvgSpeedKmh(),
            'max_rpm' => $trip->getMaxRpm(),
            'max_nm' => $trip->getMaxNm(),
            'max_ps' => $trip->getMaxPs(),
            'drive_mode' => $trip->getDriveMode(),
            'vehicle_id' => $trip->getVehicle()?->getId(),
            'vehicle_name' => $trip->getVehicleName(),
            'created_at' => $trip->getCreatedAt()->format(DATE_ATOM),
        ];

        if ($withDataPoints) {
            $payload['data_points'] = $trip->getDataPoints() ?? [];
        }

        return $payload;
    }

    private function parseDateTime(mixed $value, \DateTimeImmutable $fallback): \DateTimeImmutable
    {
        if (null === $value || '' === trim((string) $value)) {
            return $fallback;
        }

        try {
            return new \DateTimeImmutable((string) $value);
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function normalizeDecimal(mixed $value): string
    {
        if (null === $value || '' === trim((string) $value)) {
            return '0.00';
        }

        return number_format((float) $value, 2, '.', '');
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
