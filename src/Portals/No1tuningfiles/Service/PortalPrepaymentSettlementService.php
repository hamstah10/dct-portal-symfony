<?php

declare(strict_types=1);

namespace App\Portals\No1tuningfiles\Service;

use App\Entity\Kunde;
use App\Entity\KundeCreditTransaction;
use App\Entity\LexwareInvoice;
use App\Entity\User;
use App\Repository\KundeCreditTransactionRepository;
use App\Repository\LexwareInvoiceRepository;
use App\Service\Lexware\LexwareService;
use Doctrine\ORM\EntityManagerInterface;

final class PortalPrepaymentSettlementService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LexwareService $lexwareService,
        private readonly LexwareInvoiceRepository $lexwareInvoiceRepository,
        private readonly KundeCreditTransactionRepository $creditTransactionRepository,
    ) {
    }

    /**
     * @return array{
     *   settlement:KundeCreditTransaction,
     *   creditsAdded:int,
     *   invoiceId:?string,
     *   lexofficeSynced:bool,
     *   lexofficeMessage:?string
     * }
     */
    public function settlePendingPrepayment(KundeCreditTransaction $pendingTransaction, ?User $actor = null): array
    {
        if (!$this->isPendingPrepayment($pendingTransaction)) {
            throw new \RuntimeException('Diese Buchung ist keine offene Vorkasse-Anfrage.');
        }

        $customer = $pendingTransaction->getKunde();
        if (!$customer instanceof Kunde || null === $customer->getId()) {
            throw new \RuntimeException('Kunde zur Vorkasse-Buchung wurde nicht gefunden.');
        }

        $description = (string) ($pendingTransaction->getDescription() ?? '');
        $requestedCredits = $this->extractRequestedCredits($description);
        if ($requestedCredits <= 0) {
            throw new \RuntimeException('Die Credit-Anzahl konnte aus der Vorkasse-Buchung nicht gelesen werden.');
        }

        if ($this->creditTransactionRepository->hasSettlementForPrepaymentTransaction((int) $pendingTransaction->getId())) {
            throw new \RuntimeException('Diese Vorkasse wurde bereits als bezahlt verbucht.');
        }

        $invoiceId = $this->extractInvoiceId($description);
        $invoiceRecord = null;
        $lexofficeSynced = false;
        $lexofficeMessage = null;

        if (null !== $invoiceId) {
            try {
                $this->lexwareService->invoices()->markAsPaid($invoiceId);
                $lexofficeSynced = true;
            } catch (\Throwable $exception) {
                $lexofficeMessage = trim($exception->getMessage());
            }

            $invoiceRecord = $this->findInvoiceRecord($customer, $invoiceId);
            if ($invoiceRecord instanceof LexwareInvoice) {
                $invoiceRecord->setStatus('paid');
                $this->entityManager->persist($invoiceRecord);
            }
        }

        $balanceBefore = (int) $customer->getCredits();
        $balanceAfter = $balanceBefore + $requestedCredits;
        $customer->setCredits($balanceAfter);

        $settlementDescription = array_filter([
            sprintf('Vorkasse Zahlungseingang bestaetigt (%d Credits)', $requestedCredits),
            sprintf('Ursprung Buchung: #%d', (int) $pendingTransaction->getId()),
            null !== $invoiceId ? ('Lexoffice Invoice: ' . $invoiceId) : null,
            null !== $invoiceId ? ('Lexoffice Sync: ' . ($lexofficeSynced ? 'ok' : 'failed')) : null,
            !$lexofficeSynced && null !== $lexofficeMessage ? ('Lexoffice Sync Message: ' . $lexofficeMessage) : null,
        ], static fn (?string $line): bool => null !== $line && '' !== trim($line));

        $settlement = (new KundeCreditTransaction())
            ->setKunde($customer)
            ->setAmount($requestedCredits)
            ->setTransactionType(KundeCreditTransaction::TYPE_CUSTOMER_TOPUP)
            ->setDescription(implode(PHP_EOL, $settlementDescription))
            ->setPaymentMethod('vorkasse')
            ->setPriceNet($pendingTransaction->getPriceNet())
            ->setPriceGross($pendingTransaction->getPriceGross())
            ->setTaxRate($pendingTransaction->getTaxRate())
            ->setPaymentFee($pendingTransaction->getPaymentFee())
            ->setBalanceBefore($balanceBefore)
            ->setBalanceAfter($balanceAfter);

        $managedActor = $this->resolveManagedUser($actor);
        if ($managedActor instanceof User) {
            $settlement->setCreatedBy($managedActor);
        }

        $pendingTransaction->setDescription($this->buildPaidDescription($description));
        $pendingTransaction->setBalanceBefore($balanceBefore);
        $pendingTransaction->setBalanceAfter($balanceBefore);

        $this->entityManager->persist($customer);
        $this->entityManager->persist($pendingTransaction);
        $this->entityManager->persist($settlement);
        $this->entityManager->flush();

        return [
            'settlement' => $settlement,
            'creditsAdded' => $requestedCredits,
            'invoiceId' => $invoiceId,
            'lexofficeSynced' => $lexofficeSynced,
            'lexofficeMessage' => $lexofficeMessage,
        ];
    }

    private function resolveManagedUser(?User $actor): ?User
    {
        if (!$actor instanceof User) {
            return null;
        }

        if ($this->entityManager->contains($actor)) {
            return $actor;
        }

        $id = $actor->getId();
        if (!is_int($id) || $id <= 0) {
            return null;
        }

        $managed = $this->entityManager->find(User::class, $id);

        return $managed instanceof User ? $managed : null;
    }

    public function isPendingPrepayment(KundeCreditTransaction $transaction): bool
    {
        $isPrepayment = 'vorkasse' === strtolower(trim((string) ($transaction->getPaymentMethod() ?? '')));
        if (!$isPrepayment) {
            return false;
        }

        $description = strtolower((string) ($transaction->getDescription() ?? ''));
        if (!str_contains($description, 'vorkasse anfrage')) {
            return false;
        }

        return str_contains($description, 'status: pending');
    }

    private function extractRequestedCredits(string $description): int
    {
        if (preg_match('/Vorkasse Anfrage:\s*(\d+)\s*Credits/i', $description, $matches) !== 1) {
            return 0;
        }

        return max(0, (int) ($matches[1] ?? 0));
    }

    private function extractInvoiceId(string $description): ?string
    {
        if (preg_match('/Lexoffice Invoice:\s*([A-Za-z0-9\-]+)/i', $description, $matches) !== 1) {
            return null;
        }

        $invoiceId = trim((string) ($matches[1] ?? ''));

        return '' !== $invoiceId ? $invoiceId : null;
    }

    private function findInvoiceRecord(Kunde $customer, string $invoiceId): ?LexwareInvoice
    {
        $invoice = $this->lexwareInvoiceRepository->findOneBy([
            'kunde' => $customer,
            'lexwareInvoiceId' => $invoiceId,
        ]);
        if ($invoice instanceof LexwareInvoice) {
            return $invoice;
        }

        $fallback = $this->lexwareInvoiceRepository->findOneBy([
            'lexwareInvoiceId' => $invoiceId,
        ]);

        return $fallback instanceof LexwareInvoice ? $fallback : null;
    }

    private function buildPaidDescription(string $description): string
    {
        $lines = preg_split('/\R/', $description) ?: [];
        $updated = [];
        $statusReplaced = false;

        foreach ($lines as $line) {
            if (preg_match('/^\s*Status\s*:/i', $line) === 1) {
                $updated[] = 'Status: paid';
                $statusReplaced = true;
                continue;
            }

            $updated[] = rtrim($line);
        }

        if (!$statusReplaced) {
            $updated[] = 'Status: paid';
        }

        $hasPaidAt = false;
        foreach ($updated as $line) {
            if (preg_match('/^\s*Bezahlt am\s*:/i', $line) === 1) {
                $hasPaidAt = true;
                break;
            }
        }
        if (!$hasPaidAt) {
            $updated[] = 'Bezahlt am: ' . (new \DateTimeImmutable())->format('d.m.Y H:i');
        }

        return trim(implode(PHP_EOL, array_values(array_filter(
            $updated,
            static fn (string $line): bool => '' !== trim($line)
        ))));
    }
}
