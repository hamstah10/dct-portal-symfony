<?php

namespace App\Controller\Api;

use App\Entity\DynoSprint;
use App\Entity\DynoVehicle;
use App\Repository\ApiTokenRepository;
use App\Repository\DynoSprintRepository;
use App\Repository\DynoVehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/crm/sprints')]
final class DynoSprintApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly DynoSprintRepository $sprintRepository,
        private readonly DynoVehicleRepository $vehicleRepository,
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%api_token%')] private readonly string $apiToken,
    ) {
    }

    #[Route('', name: 'api_dyno_sprints_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $criteria = [];
        if ($vehicleId = trim((string) $request->query->get('vehicle_id', ''))) {
            $vehicle = $this->vehicleRepository->find($vehicleId);
            if ($vehicle instanceof DynoVehicle) {
                $criteria['vehicle'] = $vehicle;
            }
        }
        if ($sprintType = trim((string) $request->query->get('sprint_type', ''))) {
            $criteria['sprintType'] = $sprintType;
        }

        $sprints = $this->sprintRepository->findBy($criteria, ['createdAt' => 'DESC']);

        return new JsonResponse([
            'data' => array_map(fn (DynoSprint $sprint): array => $this->normalizeSprint($sprint), $sprints),
        ]);
    }

    #[Route('/bestlist', name: 'api_dyno_sprints_bestlist', methods: ['GET'])]
    public function bestlist(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $sprints = $this->sprintRepository->findBy([], ['timeSeconds' => 'ASC']);

        $best = [];
        foreach ($sprints as $sprint) {
            $vehicleId = $sprint->getVehicle()?->getId() ?? '';
            $key = $vehicleId . '::' . $sprint->getSprintType();
            if ('' === $vehicleId) {
                continue;
            }
            if (!isset($best[$key])) {
                $best[$key] = $sprint;
            }
        }

        return new JsonResponse([
            'data' => array_map(fn (DynoSprint $sprint): array => $this->normalizeSprint($sprint), array_values($best)),
        ]);
    }

    #[Route('', name: 'api_dyno_sprints_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        try {
            $payload = $this->readJsonPayload($request);
            $sprint = new DynoSprint();
            $this->applySprintPayload($sprint, $payload);
            $this->entityManager->persist($sprint);
            $this->entityManager->flush();
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->normalizeSprint($sprint), Response::HTTP_CREATED);
    }

    private function applySprintPayload(DynoSprint $sprint, array $payload): void
    {
        $sprintType = trim((string) ($payload['sprint_type'] ?? ''));
        if ('' === $sprintType) {
            throw new \InvalidArgumentException('sprint_type fehlt.');
        }

        $sprint->setSprintType($sprintType);
        $sprint->setTimeSeconds($this->normalizeDecimal($payload['time_seconds'] ?? $sprint->getTimeSeconds()));
        if (array_key_exists('trip_id', $payload) || array_key_exists('tripId', $payload)) {
            $tripId = trim((string) ($payload['trip_id'] ?? $payload['tripId'] ?? ''));
            $sprint->setTripId('' === $tripId ? null : $tripId);
        }
        $distanceMeters = $this->resolveDistanceMeters($payload);
        if (null !== $distanceMeters) {
            $sprint->setDistanceMeters($this->normalizeDecimal($distanceMeters));
        }
        $sprint->setMaxRpm(isset($payload['max_rpm']) ? (int) $payload['max_rpm'] : $sprint->getMaxRpm());
        $sprint->setMaxPs(isset($payload['max_ps']) ? (int) $payload['max_ps'] : $sprint->getMaxPs());
        $sprint->setMaxNm(isset($payload['max_nm']) ? (int) $payload['max_nm'] : $sprint->getMaxNm());
        $sprint->setDriveMode(isset($payload['drive_mode']) ? (string) $payload['drive_mode'] : $sprint->getDriveMode());

        if (array_key_exists('vehicle_id', $payload)) {
            $vehicleId = trim((string) $payload['vehicle_id']);
            if ('' !== $vehicleId) {
                $vehicle = $this->vehicleRepository->find($vehicleId);
                if (!$vehicle instanceof DynoVehicle) {
                    throw new \InvalidArgumentException('Fahrzeug nicht gefunden.');
                }
                $sprint->setVehicle($vehicle);
                if (null === $sprint->getVehicleName() || '' === trim((string) $sprint->getVehicleName())) {
                    $sprint->setVehicleName($vehicle->getName());
                }
            }
        }

        if (array_key_exists('vehicle_name', $payload)) {
            $sprint->setVehicleName('' === trim((string) $payload['vehicle_name']) ? null : (string) $payload['vehicle_name']);
        }

        if (array_key_exists('created_at', $payload)) {
            $sprint->setCreatedAt($this->parseDateTime($payload['created_at'], $sprint->getCreatedAt()));
        }
    }

    private function normalizeSprint(DynoSprint $sprint): array
    {
        return [
            'id' => $sprint->getId(),
            'vehicle_id' => $sprint->getVehicle()?->getId(),
            'vehicle_name' => $sprint->getVehicleName(),
            'sprint_type' => $sprint->getSprintType(),
            'time_seconds' => (float) $sprint->getTimeSeconds(),
            'trip_id' => $sprint->getTripId(),
            'distance_m' => (float) $sprint->getDistanceMeters(),
            'max_rpm' => $sprint->getMaxRpm(),
            'max_ps' => $sprint->getMaxPs(),
            'max_nm' => $sprint->getMaxNm(),
            'drive_mode' => $sprint->getDriveMode(),
            'created_at' => $sprint->getCreatedAt()->format(DATE_ATOM),
        ];
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

        return number_format($this->parseNumber($value), 2, '.', '');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolveDistanceMeters(array $payload): ?float
    {
        $metersKeys = [
            'distance_m',
            'distance_meters',
            'distanceMeters',
            'wegstrecke_m',
            'wegstreckeMeters',
        ];

        foreach ($metersKeys as $key) {
            if (array_key_exists($key, $payload)) {
                $value = $payload[$key];

                return null === $value || '' === $value ? 0.0 : $this->parseNumber($value);
            }
        }

        $kmKeys = [
            'distance_km',
            'distanceKm',
            'wegstrecke_km',
            'wegstreckeKm',
        ];

        foreach ($kmKeys as $key) {
            if (array_key_exists($key, $payload)) {
                $value = $payload[$key];

                return null === $value || '' === $value ? 0.0 : $this->parseNumber($value) * 1000;
            }
        }

        $rawKeys = [
            'distance',
            'wegstrecke',
        ];

        foreach ($rawKeys as $key) {
            if (array_key_exists($key, $payload)) {
                $value = $payload[$key];

                return null === $value || '' === $value ? 0.0 : $this->parseNumber($value);
            }
        }

        foreach ($payload as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', $key));
            if ('' === $normalized) {
                continue;
            }

            if (str_contains($normalized, 'distance') || str_contains($normalized, 'wegstrecke')) {
                $number = $this->parseNumber($value);

                return str_contains($normalized, 'km') ? $number * 1000 : $number;
            }
        }

        return null;
    }

    private function parseNumber(mixed $value): float
    {
        if (null === $value || '' === trim((string) $value)) {
            return 0.0;
        }

        if (is_string($value)) {
            $value = str_replace([' ', ','], ['', '.'], $value);
        }

        return (float) $value;
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
