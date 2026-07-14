<?php

declare(strict_types=1);

namespace App\Portals\No1tuningfiles\Security;

use App\Entity\Kunde;

final class No1tuningfilesPortalAccessChecker
{
    private const ALLOWED_COMPANY_CODES = ['NO', 'NO1'];

    public function canAccess(Kunde $customer): bool
    {
        if ($customer->isGesperrt()) {
            return false;
        }

        $companyCode = strtoupper(trim((string) $customer->getFirma()?->getKuerzel()));

        return in_array($companyCode, self::ALLOWED_COMPANY_CODES, true);
    }
}
