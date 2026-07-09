<?php

namespace App\Controller\Api;

use App\Entity\DynoCustomer;
use App\Entity\DynoTrip;
use App\Entity\DynoVehicle;
use App\Repository\ApiTokenRepository;
use App\Repository\DynoCustomerRepository;
use App\Repository\DynoTripRepository;
use App\Repository\DynoVehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/crm/customers')]
final class DynoCustomerApiController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly DynoCustomerRepository $customerRepository,
        private readonly DynoVehicleRepository $vehicleRepository,
        private readonly DynoTripRepository $tripRepository,
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%api_token%')] private readonly string $apiToken,
    ) {
    }

    #[Route('', name: 'api_dyno_customers_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $customers = $this->customerRepository->findBy([], ['createdAt' => 'DESC']);

        return new JsonResponse([
            'data' => array_map(fn (DynoCustomer $customer): array => $this->normalizeCustomer($customer), $customers),
        ]);
    }

    #[Route('/{id}', name: 'api_dyno_customers_get', methods: ['GET'])]
    public function getCustomer(Request $request, string $id): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $customer = $this->customerRepository->find($id);
        if (!$customer instanceof DynoCustomer) {
            return $this->errorResponse('Kunde nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        $vehicle = $customer->getVehicle();
        $trips = [];
        if ($vehicle instanceof DynoVehicle) {
            $trips = $this->tripRepository->findBy(['vehicle' => $vehicle], ['startTime' => 'DESC']);
        }

        return new JsonResponse([
            'customer' => $this->normalizeCustomer($customer),
            'vehicle' => $vehicle instanceof DynoVehicle ? $this->normalizeVehicle($vehicle) : null,
            'trips' => array_map(fn (DynoTrip $trip): array => $this->normalizeTrip($trip, false), $trips),
        ]);
    }

    #[Route('', name: 'api_dyno_customers_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        try {
            $payload = $this->readJsonPayload($request);
            $customer = new DynoCustomer();
            $this->applyCustomerPayload($customer, $payload);
            $this->entityManager->persist($customer);
            $this->entityManager->flush();
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->normalizeCustomer($customer), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_dyno_customers_put', methods: ['PUT'])]
    public function update(Request $request, string $id): JsonResponse
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $customer = $this->customerRepository->find($id);
        if (!$customer instanceof DynoCustomer) {
            return $this->errorResponse('Kunde nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $this->readJsonPayload($request);
            $this->applyCustomerPayload($customer, $payload);
            $this->entityManager->flush();
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->normalizeCustomer($customer));
    }

    #[Route('/{id}', name: 'api_dyno_customers_delete', methods: ['DELETE'])]
    public function delete(Request $request, string $id): Response
    {
        if (($authResponse = $this->authorizeDynoApi($request)) instanceof JsonResponse) {
            return $authResponse;
        }

        $customer = $this->customerRepository->find($id);
        if (!$customer instanceof DynoCustomer) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $this->entityManager->remove($customer);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function applyCustomerPayload(DynoCustomer $customer, array $payload): void
    {
        $name = trim((string) ($payload['name'] ?? $customer->getName()));
        $email = trim((string) ($payload['email'] ?? $customer->getEmail()));
        if ('' === $name || '' === $email) {
            throw new \InvalidArgumentException('Name und E-Mail sind erforderlich.');
        }

        $customer->setName($name);
        $customer->setEmail($email);
        $customer->setPhone(isset($payload['phone']) ? (string) $payload['phone'] : $customer->getPhone());
        $customer->setNotes(isset($payload['notes']) ? (string) $payload['notes'] : $customer->getNotes());

        if (array_key_exists('vehicle_id', $payload)) {
            $vehicleId = trim((string) $payload['vehicle_id']);
            if ('' === $vehicleId) {
                $customer->setVehicle(null);
            } else {
                $vehicle = $this->vehicleRepository->find($vehicleId);
                if (!$vehicle instanceof DynoVehicle) {
                    throw new \InvalidArgumentException('Fahrzeug nicht gefunden.');
                }
                $customer->setVehicle($vehicle);
            }
        }

        if (array_key_exists('created_at', $payload)) {
            $customer->setCreatedAt($this->parseDateTime($payload['created_at'], $customer->getCreatedAt()));
        }
    }

    private function normalizeCustomer(DynoCustomer $customer): array
    {
        return [
            'id' => $customer->getId(),
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
            'phone' => $customer->getPhone(),
            'vehicle_id' => $customer->getVehicle()?->getId(),
            'notes' => $customer->getNotes(),
            'created_at' => $customer->getCreatedAt()->format(DATE_ATOM),
        ];
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
