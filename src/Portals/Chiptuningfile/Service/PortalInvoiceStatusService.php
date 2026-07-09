<?php

declare(strict_types=1);

namespace App\Portals\Chiptuningfile\Service;

use App\Entity\Kunde;
use App\Entity\LexwareInvoice;
use App\Repository\KundeCreditTransactionRepository;

final class PortalInvoiceStatusService
{
    public function __construct(
        private readonly KundeCreditTransactionRepository $creditTransactionRepository,
    ) {
    }

    /**
     * @param iterable<LexwareInvoice> $invoices
     *
     * @return array<int, array{invoice:LexwareInvoice,status:string,rawStatus:string,paymentMethod:string,statusHint:?string,downloadUrl:?string,credits:int,amount:float,tax:float,total:float}>
     */
    public function buildInvoiceViews(iterable $invoices): array
    {
        $invoiceList = [];
        foreach ($invoices as $invoice) {
            if ($invoice instanceof LexwareInvoice) {
                $invoiceList[] = $invoice;
            }
        }

        if ([] === $invoiceList) {
            return [];
        }

        $transactionMetaByInvoiceId = $this->buildTransactionMetaByInvoiceId($invoiceList);

        return array_map(function (LexwareInvoice $invoice) use ($transactionMetaByInvoiceId): array {
            $invoiceId = trim((string) $invoice->getLexwareInvoiceId());
            $transactionMeta = $transactionMetaByInvoiceId[$invoiceId] ?? null;

            return [
                'invoice' => $invoice,
                ...$this->buildInvoiceView($invoice, $transactionMeta),
            ];
        }, $invoiceList);
    }

    /**
     * @return array{status:string,rawStatus:string,paymentMethod:string,statusHint:?string,downloadUrl:?string,credits:int,amount:float,tax:float,total:float}
     */
    public function buildInvoiceView(LexwareInvoice $invoice, ?array $transactionMeta = null): array
    {
        $rawStatus = mb_strtolower(trim((string) $invoice->getStatus()));
        $status = match ($rawStatus) {
            'paid', 'bezahlt' => 'paid',
            'cancelled', 'canceled', 'void', 'voided', 'storniert' => 'cancelled',
            default => 'pending',
        };

        $paymentMethod = trim((string) ($transactionMeta['paymentMethod'] ?? ''));
        $statusHint = null;
        if ('paypal' === strtolower($paymentMethod)) {
            $status = 'paid';
            if ('paid' !== $rawStatus) {
                $statusHint = 'Bezahlt via PayPal. Lexoffice liefert den Rechnungsstatus technisch weiter als offen.';
            }
        }

        $filePath = trim((string) ($invoice->getFilePath() ?? ''));
        $downloadUrl = '' !== $filePath
            ? (str_starts_with($filePath, 'http') ? $filePath : '/' . ltrim($filePath, '/'))
            : null;

        return [
            'status' => $status,
            'rawStatus' => '' !== $rawStatus ? $rawStatus : 'unknown',
            'paymentMethod' => $paymentMethod,
            'statusHint' => $statusHint,
            'downloadUrl' => $downloadUrl,
            'credits' => (int) ($transactionMeta['credits'] ?? 0),
            'amount' => (float) ($transactionMeta['amount'] ?? 0.0),
            'tax' => (float) ($transactionMeta['tax'] ?? 0.0),
            'total' => (float) ($transactionMeta['total'] ?? 0.0),
        ];
    }

    /**
     * @param iterable<LexwareInvoice> $invoices
     *
     * @return array<string, array{credits:int,amount:float,tax:float,total:float,paymentMethod:string}>
     */
    private function buildTransactionMetaByInvoiceId(iterable $invoices): array
    {
        $invoiceIds = [];
        $customers = [];

        foreach ($invoices as $invoice) {
            if (!$invoice instanceof LexwareInvoice) {
                continue;
            }

            $invoiceId = trim((string) $invoice->getLexwareInvoiceId());
            if ('' !== $invoiceId) {
                $invoiceIds[$invoiceId] = true;
            }

            $customer = $invoice->getKunde();
            if ($customer instanceof Kunde && null !== $customer->getId()) {
                $customers[(int) $customer->getId()] = $customer;
            }
        }

        if ([] === $invoiceIds || [] === $customers) {
            return [];
        }

        $metaByInvoiceId = [];
        foreach ($customers as $customer) {
            foreach ($this->creditTransactionRepository->findByKunde($customer, 0) as $transaction) {
                $invoiceId = $this->extractLexofficeInvoiceIdFromDescription((string) $transaction->getDescription());
                if (null === $invoiceId || !isset($invoiceIds[$invoiceId]) || isset($metaByInvoiceId[$invoiceId])) {
                    continue;
                }

                $priceNet = $this->toNullableFloat($transaction->getPriceNet()) ?? 0.0;
                $priceGross = $this->toNullableFloat($transaction->getPriceGross()) ?? 0.0;
                $tax = max(0.0, $priceGross - $priceNet);
                $credits = (int) $transaction->getAmount();
                if ($credits <= 0) {
                    $credits = $this->extractVorkasseCreditsFromDescription((string) $transaction->getDescription()) ?? 0;
                }

                $metaByInvoiceId[$invoiceId] = [
                    'credits' => max(0, $credits),
                    'amount' => $priceNet,
                    'tax' => $tax,
                    'total' => $priceGross,
                    'paymentMethod' => trim((string) ($transaction->getPaymentMethod() ?? '')),
                ];
            }
        }

        return $metaByInvoiceId;
    }

    private function extractLexofficeInvoiceIdFromDescription(string $description): ?string
    {
        if (preg_match('/Lexoffice Invoice:\s*([A-Za-z0-9\-]+)/i', $description, $matches) !== 1) {
            return null;
        }

        $value = trim((string) ($matches[1] ?? ''));

        return '' !== $value ? $value : null;
    }

    private function extractVorkasseCreditsFromDescription(string $description): ?int
    {
        if (preg_match('/Vorkasse Anfrage:\s*(\d+)\s*Credits/i', $description, $matches) !== 1) {
            return null;
        }

        $credits = (int) ($matches[1] ?? 0);

        return $credits > 0 ? $credits : null;
    }

    private function toNullableFloat(?string $value): ?float
    {
        if (null === $value) {
            return null;
        }

        $normalized = trim($value);
        if ('' === $normalized || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}
