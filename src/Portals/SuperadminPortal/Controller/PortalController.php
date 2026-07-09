<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use App\Portals\SuperadminPortal\Service\SuperadminPortalBootstrapBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class PortalController extends AbstractController
{
    #[Route('/', name: 'superadmin_portal_dashboard', methods: ['GET'])]
    public function index(
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
        SuperadminPortalBootstrapBuilder $bootstrapBuilder,
    ): Response {
        return $this->renderPortalHost($request, $portalRuntimeResolver, $bootstrapBuilder);
    }

    #[Route('/{reactRoute}', name: 'superadmin_portal_app', requirements: ['reactRoute' => '(?!(?:_portal-react|vehicle-api|operations-api|api)).+'], methods: ['GET'])]
    public function app(
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
        SuperadminPortalBootstrapBuilder $bootstrapBuilder,
    ): Response {
        return $this->renderPortalHost($request, $portalRuntimeResolver, $bootstrapBuilder);
    }

    private function renderPortalHost(
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
        SuperadminPortalBootstrapBuilder $bootstrapBuilder,
    ): Response {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);
        if ($portal->getPortalType() !== Portal::TYPE_SUPERADMIN_PORTAL) {
            throw $this->createNotFoundException('Portal type mismatch.');
        }

        return $this->render('portal/superadmin_portal/dashboard/index.html.twig', [
            'title' => $portal->getName() ?: 'Superadmin Portal',
            'portalConfig' => $portal,
            'reactBootstrap' => $bootstrapBuilder->build($portal),
        ]);
    }
}
