<?php

declare(strict_types=1);

namespace App\Portals\No1tuningfiles\Controller;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Service\VehicleApiProxy;
use App\Portals\No1tuningfiles\Security\No1tuningfilesPortalCustomerUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VehicleApiProxyController extends AbstractController
{
    public function __construct(private readonly VehicleApiProxy $vehicleApiProxy)
    {
    }

    #[Route('/vehicle-api/types', name: 'no1_portal_vehicle_api_types', methods: ['GET'])]
    public function types(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/types', [], $this->getNo1tuningfilesPortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/brands/{typeId}', name: 'no1_portal_vehicle_api_brands', requirements: ['typeId' => '\d+'], methods: ['GET'])]
    public function brands(int $typeId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/brands/' . $typeId, [], $this->getNo1tuningfilesPortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/series/{brandId}', name: 'no1_portal_vehicle_api_series', requirements: ['brandId' => '\d+'], methods: ['GET'])]
    public function series(int $brandId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/series/' . $brandId, [], $this->getNo1tuningfilesPortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/models/{seriesId}', name: 'no1_portal_vehicle_api_models', requirements: ['seriesId' => '\d+'], methods: ['GET'])]
    public function models(int $seriesId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/models/' . $seriesId, [], $this->getNo1tuningfilesPortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/engines/{modelId}', name: 'no1_portal_vehicle_api_engines', requirements: ['modelId' => '\d+'], methods: ['GET'])]
    public function engines(int $modelId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/engines/' . $modelId, [], $this->getNo1tuningfilesPortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/engine-details/{engineId}', name: 'no1_portal_vehicle_api_engine_details', requirements: ['engineId' => '\d+'], methods: ['GET'])]
    public function engineDetails(int $engineId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        $query = [];
        $stageId = trim((string) $request->query->get('stageId', ''));
        if ($stageId !== '') {
            $query['stageId'] = $stageId;
        }
        $templateVariant = trim((string) $request->query->get('templateVariant', ''));
        if ($templateVariant !== '') {
            $query['templateVariant'] = $templateVariant;
        }

        return $this->vehicleApiProxy->proxy('/engine-details/' . $engineId, $query, $this->getNo1tuningfilesPortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/track', name: 'no1_portal_vehicle_api_track', methods: ['POST'])]
    public function track(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        return $this->vehicleApiProxy->proxyPost('/track', $payload, $this->getNo1tuningfilesPortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/asset', name: 'no1_portal_vehicle_api_asset', methods: ['GET'])]
    public function asset(Request $request): Response
    {
        $path = (string) $request->query->get('path', '');

        return $this->vehicleApiProxy->proxyAsset($path);
    }

    /**
     * @param array<string, string> $query
     */
    private function getNo1tuningfilesPortalCustomerUser(): ?No1tuningfilesPortalCustomerUser
    {
        $user = $this->getUser();

        return $user instanceof No1tuningfilesPortalCustomerUser ? $user : null;
    }
}
