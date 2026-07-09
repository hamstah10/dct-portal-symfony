<?php

namespace App\Twig\Components;

use App\Controller\Admin\KundeCreditTransactionCrudController;
use App\Entity\KundeCreditTransaction;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('credit_history_search', template: 'components/credit_history_search.html.twig')]
final class CreditHistorySearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public int $limit = 40;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    /**
     * @return array<int, array{
     *   url:string,
     *   label:string,
     *   subLabel:?string,
     *   meta:?string,
     *   badge:?string,
     *   badgeTone:?string,
     *   amount:?string,
     *   amountTone:?string
     * }>
     */
    public function getResults(): array
    {
        $query = trim($this->query);
        $length = function_exists('mb_strlen') ? mb_strlen($query) : strlen($query);
        if ($length < 2) {
            return [];
        }

        $normalized = function_exists('mb_strtolower') ? mb_strtolower($query) : strtolower($query);
        $term = '%' . $normalized . '%';
        $numeric = $this->extractNumber($normalized);
        $kundeId = $this->extractKundeId($normalized);

        $qb = $this->entityManager->createQueryBuilder()
            ->select('t', 'k', 'f', 'u')
            ->from(KundeCreditTransaction::class, 't')
            ->leftJoin('t.kunde', 'k')
            ->leftJoin('k.firma', 'f')
            ->leftJoin('t.createdBy', 'u')
            ->orderBy('t.createdAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setMaxResults(max(1, $this->limit));

        $expr = $qb->expr()->orX(
            $qb->expr()->like('LOWER(COALESCE(k.kundennummer, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.vorname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.nachname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.email, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.firmenname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(f.name, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(f.kuerzel, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(t.transactionType, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(t.paymentMethod, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(t.description, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(u.email, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(u.vorname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(u.nachname, \'\'))', ':term')
        );

        if (null !== $numeric) {
            $expr->add($qb->expr()->eq('t.id', ':numeric'));
            $expr->add($qb->expr()->eq('t.amount', ':numeric'));
            $expr->add($qb->expr()->eq('t.balanceBefore', ':numeric'));
            $expr->add($qb->expr()->eq('t.balanceAfter', ':numeric'));
            $qb->setParameter('numeric', $numeric);
        }

        if (null !== $kundeId) {
            $expr->add($qb->expr()->eq('k.id', ':kundeId'));
            $qb->setParameter('kundeId', $kundeId);
        }

        $items = $qb
            ->andWhere($expr)
            ->setParameter('term', $term)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($items as $transaction) {
            if (!$transaction instanceof KundeCreditTransaction) {
                continue;
            }

            $type = $transaction->getTransactionType();

            $results[] = [
                'url' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(KundeCreditTransactionCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($transaction->getId())
                    ->generateUrl(),
                'label' => $this->buildLabel($transaction),
                'subLabel' => $this->buildSubLabel($transaction),
                'meta' => $transaction->getCreatedAt()?->format('d.m.Y H:i'),
                'badge' => $this->typeLabel($type),
                'badgeTone' => $this->typeTone($type),
                'amount' => $this->amountLabel($transaction->getAmount()),
                'amountTone' => $this->amountTone($transaction->getAmount()),
            ];
        }

        return $results;
    }

    private function extractNumber(string $query): ?int
    {
        if ('' === $query) {
            return null;
        }

        if (preg_match('/^#?([+-]?\\d+)$/', $query, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function extractKundeId(string $query): ?int
    {
        if ('' === $query) {
            return null;
        }

        if (preg_match('/^k-?(\\d+)$/i', $query, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function buildLabel(KundeCreditTransaction $transaction): string
    {
        $kunde = $transaction->getKunde();
        $parts = [];

        $kundennummer = trim((string) ($kunde?->getKundennummer() ?? ''));
        if ('' !== $kundennummer) {
            $parts[] = $kundennummer;
        }

        $name = '';
        if ($kunde) {
            $name = trim(sprintf('%s %s', (string) ($kunde->getVorname() ?? ''), (string) ($kunde->getNachname() ?? '')));
        }

        if ('' !== $name) {
            $parts[] = $name;
        } else {
            $firmaDisplay = trim((string) ($kunde?->getFirmenname() ?? ''));
            if ('' !== $firmaDisplay) {
                $parts[] = $firmaDisplay;
            }
        }

        if (!$parts) {
            $parts[] = 'Buchung #' . (string) ($transaction->getId() ?? '');
        }

        return implode(' · ', $parts);
    }

    private function buildSubLabel(KundeCreditTransaction $transaction): ?string
    {
        $parts = [];

        $kunde = $transaction->getKunde();
        if ($kunde) {
            $email = trim((string) ($kunde->getEmail() ?? ''));
            if ('' !== $email) {
                $parts[] = $email;
            }

            $firmaName = trim((string) ($kunde->getFirma()?->getName() ?? ''));
            $firmaDisplay = trim((string) ($kunde->getFirmenname() ?? ''));
            if ('' !== $firmaName) {
                $parts[] = $firmaName;
            } elseif ('' !== $firmaDisplay) {
                $parts[] = $firmaDisplay;
            }
        }

        $paymentMethod = trim((string) ($transaction->getPaymentMethod() ?? ''));
        if ('' !== $paymentMethod) {
            $parts[] = 'Zahlart: ' . $paymentMethod;
        }

        $description = trim((string) ($transaction->getDescription() ?? ''));
        if ('' !== $description) {
            $parts[] = $this->truncate($description, 80);
        }

        return $parts ? implode(' | ', $parts) : null;
    }

    private function amountLabel(int $amount): string
    {
        $prefix = $amount >= 0 ? '+' : '';
        return $prefix . $amount . ' Credits';
    }

    private function amountTone(int $amount): string
    {
        return $amount > 0 ? 'success' : ($amount < 0 ? 'danger' : 'secondary');
    }

    private function typeLabel(string $type): string
    {
        $type = trim($type);

        return match ($type) {
            KundeCreditTransaction::TYPE_ADMIN_TOPUP => 'Admin Aufladung',
            KundeCreditTransaction::TYPE_CUSTOMER_TOPUP => 'Kunde Aufladung',
            KundeCreditTransaction::TYPE_USAGE => 'Verbrauch',
            KundeCreditTransaction::TYPE_MANUAL_ADJUSTMENT => 'Manuelle Anpassung',
            KundeCreditTransaction::TYPE_REVERSAL => 'Storno',
            default => '' !== $type ? $type : 'Unbekannt',
        };
    }

    private function typeTone(string $type): string
    {
        return match ($type) {
            KundeCreditTransaction::TYPE_ADMIN_TOPUP => 'primary',
            KundeCreditTransaction::TYPE_CUSTOMER_TOPUP => 'success',
            KundeCreditTransaction::TYPE_USAGE => 'warning',
            KundeCreditTransaction::TYPE_MANUAL_ADJUSTMENT => 'secondary',
            KundeCreditTransaction::TYPE_REVERSAL => 'dark',
            default => 'secondary',
        };
    }

    private function truncate(string $text, int $max): string
    {
        $text = trim($text);
        if ('' === $text) {
            return $text;
        }

        $length = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
        if ($length <= $max) {
            return $text;
        }

        $slice = function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);

        return rtrim($slice) . '...';
    }
}
