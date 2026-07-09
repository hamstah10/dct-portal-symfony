<?php

namespace App\Controller\Api;

use App\Entity\Auftrag;
use App\Entity\Kunde;
use App\Repository\ApiTokenRepository;
use App\Repository\KundeRepository;
use App\Service\Api\CustomerApiAuthService;
use App\Service\Webhook\WebhookDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/orders')]
final class OrderController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly KundeRepository $kundeRepository,
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly CustomerApiAuthService $customerApiAuthService,
        private readonly WebhookDispatcher $webhookDispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $apiToken,
    ) {
    }

    #[Route('', name: 'api_orders_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $auth = $this->authorizeRequest($request);
        if (($auth['error'] ?? null) instanceof JsonResponse) {
            return $auth['error'];
        }

        $authenticatedCustomer = $auth['customer'] ?? null;

        try {
            $payload = $this->readJsonPayload($request);

            $customerId = $this->extractPositiveInt($payload, 'customerId');
            $customerEmail = $this->extractString($payload, 'customerEmail');
            $fileName = $this->extractString($payload, 'fileName');
            $fileSize = $this->extractPositiveInt($payload, 'fileSize');
            $fileData = $this->normalizeBase64($this->extractString($payload, 'fileData'));
            $vehicleType = $this->extractString($payload, 'vehicleType');
            $manufacturer = $this->extractString($payload, 'manufacturer');
            $model = $this->extractString($payload, 'model');
            $built = $this->extractString($payload, 'built');
            $engine = $this->extractString($payload, 'engine');
            $vehicleDisplay = $this->extractString($payload, 'vehicleDisplay');
            $tuningTool = $this->extractString($payload, 'tuningTool');
            $method = $this->extractString($payload, 'method');
            $slaveOrMaster = $this->extractString($payload, 'slaveOrMaster');
            $stage = $this->extractString($payload, 'stage');
            $createdAt = $this->extractIsoDateTime($payload, 'createdAt');
            $deviceName = $this->extractString($payload, 'deviceName');

            if (false === filter_var($customerEmail, \FILTER_VALIDATE_EMAIL)) {
                return $this->errorResponse('customerEmail ist keine gueltige E-Mail-Adresse.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $decodedFileData = base64_decode($fileData, true);
            if (false === $decodedFileData) {
                return $this->errorResponse('fileData ist keine gueltige Base64-Zeichenkette.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (strlen($decodedFileData) !== $fileSize) {
                return $this->errorResponse('fileSize passt nicht zur dekodierten Dateigroesse.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $customer = $this->kundeRepository->find($customerId);
            if (null === $customer) {
                return $this->errorResponse('Kunde nicht gefunden.', Response::HTTP_NOT_FOUND);
            }

            if ($authenticatedCustomer instanceof Kunde && $authenticatedCustomer->getId() !== $customer->getId()) {
                return $this->errorResponse('customerId passt nicht zum angemeldeten Benutzer.', Response::HTTP_FORBIDDEN);
            }

            $knownCustomerEmail = trim((string) $customer->getEmail());
            if ('' === $knownCustomerEmail || 0 !== strcasecmp($knownCustomerEmail, $customerEmail)) {
                return $this->errorResponse('customerEmail passt nicht zur customerId.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $order = (new Auftrag())
                ->setKunde($customer)
                ->setCustomerEmail($customerEmail)
                ->setFileName($fileName)
                ->setFileData($fileData)
                ->setFileSize($fileSize)
                ->setVehicleType($vehicleType)
                ->setManufacturer($manufacturer)
                ->setModel($model)
                ->setBuilt($built)
                ->setEngine($engine)
                ->setVehicleDisplay($vehicleDisplay)
                ->setTuningTool($tuningTool)
                ->setTuningMethod($method)
                ->setSlaveOrMaster($slaveOrMaster)
                ->setStageLabel($stage)
                ->setDeviceName($deviceName)
                ->setVehicleData($this->buildVehicleData(
                    $payload,
                    $vehicleType,
                    $manufacturer,
                    $model,
                    $built,
                    $engine,
                    $vehicleDisplay,
                    $tuningTool,
                    $method,
                    $slaveOrMaster,
                    $stage,
                    $createdAt,
                    $deviceName
                ))
                ->setCreatedAt($createdAt)
                ->setStatus(Auftrag::STATUS_RECEIVED);

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $this->webhookDispatcher->dispatch('order.created', [
                'orderId' => $order->getId(),
                'customerId' => $customer->getId(),
                'customerEmail' => $order->getCustomerEmail(),
                'fileName' => $order->getFileName(),
                'fileSize' => $order->getFileSize(),
                'vehicleType' => $order->getVehicleType(),
                'manufacturer' => $order->getManufacturer(),
                'model' => $order->getModel(),
                'built' => $order->getBuilt(),
                'engine' => $order->getEngine(),
                'vehicleDisplay' => $order->getVehicleDisplay(),
                'tuningTool' => $order->getTuningTool(),
                'method' => $order->getTuningMethod(),
                'slaveOrMaster' => $order->getSlaveOrMaster(),
                'stage' => $order->getStageLabel(),
                'deviceName' => $order->getDeviceName(),
                'createdAt' => $order->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ]);

            return new JsonResponse([
                'id' => $order->getId(),
                'status' => $order->getStatus(),
                'customerId' => $customer->getId(),
                'customerEmail' => $order->getCustomerEmail(),
                'fileName' => $order->getFileName(),
                'fileSize' => $order->getFileSize(),
                'vehicleType' => $order->getVehicleType(),
                'manufacturer' => $order->getManufacturer(),
                'model' => $order->getModel(),
                'built' => $order->getBuilt(),
                'engine' => $order->getEngine(),
                'vehicleDisplay' => $order->getVehicleDisplay(),
                'tuningTool' => $order->getTuningTool(),
                'method' => $order->getTuningMethod(),
                'slaveOrMaster' => $order->getSlaveOrMaster(),
                'stage' => $order->getStageLabel(),
                'deviceName' => $order->getDeviceName(),
                'createdAt' => $order->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @return array{isSystem: bool, customer: Kunde|null, error: JsonResponse|null}
     */
    private function authorizeRequest(Request $request): array
    {
        $provided = $this->getBearerToken($request);
        if (null === $provided) {
            return [
                'isSystem' => false,
                'customer' => null,
                'error' => $this->errorResponse('Token fehlt.', Response::HTTP_UNAUTHORIZED),
            ];
        }

        $envTokenMatches = '' !== trim($this->apiToken) && hash_equals($this->apiToken, $provided);
        $dbTokenMatches = null !== $this->apiTokenRepository->findOneBy(['apiToken' => $provided]);
        if ($envTokenMatches || $dbTokenMatches) {
            return [
                'isSystem' => true,
                'customer' => null,
                'error' => null,
            ];
        }

        $customer = $this->customerApiAuthService->findCustomerByAccessToken($provided);
        if ($customer instanceof Kunde) {
            return [
                'isSystem' => false,
                'customer' => $customer,
                'error' => null,
            ];
        }

        return [
            'isSystem' => false,
            'customer' => null,
            'error' => $this->errorResponse('Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractString(array $payload, string $key): string
    {
        if (!array_key_exists($key, $payload)) {
            throw new \InvalidArgumentException(sprintf('Pflichtfeld %s fehlt.', $key));
        }

        $value = $payload[$key];
        if (!is_string($value) || '' === trim($value)) {
            throw new \InvalidArgumentException(sprintf('Pflichtfeld %s muss ein nicht-leerer String sein.', $key));
        }

        return trim($value);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractPositiveInt(array $payload, string $key): int
    {
        if (!array_key_exists($key, $payload)) {
            throw new \InvalidArgumentException(sprintf('Pflichtfeld %s fehlt.', $key));
        }

        $value = $payload[$key];
        if (!is_int($value) && !is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Pflichtfeld %s muss eine Ganzzahl sein.', $key));
        }

        if (!is_numeric((string) $value)) {
            throw new \InvalidArgumentException(sprintf('Pflichtfeld %s muss eine Ganzzahl sein.', $key));
        }

        $normalized = (int) $value;
        if ($normalized <= 0) {
            throw new \InvalidArgumentException(sprintf('Pflichtfeld %s muss groesser als 0 sein.', $key));
        }

        return $normalized;
    }

    private function normalizeBase64(string $value): string
    {
        $normalized = trim($value);

        if (preg_match('/^data:[^;]+;base64,(.+)$/i', $normalized, $matches)) {
            $normalized = (string) ($matches[1] ?? '');
        }

        $normalized = preg_replace('/\s+/', '', $normalized) ?? '';

        if ('' === $normalized) {
            throw new \InvalidArgumentException('Pflichtfeld fileData muss einen Base64-Inhalt enthalten.');
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractIsoDateTime(array $payload, string $key): \DateTimeImmutable
    {
        $value = $this->extractString($payload, $key);

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw new \InvalidArgumentException(sprintf('Pflichtfeld %s muss ein gueltiges ISO-8601-Datum sein.', $key));
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>|null
     */
    private function extractVehicleData(array $payload): ?array
    {
        if (array_key_exists('vehicleData', $payload)) {
            if (!is_array($payload['vehicleData'])) {
                throw new \InvalidArgumentException('vehicleData muss ein Objekt sein.');
            }

            return [] === $payload['vehicleData'] ? null : $payload['vehicleData'];
        }

        if (array_key_exists('fahrzeugDaten', $payload)) {
            if (!is_array($payload['fahrzeugDaten'])) {
                throw new \InvalidArgumentException('fahrzeugDaten muss ein Objekt sein.');
            }

            return [] === $payload['fahrzeugDaten'] ? null : $payload['fahrzeugDaten'];
        }

        $knownVehicleFields = [
            'vehicleType',
            'vehicleBrand',
            'vehicleSeries',
            'vehicleModel',
            'vehicleEngine',
            'vehicleFuel',
            'vehicleYear',
            'vehicleVin',
            'vehicleHsn',
            'vehicleTsn',
            'vehiclePowerPs',
            'vehiclePowerKw',
            'stage',
            'gearbox',
            'ecu',
        ];

        $vehicleData = [];
        foreach ($knownVehicleFields as $fieldName) {
            if (array_key_exists($fieldName, $payload)) {
                $vehicleData[$fieldName] = $payload[$fieldName];
            }
        }

        return [] === $vehicleData ? null : $vehicleData;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function buildVehicleData(
        array $payload,
        string $vehicleType,
        string $manufacturer,
        string $model,
        string $built,
        string $engine,
        string $vehicleDisplay,
        string $tuningTool,
        string $method,
        string $slaveOrMaster,
        string $stage,
        \DateTimeImmutable $createdAt,
        string $deviceName
    ): array {
        $extraData = $this->extractVehicleData($payload) ?? [];

        return array_merge($extraData, [
            'vehicleType' => $vehicleType,
            'manufacturer' => $manufacturer,
            'model' => $model,
            'built' => $built,
            'engine' => $engine,
            'vehicleDisplay' => $vehicleDisplay,
            'tuningTool' => $tuningTool,
            'method' => $method,
            'slaveOrMaster' => $slaveOrMaster,
            'stage' => $stage,
            'createdAt' => $createdAt->format(\DateTimeInterface::ATOM),
            'deviceName' => $deviceName,
        ]);
    }
}
