<?php

declare(strict_types=1);

namespace App\Portals\Srfile\Security;

use App\Brand\Common\Context\SystemType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

final class SrfilePortalPublicRequestMatcher implements RequestMatcherInterface
{
    private const PUBLIC_PATHS = [
        '/login',
        '/registrieren',
        '/passwort-vergessen',
        '/configurator',
    ];

    private const PUBLIC_PREFIXES = [
        '/passwort-zuruecksetzen/',
        '/vehicle-api/',
    ];

    public function matches(Request $request): bool
    {
        if (
            $request->attributes->get('_brand_system_type') !== SystemType::PORTAL
            || $request->attributes->get('_brand_portal_key') !== 'srfile'
        ) {
            return false;
        }

        $path = $request->getPathInfo();
        if (in_array($path, self::PUBLIC_PATHS, true)) {
            return true;
        }

        foreach (self::PUBLIC_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
