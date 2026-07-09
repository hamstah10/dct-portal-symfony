<?php

namespace App\Portals\Chiptuningfile\Security;

use App\Brand\Common\Context\SystemType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

final class ChiptuningfilePortalRequestMatcher implements RequestMatcherInterface
{
    public function matches(Request $request): bool
    {
        return $request->attributes->get('_brand_system_type') === SystemType::PORTAL
            && $request->attributes->get('_brand_portal_key') === 'chiptuningfile';
    }
}
