<?php

declare(strict_types=1);

namespace App\Portals\Chiptuningfile\Controller;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Portals\Chiptuningfile\Service\PortalHeaderSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class HeaderSearchController extends AbstractController
{
    #[Route('/header-search/autocomplete', name: 'cpt_portal_header_search_autocomplete', methods: ['GET'])]
    public function autocomplete(
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
        PortalHeaderSearchService $searchService,
    ): JsonResponse {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);
        $query = trim((string) $request->query->get('query', ''));

        return $this->json([
            'results' => $searchService->search($query, $portal->getEnabledModules(), 8),
            'next_page' => null,
        ]);
    }
}
