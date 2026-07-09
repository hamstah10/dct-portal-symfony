<?php

declare(strict_types=1);

namespace App\Portals\No1tuningfiles\Service;

use App\Entity\CreditPackage;
use App\Entity\Kunde;
use App\Entity\KundeCreditTransaction;
use App\Repository\CreditPackageRepository;
use App\Repository\KundeCreditTransactionRepository;
use App\Service\CreditPackagePricingCalculator;
use Doctrine\ORM\EntityManagerInterface;

final class PortalCreditTopupService
{
    public function __construct(
        private readonly CreditPackageRepository $creditPackageRepository,
        private readonly KundeCreditTransactionRepository $transactionRepository,
        private readonly CreditPackagePricingCalculator $pricingCalculator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array{
     *     transaction:KundeCreditTransaction,
     *     package:CreditPackage,
     *     pricing:array{
     *         title:string,
     *         credits:int,
     *         priceNet:float,
     *         priceGross:float,
     *         taxRate:float,
     *         paymentFee:float
     *     }
     * }
     */
    public function bookCapturedPaypalOrder(Kunde $customer, array $captureData): array
    {
        $orderId = trim((string) ($captureData['id'] ?? ''));
        if ('' === $orderId) {
            throw new \RuntimeException('PayPal Order-ID fehlt.');
        }

        if ($this->transactionRepository->hasTransactionWithPaypalOrderId($orderId)) {
            throw new \RuntimeException('Diese PayPal-Zahlung wurde bereits verbucht.');
        }

        $purchaseUnit = $captureData['purchase_units'][0] ?? null;
        if (!is_array($purchaseUnit)) {
            throw new \RuntimeException('PayPal Purchase Unit fehlt.');
        }

        $customId = (string) ($purchaseUnit['custom_id'] ?? '');
        $package = null;
        $ids = $this->tryParseCustomId($customId);

        $customerId = $customer->getId();
        if (null === $customerId) {
            throw new \RuntimeException('Kunde ist nicht persistent.');
        }

        if (null !== $ids && $ids['customerId'] !== $customerId) {
            throw new \RuntimeException('Die PayPal-Zahlung gehoert nicht zu diesem Kundenkonto.');
        }

        if (null !== $ids) {
            $resolved = $this->creditPackageRepository->find($ids['packageId']);
            if ($resolved instanceof CreditPackage) {
                $package = $resolved;
            }
        }

        if (!$package instanceof CreditPackage) {
            $package = $this->resolvePackageFromPurchaseUnit($purchaseUnit);
        }

        $capture = $purchaseUnit['payments']['captures'][0] ?? null;
        if (!is_array($capture)) {
            throw new \RuntimeException('PayPal Capture-Daten fehlen.');
        }

        if (($capture['status'] ?? null) !== 'COMPLETED') {
            throw new \RuntimeException('Die PayPal-Zahlung ist noch nicht abgeschlossen.');
        }

        $currencyCode = (string) ($capture['amount']['currency_code'] ?? $purchaseUnit['amount']['currency_code'] ?? '');
        if ('EUR' !== strtoupper($currencyCode)) {
            throw new \RuntimeException('Unerwartete PayPal-Waehrung.');
        }

        $pricing = $this->pricingCalculator->calculate($package);
        $paidAmount = round((float) ($capture['amount']['value'] ?? $purchaseUnit['amount']['value'] ?? 0), 2);
        if (abs($paidAmount - $pricing['priceGross']) > 0.01) {
            throw new \RuntimeException('Der PayPal-Betrag stimmt nicht mit dem Paketpreis ueberein.');
        }

        $balanceBefore = $customer->getCredits();
        $balanceAfter = $balanceBefore + $package->getCredits();

        $description = sprintf(
            'Paket: %s (%d Credits)%sPayPal Order: %s%sPayPal Capture: %s',
            (string) $package,
            $package->getCredits(),
            PHP_EOL,
            $orderId,
            PHP_EOL,
            (string) ($capture['id'] ?? ''),
        );

        $transaction = (new KundeCreditTransaction())
            ->setKunde($customer)
            ->setAmount($package->getCredits())
            ->setTransactionType(KundeCreditTransaction::TYPE_CUSTOMER_TOPUP)
            ->setDescription($description)
            ->setPaymentMethod('paypal')
            ->setPriceNet(number_format($pricing['priceNet'], 2, '.', ''))
            ->setPriceGross(number_format($pricing['priceGross'], 2, '.', ''))
            ->setTaxRate(number_format($pricing['taxRate'], 2, '.', ''))
            ->setPaymentFee(number_format($pricing['paymentFee'], 2, '.', ''))
            ->setBalanceBefore($balanceBefore)
            ->setBalanceAfter($balanceAfter);

        $customer->setCredits($balanceAfter);

        $this->entityManager->persist($transaction);
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return [
            'transaction' => $transaction,
            'package' => $package,
            'pricing' => $pricing,
        ];
    }

    /**
     * @param array{priceNet:float,priceGross:float,taxRate:float,paymentFee?:float} $pricing
     */
    public function bookCapturedPaypalCredits(
        Kunde $customer,
        array $captureData,
        int $credits,
        array $pricing,
        string $descriptionPrefix = 'Credit-Kauf'
    ): KundeCreditTransaction {
        $orderId = trim((string) ($captureData['id'] ?? ''));
        if ('' === $orderId) {
            throw new \RuntimeException('PayPal Order-ID fehlt.');
        }

        if ($this->transactionRepository->hasTransactionWithPaypalOrderId($orderId)) {
            throw new \RuntimeException('Diese PayPal-Zahlung wurde bereits verbucht.');
        }

        if ($credits <= 0) {
            throw new \RuntimeException('Ungueltige Credit-Anzahl.');
        }

        $purchaseUnit = $captureData['purchase_units'][0] ?? null;
        if (!is_array($purchaseUnit)) {
            throw new \RuntimeException('PayPal Purchase Unit fehlt.');
        }

        $capture = $purchaseUnit['payments']['captures'][0] ?? null;
        if (!is_array($capture)) {
            throw new \RuntimeException('PayPal Capture-Daten fehlen.');
        }

        if (($capture['status'] ?? null) !== 'COMPLETED') {
            throw new \RuntimeException('Die PayPal-Zahlung ist noch nicht abgeschlossen.');
        }

        $currencyCode = (string) ($capture['amount']['currency_code'] ?? $purchaseUnit['amount']['currency_code'] ?? '');
        if ('EUR' !== strtoupper($currencyCode)) {
            throw new \RuntimeException('Unerwartete PayPal-Waehrung.');
        }

        $paidAmount = round((float) ($capture['amount']['value'] ?? $purchaseUnit['amount']['value'] ?? 0), 2);
        $expectedGross = round((float) ($pricing['priceGross'] ?? 0.0), 2);
        if ($expectedGross <= 0.0 || abs($paidAmount - $expectedGross) > 0.01) {
            throw new \RuntimeException('Der PayPal-Betrag stimmt nicht mit dem berechneten Credit-Preis ueberein.');
        }

        $balanceBefore = (int) $customer->getCredits();
        $balanceAfter = $balanceBefore + $credits;
        $description = sprintf(
            '%s%s%d Credits%sPayPal Order: %s%sPayPal Capture: %s',
            trim($descriptionPrefix),
            PHP_EOL,
            $credits,
            PHP_EOL,
            $orderId,
            PHP_EOL,
            (string) ($capture['id'] ?? ''),
        );

        $transaction = (new KundeCreditTransaction())
            ->setKunde($customer)
            ->setAmount($credits)
            ->setTransactionType(KundeCreditTransaction::TYPE_CUSTOMER_TOPUP)
            ->setDescription($description)
            ->setPaymentMethod('paypal')
            ->setPriceNet(number_format((float) ($pricing['priceNet'] ?? 0.0), 2, '.', ''))
            ->setPriceGross(number_format($expectedGross, 2, '.', ''))
            ->setTaxRate(number_format((float) ($pricing['taxRate'] ?? 19.0), 2, '.', ''))
            ->setPaymentFee(number_format((float) ($pricing['paymentFee'] ?? 0.0), 2, '.', ''))
            ->setBalanceBefore($balanceBefore)
            ->setBalanceAfter($balanceAfter);

        $customer->setCredits($balanceAfter);

        $this->entityManager->persist($transaction);
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $transaction;
    }

    /**
     * @param array{priceNet:float,priceGross:float,taxRate:float,paymentFee?:float} $pricing
     * @param array<string, mixed> $intentData
     */
    public function bookCapturedStripePayment(
        Kunde $customer,
        CreditPackage $package,
        array $pricing,
        array $intentData
    ): KundeCreditTransaction {
        $intentId = trim((string) ($intentData['id'] ?? ''));
        if ('' === $intentId) {
            throw new \RuntimeException('Stripe PaymentIntent fehlt.');
        }

        $reference = 'stripe:' . $intentId;
        if ($this->transactionRepository->hasTransactionWithPaymentReference($reference)) {
            throw new \RuntimeException('Diese Stripe-Zahlung wurde bereits verbucht.');
        }

        if ((string) ($intentData['status'] ?? '') !== 'succeeded') {
            throw new \RuntimeException('Die Stripe-Zahlung ist noch nicht abgeschlossen.');
        }

        $currency = strtolower(trim((string) ($intentData['currency'] ?? '')));
        if ('eur' !== $currency) {
            throw new \RuntimeException('Unerwartete Stripe-Währung.');
        }

        $amountReceived = (int) ($intentData['amount_received'] ?? $intentData['amount'] ?? 0);
        $expectedGrossCents = (int) round(($pricing['priceGross'] ?? 0.0) * 100);
        if ($expectedGrossCents <= 0 || abs($amountReceived - $expectedGrossCents) > 1) {
            throw new \RuntimeException('Der Stripe-Betrag stimmt nicht mit dem Paketpreis überein.');
        }

        $balanceBefore = (int) $customer->getCredits();
        $balanceAfter = $balanceBefore + $package->getCredits();

        $description = sprintf(
            'Paket: %s (%d Credits)%sStripe PaymentIntent: %s',
            (string) $package,
            $package->getCredits(),
            PHP_EOL,
            $intentId,
        );

        $transaction = (new KundeCreditTransaction())
            ->setKunde($customer)
            ->setAmount($package->getCredits())
            ->setTransactionType(KundeCreditTransaction::TYPE_CUSTOMER_TOPUP)
            ->setDescription($description)
            ->setPaymentMethod('stripe')
            ->setPaymentReference($reference)
            ->setPriceNet(number_format((float) ($pricing['priceNet'] ?? 0.0), 2, '.', ''))
            ->setPriceGross(number_format((float) ($pricing['priceGross'] ?? 0.0), 2, '.', ''))
            ->setTaxRate(number_format((float) ($pricing['taxRate'] ?? 19.0), 2, '.', ''))
            ->setPaymentFee(number_format((float) ($pricing['paymentFee'] ?? 0.0), 2, '.', ''))
            ->setBalanceBefore($balanceBefore)
            ->setBalanceAfter($balanceAfter);

        $customer->setCredits($balanceAfter);

        $this->entityManager->persist($transaction);
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $transaction;
    }

    /**
     * @return array{packageId:int,customerId:int}
     */
    private function tryParseCustomId(string $customId): ?array
    {
        if (preg_match('/^cp(\d+)-c(\d+)$/', $customId, $matches)) {
            return [
                'packageId' => (int) $matches[1],
                'customerId' => (int) $matches[2],
            ];
        }

        if (preg_match('/^credit_package:(\d+);customer:(\d+)$/', $customId, $legacyMatches)) {
            return [
                'packageId' => (int) $legacyMatches[1],
                'customerId' => (int) $legacyMatches[2],
            ];
        }

        return null;
    }

    private function resolvePackageFromPurchaseUnit(array $purchaseUnit): CreditPackage
    {
        $unitAmount = round((float) ($purchaseUnit['amount']['value'] ?? 0.0), 2);
        $captureAmount = round((float) ($purchaseUnit['payments']['captures'][0]['amount']['value'] ?? 0.0), 2);
        $paidAmount = $captureAmount > 0.0 ? $captureAmount : $unitAmount;

        if ($paidAmount <= 0.0) {
            throw new \RuntimeException('PayPal-Betrag fehlt oder ist ungueltig.');
        }

        $candidates = [];
        foreach ($this->creditPackageRepository->findBy([], ['credits' => 'ASC']) as $candidate) {
            if (!$candidate instanceof CreditPackage) {
                continue;
            }

            if ($candidate->getCredits() <= 0 || (float) $candidate->getPrice() <= 0) {
                continue;
            }

            $pricing = $this->pricingCalculator->calculate($candidate);
            if (abs($pricing['priceGross'] - $paidAmount) <= 0.01) {
                $candidates[] = $candidate;
            }
        }

        if (1 === count($candidates)) {
            return $candidates[0];
        }

        if (count($candidates) > 1) {
            $description = (string) ($purchaseUnit['description'] ?? '');
            if (preg_match('/(\d+)\s*Credits/i', $description, $matches) === 1) {
                $descriptionCredits = (int) ($matches[1] ?? 0);
                $filtered = array_values(array_filter(
                    $candidates,
                    static fn (CreditPackage $candidate): bool => $candidate->getCredits() === $descriptionCredits
                ));

                if (1 === count($filtered)) {
                    return $filtered[0];
                }
            }

            throw new \RuntimeException('PayPal Paket konnte nicht eindeutig zugeordnet werden.');
        }

        throw new \RuntimeException('Kein passendes Credit-Paket zum PayPal-Betrag gefunden.');
    }
}
