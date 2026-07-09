<?php

namespace App\Repository;

use App\Entity\VehicleDataPool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VehicleDataPool>
 */
class VehicleDataPoolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VehicleDataPool::class);
    }

    public function findActiveByPoolKey(string $poolKey): ?VehicleDataPool
    {
        $normalized = trim($poolKey);
        if ('' === $normalized) {
            return null;
        }

        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.poolKey) = :poolKey')
            ->andWhere('p.active = :active')
            ->setParameter('poolKey', strtolower($normalized))
            ->setParameter('active', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
