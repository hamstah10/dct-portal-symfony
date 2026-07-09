<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Security;

use App\Brand\Common\Context\SystemType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

final class SuperadminPortalPublicRequestMatcher implements RequestMatcherInterface
{
    public function matches(Request $request): bool
    {
        if ($request->attributes->get('_brand_system_type') !== SystemType::PORTAL
            || $request->attributes->get('_brand_portal_key') !== 'superadmin_portal') {
            return false;
        }

        $path = $request->getPathInfo();

        return $path === '/login'
            || str_starts_with($path, '/api/watermark/');
    }
}
