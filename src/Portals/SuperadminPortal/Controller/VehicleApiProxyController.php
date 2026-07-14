<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Service\VehicleApiProxy;
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

    #[Route('/vehicle-api/types', name: 'superadmin_portal_vehicle_api_types', methods: ['GET'])]
    public function types(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/types', [], null, $portal);
    }

    #[Route('/vehicle-api/brands/{typeId}', name: 'superadmin_portal_vehicle_api_brands', requirements: ['typeId' => '\d+'], methods: ['GET'])]
    public function brands(int $typeId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/brands/' . $typeId, [], null, $portal);
    }

    #[Route('/vehicle-api/series/{brandId}', name: 'superadmin_portal_vehicle_api_series', requirements: ['brandId' => '\d+'], methods: ['GET'])]
    public function series(int $brandId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/series/' . $brandId, [], null, $portal);
    }

    #[Route('/vehicle-api/models/{seriesId}', name: 'superadmin_portal_vehicle_api_models', requirements: ['seriesId' => '\d+'], methods: ['GET'])]
    public function models(int $seriesId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/models/' . $seriesId, [], null, $portal);
    }

    #[Route('/vehicle-api/engines/{modelId}', name: 'superadmin_portal_vehicle_api_engines', requirements: ['modelId' => '\d+'], methods: ['GET'])]
    public function engines(int $modelId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/engines/' . $modelId, [], null, $portal);
    }

    #[Route('/vehicle-api/engine-details/{engineId}', name: 'superadmin_portal_vehicle_api_engine_details', requirements: ['engineId' => '\d+'], methods: ['GET'])]
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

        return $this->vehicleApiProxy->proxy('/engine-details/' . $engineId, $query, null, $portal);
    }

    #[Route('/vehicle-api/track', name: 'superadmin_portal_vehicle_api_track', methods: ['POST'])]
    public function track(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        return $this->vehicleApiProxy->proxyPost('/track', $payload, null, $portal);
    }

    #[Route('/vehicle-api/asset', name: 'superadmin_portal_vehicle_api_asset', methods: ['GET'])]
    public function asset(Request $request): Response
    {
        $path = (string) $request->query->get('path', '');

        return $this->vehicleApiProxy->proxyAsset($path);
    }

    #[Route('/vehicle-api/admin/stages/save', name: 'superadmin_portal_vehicle_api_admin_stages_save', methods: ['POST'])]
    public function saveStage(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/stages/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/series/save', name: 'superadmin_portal_vehicle_api_admin_series_save', methods: ['POST'])]
    public function saveSeries(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/series/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/models/save', name: 'superadmin_portal_vehicle_api_admin_models_save', methods: ['POST'])]
    public function saveModel(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/models/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/engines/save', name: 'superadmin_portal_vehicle_api_admin_engines_save', methods: ['POST'])]
    public function saveEngine(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/engines/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/options/save', name: 'superadmin_portal_vehicle_api_admin_options_save', methods: ['POST'])]
    public function saveOption(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/options/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/ecus/save', name: 'superadmin_portal_vehicle_api_admin_ecus_save', methods: ['POST'])]
    public function saveEcu(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/ecus/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/gearboxes/save', name: 'superadmin_portal_vehicle_api_admin_gearboxes_save', methods: ['POST'])]
    public function saveGearbox(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/gearboxes/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/hints/save', name: 'superadmin_portal_vehicle_api_admin_hints_save', methods: ['POST'])]
    public function saveHint(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/hints/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/tool-entries/save', name: 'superadmin_portal_vehicle_api_admin_tool_entries_save', methods: ['POST'])]
    public function saveToolEntry(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/tool-entries/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/stages/{id}/inactive', name: 'superadmin_portal_vehicle_api_admin_stages_inactive', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deactivateStage(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/stages/' . $id . '/inactive', $request, $portalRuntimeResolver);
    }

    private function proxyAdminSave(string $path, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        return $this->vehicleApiProxy->proxyPost($path, $payload, null, $portal);
    }
}
