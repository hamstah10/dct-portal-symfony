<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CreditPackage;

final class CreditPackagePricingCalculator
{
    public const DEFAULT_TAX_RATE = 19.0;
    public const PAYPAL_PERCENT_FEE = 0.0179;
    public const PAYPAL_FIXED_FEE = 0.35;

    /**
     * @return array{
     *     title:string,
     *     credits:int,
     *     priceNet:float,
     *     priceGross:float,
     *     taxRate:float,
     *     paymentFee:float
     * }
     */
    public function calculate(CreditPackage $package, float $taxRate = self::DEFAULT_TAX_RATE): array
    {
        $priceNet = round((float) $package->getPrice(), 2);
        $taxRate = max(0.0, round($taxRate, 2));
        $priceGross = round($priceNet * (1 + ($taxRate / 100)), 2);
        $paymentFee = round(($priceGross * self::PAYPAL_PERCENT_FEE) + self::PAYPAL_FIXED_FEE, 2);

        return [
            'title' => (string) $package,
            'credits' => $package->getCredits(),
            'priceNet' => $priceNet,
            'priceGross' => $priceGross,
            'taxRate' => $taxRate,
            'paymentFee' => $paymentFee,
        ];
    }
}
