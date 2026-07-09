<?php

namespace App\Portals\No1tuningfiles\Repository;

use App\Entity\Kunde;
use App\Portals\No1tuningfiles\Entity\PortalUploadJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PortalUploadJob>
 */
final class PortalUploadJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PortalUploadJob::class);
    }

    /**
     * @return list<PortalUploadJob>
     */
    public function findRecentForCustomer(Kunde $kunde, int $limit = 10): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.kunde = :kunde')
            ->setParameter('kunde', $kunde)
            ->orderBy('j.createdAt', 'DESC')
            ->setMaxResults(max(1, $limit))
            ->getQuery()
            ->getResult();
    }

    public function countForCustomer(Kunde $kunde): int
    {
        return (int) $this->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->andWhere('j.kunde = :kunde')
            ->setParameter('kunde', $kunde)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<string, int>
     */
    public function countByStatusForCustomer(Kunde $kunde): array
    {
        $rows = $this->createQueryBuilder('j')
            ->select('j.status AS status, COUNT(j.id) AS total')
            ->andWhere('j.kunde = :kunde')
            ->setParameter('kunde', $kunde)
            ->groupBy('j.status')
            ->getQuery()
            ->getArrayResult();

        $totals = [];
        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
            if ('' === $status) {
                continue;
            }

            $totals[$status] = (int) ($row['total'] ?? 0);
        }

        return $totals;
    }

    public function countCreatedSinceForCustomer(Kunde $kunde, \DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('j')
            ->select('COUNT(j.id)')
            ->andWhere('j.kunde = :kunde')
            ->andWhere('j.createdAt >= :since')
            ->setParameter('kunde', $kunde)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return list<PortalUploadJob>
     */
    public function findCreatedSinceForCustomer(Kunde $kunde, \DateTimeImmutable $since, int $limit = 250): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.kunde = :kunde')
            ->andWhere('j.createdAt >= :since')
            ->setParameter('kunde', $kunde)
            ->setParameter('since', $since)
            ->orderBy('j.createdAt', 'ASC')
            ->setMaxResults(max(1, $limit))
            ->getQuery()
            ->getResult();
    }
}
