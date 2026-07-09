<?php

namespace App\Brand\Common\Context;

use App\Repository\PortalRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class BrandContextResolver
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly PortalRepository $portalRepository,
    ) {
    }

    public function resolve(?Request $request = null): BrandContext
    {
        $request ??= $this->requestStack->getCurrentRequest();
        $host = strtolower(trim((string) $request?->getHost()));

        $systemType = $request?->attributes->get('_brand_system_type');
        $portalKey = $request?->attributes->get('_brand_portal_key');
        if (is_string($systemType) && is_string($portalKey) && $portalKey !== '') {
            return new BrandContext($portalKey, $systemType, $host);
        }

        $portal = $this->portalRepository->findOneByKnownHost($host);
        if ($portal !== null) {
            if ($portal->getPortalHost() !== null && strtolower($portal->getPortalHost()) === $host) {
                return new BrandContext((string) $portal->getPortalKey(), SystemType::PORTAL, $host);
            }

            if ($portal->getWebsiteHost() !== null && strtolower($portal->getWebsiteHost()) === $host) {
                return new BrandContext((string) $portal->getPortalKey(), SystemType::WEBSITE, $host);
            }
        }

        return new BrandContext('superadmin', SystemType::SUPERADMIN, $host);
    }
}
