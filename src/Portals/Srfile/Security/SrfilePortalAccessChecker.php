<?php

declare(strict_types=1);

namespace App\Portals\Srfile\Security;

use App\Entity\Kunde;

final class SrfilePortalAccessChecker
{
    private const ALLOWED_COMPANY_CODE = 'TF';

    public function canAccess(Kunde $customer): bool
    {
        if ($customer->isGesperrt()) {
            return false;
        }

        $companyCode = strtoupper(trim((string) $customer->getFirma()?->getKuerzel()));

        return self::ALLOWED_COMPANY_CODE === $companyCode;
    }
}
