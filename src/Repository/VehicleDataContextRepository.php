<?php

namespace App\Repository;

use App\Entity\VehicleDataContext;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VehicleDataContext>
 */
class VehicleDataContextRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VehicleDataContext::class);
    }

    public function findActiveByContextKey(string $contextKey): ?VehicleDataContext
    {
        $normalized = trim($contextKey);
        if ('' === $normalized) {
            return null;
        }

        return $this->createQueryBuilder('c')
            ->andWhere('LOWER(c.contextKey) = :contextKey')
            ->andWhere('c.active = :active')
            ->setParameter('contextKey', strtolower($normalized))
            ->setParameter('active', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
