<?php

namespace App\Portals\Chiptuningfile\Controller;

use App\Brand\Common\Context\BrandContextResolver;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use App\Portals\Chiptuningfile\Service\PortalVehicleDatabaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VehicleController extends AbstractController
{
    #[Route('/fahrzeuge', name: 'cpt_portal_vehicles', methods: ['GET'])]
    public function index(
        Request $request,
        PortalVehicleDatabaseService $portalVehicleDatabaseService,
        BrandContextResolver $brandContextResolver,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        $portalConfig = $portalRuntimeResolver->requirePortalModule(Portal::MODULE_VEHICLE_DATABASE, $request);
        $query = $request->query->get('q');
        $brandId = $request->query->getInt('brand');
        if ($brandId <= 0) {
            $brandId = null;
        }

        return $this->render('portal/chiptuningfile/vehicle/index.html.twig', [
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
            'database' => $portalVehicleDatabaseService->search(is_string($query) ? $query : null, $brandId),
        ]);
    }
}
