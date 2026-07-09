<?php

namespace App\Service;

use App\Entity\DynoCustomer;
use App\Entity\DynoDtc;
use App\Entity\DynoSprint;
use App\Entity\DynoTrip;
use App\Entity\DynoVehicle;
use App\Repository\ApiTokenRepository;
use App\Repository\DynoCustomerRepository;
use App\Repository\DynoDtcRepository;
use App\Repository\DynoSprintRepository;
use App\Repository\DynoTripRepository;
use App\Repository\DynoVehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DynoRemoteSyncService
{
    public const TYPE_VEHICLES = 'vehicles';
    public const TYPE_CUSTOMERS = 'customers';
    public const TYPE_TRIPS = 'trips';
    public const TYPE_SPRINTS = 'sprints';
    public const TYPE_DTCS = 'dtcs';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly DynoVehicleRepository $vehicleRepository,
        private readonly DynoCustomerRepository $customerRepository,
        private readonly DynoTripRepository $tripRepository,
        private readonly DynoSprintRepository $sprintRepository,
        private readonly DynoDtcRepository $dtcRepository,
        private readonly ApiTokenRepository $apiTokenRepository,
        #[Autowire('%dyno.remote_api_base%')] private readonly string $remoteApiBase,
        #[Autowire('%dyno.remote_api_token%')] private readonly string $remoteApiToken,
        #[Autowire('%dyno.remote_api_token_name%')] private readonly string $remoteApiTokenName,
    ) {
    }

    public function sync(string $type): int
    {
        return match ($type) {
            self::TYPE_VEHICLES => $this->syncVehicles(),
            self::TYPE_CUSTOMERS => $this->syncCustomers(),
            self::TYPE_TRIPS => $this->syncTrips(),
            self::TYPE_SPRINTS => $this->syncSprints(),
            self::TYPE_DTCS => $this->syncDtcs(),
            default => throw new \InvalidArgumentException('Unbekannter Sync-Typ.'),
        };
    }

    private function syncVehicles(): int
    {
        $rows = $this->fetchList('vehicles');
        $count = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $id = trim((string) ($row['id'] ?? ''));
            if ('' === $id) {
                continue;
            }

            $vehicle = $this->vehicleRepository->find($id);
            if (!$vehicle instanceof DynoVehicle) {
                $vehicle = new DynoVehicle();
                $vehicle->setId($id);
                $this->entityManager->persist($vehicle);
            }

            $vehicle->setName((string) ($row['name'] ?? $vehicle->getName()));
            $vehicle->setMake(isset($row['make']) ? (string) $row['make'] : $vehicle->getMake());
            $vehicle->setModel(isset($row['model']) ? (string) $row['model'] : $vehicle->getModel());
            $vehicle->setYear(isset($row['year']) ? (int) $row['year'] : $vehicle->getYear());
            $vehicle->setStage(isset($row['stage']) ? (string) $row['stage'] : $vehicle->getStage());
            $vehicle->setMaxPs(isset($row['max_ps']) ? (int) $row['max_ps'] : $vehicle->getMaxPs());
            $vehicle->setMaxNm(isset($row['max_nm']) ? (int) $row['max_nm'] : $vehicle->getMaxNm());
            $vehicle->setMaxRpm(isset($row['max_rpm']) ? (int) $row['max_rpm'] : $vehicle->getMaxRpm());
            $vehicle->setIdleRpm(isset($row['idle_rpm']) ? (int) $row['idle_rpm'] : $vehicle->getIdleRpm());
            $vehicle->setEngineCc(isset($row['engine_cc']) ? (int) $row['engine_cc'] : $vehicle->getEngineCc());
            $vehicle->setCylinders(isset($row['cylinders']) ? (int) $row['cylinders'] : $vehicle->getCylinders());
            $vehicle->setFuelType(isset($row['fuel_type']) ? (string) $row['fuel_type'] : $vehicle->getFuelType());
            if (array_key_exists('is_active', $row)) {
                $vehicle->setIsActive((bool) $row['is_active']);
            }

            if (array_key_exists('created_at', $row)) {
                $createdAt = $this->parseDateTime($row['created_at']);
                if ($createdAt instanceof \DateTimeImmutable) {
                    $vehicle->setCreatedAt($createdAt);
                }
            }

            $count++;
        }

        $this->entityManager->flush();

        return $count;
    }

    private function syncCustomers(): int
    {
        $rows = $this->fetchList('crm/customers');
        $count = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $id = trim((string) ($row['id'] ?? ''));
            if ('' === $id) {
                continue;
            }

            $customer = $this->customerRepository->find($id);
            if (!$customer instanceof DynoCustomer) {
                $customer = new DynoCustomer();
                $customer->setId($id);
                $this->entityManager->persist($customer);
            }

            $customer->setName((string) ($row['name'] ?? $customer->getName()));
            $customer->setEmail((string) ($row['email'] ?? $customer->getEmail()));
            $customer->setPhone(array_key_exists('phone', $row) ? ($row['phone'] !== null ? (string) $row['phone'] : null) : $customer->getPhone());
            $customer->setNotes(array_key_exists('notes', $row) ? ($row['notes'] !== null ? (string) $row['notes'] : null) : $customer->getNotes());

            if (array_key_exists('vehicle_id', $row)) {
                $vehicleId = trim((string) ($row['vehicle_id'] ?? ''));
                $vehicle = '' !== $vehicleId ? $this->vehicleRepository->find($vehicleId) : null;
                $customer->setVehicle($vehicle instanceof DynoVehicle ? $vehicle : null);
            }

            if (array_key_exists('created_at', $row)) {
                $createdAt = $this->parseDateTime($row['created_at']);
                if ($createdAt instanceof \DateTimeImmutable) {
                    $customer->setCreatedAt($createdAt);
                }
            }

            $count++;
        }

        $this->entityManager->flush();

        return $count;
    }

    private function syncTrips(): int
    {
        $rows = $this->fetchList('trips');
        $count = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $id = trim((string) ($row['id'] ?? ''));
            if ('' === $id) {
                continue;
            }

            $trip = $this->tripRepository->find($id);
            if (!$trip instanceof DynoTrip) {
                $trip = new DynoTrip();
                $trip->setId($id);
                $this->entityManager->persist($trip);
            }

            $startTime = $this->parseDateTime($row['start_time'] ?? null) ?? $trip->getStartTime();
            $endTime = $this->parseDateTime($row['end_time'] ?? null) ?? $trip->getEndTime();

            $trip->setStartTime($startTime);
            $trip->setEndTime($endTime);
            $trip->setDurationSeconds(isset($row['duration_seconds']) ? (int) $row['duration_seconds'] : $trip->getDurationSeconds());
            $trip->setDistanceKm($this->normalizeDecimal($row['distance_km'] ?? $trip->getDistanceKm()));
            $trip->setMaxSpeedKmh($this->normalizeDecimal($row['max_speed_kmh'] ?? $trip->getMaxSpeedKmh()));
            $trip->setAvgSpeedKmh($this->normalizeDecimal($row['avg_speed_kmh'] ?? $trip->getAvgSpeedKmh()));
            $trip->setMaxRpm(isset($row['max_rpm']) ? (int) $row['max_rpm'] : $trip->getMaxRpm());
            $trip->setMaxNm(isset($row['max_nm']) ? (int) $row['max_nm'] : $trip->getMaxNm());
            $trip->setMaxPs(isset($row['max_ps']) ? (int) $row['max_ps'] : $trip->getMaxPs());
            $trip->setDriveMode(isset($row['drive_mode']) ? (string) $row['drive_mode'] : $trip->getDriveMode());
            $trip->setVehicleName(isset($row['vehicle_name']) ? (string) $row['vehicle_name'] : $trip->getVehicleName());
            if (array_key_exists('data_points', $row) || array_key_exists('dataPoints', $row)) {
                $dataPoints = $this->normalizeDataPoints($row['data_points'] ?? $row['dataPoints'] ?? null);
                if (null !== $dataPoints) {
                    $trip->setDataPoints($dataPoints);
                }
            }

            if (array_key_exists('vehicle_id', $row)) {
                $vehicleId = trim((string) ($row['vehicle_id'] ?? ''));
                $vehicle = '' !== $vehicleId ? $this->vehicleRepository->find($vehicleId) : null;
                $trip->setVehicle($vehicle instanceof DynoVehicle ? $vehicle : null);
            }

            if (array_key_exists('created_at', $row)) {
                $createdAt = $this->parseDateTime($row['created_at']);
                if ($createdAt instanceof \DateTimeImmutable) {
                    $trip->setCreatedAt($createdAt);
                }
            }

            $count++;
        }

        $this->entityManager->flush();

        return $count;
    }

    private function syncSprints(): int
    {
        $rows = $this->fetchList('crm/sprints');
        $count = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $id = trim((string) ($row['id'] ?? ''));
            if ('' === $id) {
                continue;
            }

            $sprint = $this->sprintRepository->find($id);
            if (!$sprint instanceof DynoSprint) {
                $sprint = new DynoSprint();
                $sprint->setId($id);
                $this->entityManager->persist($sprint);
            }

            $sprint->setSprintType((string) ($row['sprint_type'] ?? $sprint->getSprintType()));
            $sprint->setTimeSeconds($this->normalizeDecimal($row['time_seconds'] ?? $sprint->getTimeSeconds()));
            if (array_key_exists('trip_id', $row) || array_key_exists('tripId', $row)) {
                $tripId = trim((string) ($row['trip_id'] ?? $row['tripId'] ?? ''));
                $sprint->setTripId('' === $tripId ? null : $tripId);
            }
            $distanceMeters = $this->resolveDistanceMeters($row);
            if (null !== $distanceMeters) {
                $sprint->setDistanceMeters($this->normalizeDecimal($distanceMeters));
            }
            $sprint->setMaxRpm(isset($row['max_rpm']) ? (int) $row['max_rpm'] : $sprint->getMaxRpm());
            $sprint->setMaxPs(isset($row['max_ps']) ? (int) $row['max_ps'] : $sprint->getMaxPs());
            $sprint->setMaxNm(isset($row['max_nm']) ? (int) $row['max_nm'] : $sprint->getMaxNm());
            $sprint->setDriveMode(isset($row['drive_mode']) ? (string) $row['drive_mode'] : $sprint->getDriveMode());
            $sprint->setVehicleName(isset($row['vehicle_name']) ? (string) $row['vehicle_name'] : $sprint->getVehicleName());

            if (array_key_exists('vehicle_id', $row)) {
                $vehicleId = trim((string) ($row['vehicle_id'] ?? ''));
                $vehicle = '' !== $vehicleId ? $this->vehicleRepository->find($vehicleId) : null;
                $sprint->setVehicle($vehicle instanceof DynoVehicle ? $vehicle : null);
            }

            if (array_key_exists('created_at', $row)) {
                $createdAt = $this->parseDateTime($row['created_at']);
                if ($createdAt instanceof \DateTimeImmutable) {
                    $sprint->setCreatedAt($createdAt);
                }
            }

            $count++;
        }

        $this->entityManager->flush();

        return $count;
    }

    private function syncDtcs(): int
    {
        $rows = $this->fetchList('dtcs');
        $count = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $code = trim((string) ($row['code'] ?? ''));
            if ('' === $code) {
                continue;
            }

            $dtc = $this->dtcRepository->findOneBy(['code' => $code]);
            if (!$dtc instanceof DynoDtc) {
                $dtc = new DynoDtc();
                $dtc->setCode($code);
                $this->entityManager->persist($dtc);
            }

            $dtc->setDescription(array_key_exists('description', $row) ? (string) $row['description'] : $dtc->getDescription());
            $dtc->setPayload($row);

            if (array_key_exists('created_at', $row)) {
                $createdAt = $this->parseDateTime($row['created_at']);
                if ($createdAt instanceof \DateTimeImmutable) {
                    $dtc->setCreatedAt($createdAt);
                }
            }

            $count++;
        }

        $this->entityManager->flush();

        return $count;
    }

    /**
     * @return array<int, mixed>
     */
    private function fetchList(string $path): array
    {
        $base = rtrim(trim($this->remoteApiBase), '/');
        if ('' === $base) {
            throw new \RuntimeException('DYNO Remote API Base ist nicht konfiguriert.');
        }

        $token = $this->resolveToken();
        if ('' === $token) {
            throw new \RuntimeException('DYNO Remote API Token ist nicht konfiguriert.');
        }

        $url = $base . '/' . ltrim($path, '/');
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        $status = $response->getStatusCode();
        $content = $response->getContent(false);
        if ($status >= 400) {
            throw new \RuntimeException(sprintf('Remote API Fehler (%d): %s', $status, $url));
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return [];
        }

        if (array_key_exists('data', $decoded) && is_array($decoded['data'])) {
            return $decoded['data'];
        }

        return $decoded;
    }

    private function resolveToken(): string
    {
        $token = trim($this->remoteApiToken);
        if ('' !== $token) {
            return $token;
        }

        $name = trim($this->remoteApiTokenName);
        if ('' !== $name) {
            $apiToken = $this->apiTokenRepository->findActiveByApplicationName($name);
            if (null !== $apiToken) {
                return trim((string) $apiToken->getApiToken());
            }
        }

        return '';
    }

    private function parseDateTime(mixed $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === trim((string) $value)) {
            return null;
        }

        try {
            return new \DateTimeImmutable((string) $value);
        } catch (\Throwable) {
            return null;
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
     * @param array<string, mixed> $row
     */
    private function resolveDistanceMeters(array $row): ?float
    {
        $metersKeys = [
            'distance_m',
            'distance_meters',
            'distanceMeters',
            'wegstrecke_m',
            'wegstreckeMeters',
        ];

        foreach ($metersKeys as $key) {
            if (array_key_exists($key, $row)) {
                $value = $row[$key];

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
            if (array_key_exists($key, $row)) {
                $value = $row[$key];

                return null === $value || '' === $value ? 0.0 : $this->parseNumber($value) * 1000;
            }
        }

        $rawKeys = [
            'distance',
            'wegstrecke',
        ];

        foreach ($rawKeys as $key) {
            if (array_key_exists($key, $row)) {
                $value = $row[$key];

                return null === $value || '' === $value ? 0.0 : $this->parseNumber($value);
            }
        }

        foreach ($row as $key => $value) {
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

    /**
     * @return array<int, mixed>|null
     */
    private function normalizeDataPoints(mixed $value): ?array
    {
        if (null === $value) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && '' !== trim($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
