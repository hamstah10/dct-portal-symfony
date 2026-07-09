<?php

namespace App\Portals\Chiptuningfile\Security;

use App\Entity\Kunde;

final class ChiptuningfilePortalAccessChecker
{
    private const ALLOWED_COMPANY_CODE = 'CPT';

    public function canAccess(Kunde $customer): bool
    {
        if ($customer->isGesperrt()) {
            return false;
        }

        $companyCode = strtoupper(trim((string) $customer->getFirma()?->getKuerzel()));

        return self::ALLOWED_COMPANY_CODE === $companyCode;
    }
}
