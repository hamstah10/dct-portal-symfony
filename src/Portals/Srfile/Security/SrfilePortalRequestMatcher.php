<?php

declare(strict_types=1);

namespace App\Portals\Srfile\Security;

use App\Brand\Common\Context\SystemType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

final class SrfilePortalRequestMatcher implements RequestMatcherInterface
{
    public function matches(Request $request): bool
    {
        return $request->attributes->get('_brand_system_type') === SystemType::PORTAL
            && $request->attributes->get('_brand_portal_key') === 'srfile';
    }
}
