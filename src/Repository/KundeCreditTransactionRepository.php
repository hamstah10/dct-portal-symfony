<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Kunde;
use App\Entity\KundeCreditTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<KundeCreditTransaction>
 */
class KundeCreditTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KundeCreditTransaction::class);
    }

    /**
     * @return array<int, KundeCreditTransaction>
     */
    public function findByKunde(Kunde $kunde, int $limit = 100): array
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->andWhere('t.kunde = :kunde')
            ->setParameter('kunde', $kunde)
            ->orderBy('t.createdAt', 'DESC')
            ->addOrderBy('t.id', 'DESC');

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function hasReversalFor(KundeCreditTransaction $transaction): bool
    {
        $count = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.reversalOf = :original')
            ->setParameter('original', $transaction)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    public function hasTransactionWithPaypalOrderId(string $paypalOrderId): bool
    {
        $paypalOrderId = trim($paypalOrderId);
        if ('' === $paypalOrderId) {
            return false;
        }

        $count = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.paymentMethod = :paymentMethod')
            ->andWhere('t.description LIKE :orderNeedle')
            ->setParameter('paymentMethod', 'paypal')
            ->setParameter('orderNeedle', '%PayPal Order: ' . $paypalOrderId . '%')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    public function hasTransactionWithPaymentReference(string $reference): bool
    {
        $reference = trim($reference);
        if ('' === $reference) {
            return false;
        }

        $count = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.paymentReference = :reference')
            ->setParameter('reference', $reference)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    public function hasSettlementForPrepaymentTransaction(int $sourceTransactionId): bool
    {
        if ($sourceTransactionId <= 0) {
            return false;
        }

        $count = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.paymentMethod = :paymentMethod')
            ->andWhere('t.amount > 0')
            ->andWhere('t.description LIKE :needle')
            ->setParameter('paymentMethod', 'vorkasse')
            ->setParameter('needle', '%Ursprung Buchung: #' . $sourceTransactionId . '%')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }
}
