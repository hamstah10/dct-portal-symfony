<?php

declare(strict_types=1);

namespace App\Portals\Srfile\Controller;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Service\VehicleApiProxy;
use App\Portals\Srfile\Security\SrfilePortalCustomerUser;
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

    #[Route('/vehicle-api/types', name: 'srf_portal_vehicle_api_types', methods: ['GET'])]
    public function types(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/types', [], $this->getSrfilePortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/brands/{typeId}', name: 'srf_portal_vehicle_api_brands', requirements: ['typeId' => '\d+'], methods: ['GET'])]
    public function brands(int $typeId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/brands/' . $typeId, [], $this->getSrfilePortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/series/{brandId}', name: 'srf_portal_vehicle_api_series', requirements: ['brandId' => '\d+'], methods: ['GET'])]
    public function series(int $brandId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/series/' . $brandId, [], $this->getSrfilePortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/models/{seriesId}', name: 'srf_portal_vehicle_api_models', requirements: ['seriesId' => '\d+'], methods: ['GET'])]
    public function models(int $seriesId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/models/' . $seriesId, [], $this->getSrfilePortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/engines/{modelId}', name: 'srf_portal_vehicle_api_engines', requirements: ['modelId' => '\d+'], methods: ['GET'])]
    public function engines(int $modelId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/engines/' . $modelId, [], $this->getSrfilePortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/engine-details/{engineId}', name: 'srf_portal_vehicle_api_engine_details', requirements: ['engineId' => '\d+'], methods: ['GET'])]
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

        return $this->vehicleApiProxy->proxy('/engine-details/' . $engineId, $query, $this->getSrfilePortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/track', name: 'srf_portal_vehicle_api_track', methods: ['POST'])]
    public function track(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        return $this->vehicleApiProxy->proxyPost('/track', $payload, $this->getSrfilePortalCustomerUser(), $portal);
    }

    #[Route('/vehicle-api/asset', name: 'srf_portal_vehicle_api_asset', methods: ['GET'])]
    public function asset(Request $request): Response
    {
        $path = (string) $request->query->get('path', '');

        return $this->vehicleApiProxy->proxyAsset($path);
    }

    /**
     * @param array<string, string> $query
     */
    private function getSrfilePortalCustomerUser(): ?SrfilePortalCustomerUser
    {
        $user = $this->getUser();

        return $user instanceof SrfilePortalCustomerUser ? $user : null;
    }
}
