<?php

declare(strict_types=1);

namespace App\Websites\No1tuningfiles\Controller;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Service\VehicleApiProxy;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WebsiteApiController extends AbstractController
{
    public function __construct(
        private readonly VehicleApiProxy $vehicleApiProxy,
        private readonly Connection $connection,
    ) {
    }

    #[Route('/api/vehicle/types', name: 'no1_website_api_vehicle_types', methods: ['GET'])]
    public function types(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/types', [], null, $portal);
    }

    #[Route('/api/vehicle/brands/{typeId}', name: 'no1_website_api_vehicle_brands', requirements: ['typeId' => '\d+'], methods: ['GET'])]
    public function brands(int $typeId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/brands/' . $typeId, [], null, $portal);
    }

    #[Route('/api/vehicle/series/{brandId}', name: 'no1_website_api_vehicle_series', requirements: ['brandId' => '\d+'], methods: ['GET'])]
    public function series(int $brandId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/series/' . $brandId, [], null, $portal);
    }

    #[Route('/api/vehicle/models/{seriesId}', name: 'no1_website_api_vehicle_models', requirements: ['seriesId' => '\d+'], methods: ['GET'])]
    public function models(int $seriesId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/models/' . $seriesId, [], null, $portal);
    }

    #[Route('/api/vehicle/engines/{modelId}', name: 'no1_website_api_vehicle_engines', requirements: ['modelId' => '\d+'], methods: ['GET'])]
    public function engines(int $modelId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/engines/' . $modelId, [], null, $portal);
    }

    #[Route('/api/vehicle/engine-details/{engineId}', name: 'no1_website_api_vehicle_engine_details', requirements: ['engineId' => '\d+'], methods: ['GET'])]
    public function engineDetails(int $engineId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/engine-details/' . $engineId, [], null, $portal);
    }

    #[Route('/vehicle-api/asset', name: 'no1_website_vehicle_api_asset', methods: ['GET'])]
    public function asset(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);
        $path = (string) $request->query->get('path', '');

        return $this->vehicleApiProxy->proxyAsset($path);
    }

    #[Route('/api/contact', name: 'no1_website_api_contact', methods: ['POST'])]
    public function contact(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $this->ensureContactTable();

        $id = bin2hex(random_bytes(16));
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM);

        $this->connection->insert('no1_contact_submissions', [
            'id' => $id,
            'name' => (string) ($data['name'] ?? ''),
            'company' => (string) ($data['company'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'phone' => (string) ($data['phone'] ?? ''),
            'interest' => (string) ($data['interest'] ?? ''),
            'message' => (string) ($data['message'] ?? ''),
            'created_at' => $now,
        ]);

        return new JsonResponse([
            'id' => $id,
            'created_at' => $now,
        ], 201);
    }

    private function ensureContactTable(): void
    {
        $this->connection->executeStatement("
            CREATE TABLE IF NOT EXISTS no1_contact_submissions (
                id VARCHAR(36) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                company VARCHAR(255),
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(100),
                interest VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                created_at VARCHAR(50) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
}
