<?php

namespace App\Websites\Chiptuningfile\Service;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Firma;
use App\Entity\Portal;
use App\Repository\FirmaRepository;

final class WebsiteCompanyContextResolver
{
    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly FirmaRepository $firmaRepository,
    ) {
    }

    /**
     * @return array{portal:?Portal,firma:?Firma}
     */
    public function resolve(): array
    {
        $portal = $this->portalRuntimeResolver->resolveCurrentPortal();
        $firma = $portal?->getFirma() ?? $this->firmaRepository->findActiveByKuerzel('CPT');

        return [
            'portal' => $portal,
            'firma' => $firma,
        ];
    }
}
