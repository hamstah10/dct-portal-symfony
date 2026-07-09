<?php

namespace App\Repository;

use App\Entity\VehiclePlatformAlias;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VehiclePlatformAlias>
 */
class VehiclePlatformAliasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VehiclePlatformAlias::class);
    }

    /**
     * Finds active aliases whose source brand matches the given brand (case-insensitive).
     *
     * @return array<int, VehiclePlatformAlias>
     */
    public function findActiveBySourceBrand(string $brand): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isActive = :active')
            ->andWhere('LOWER(a.sourceBrand) = :brand')
            ->setParameter('active', true)
            ->setParameter('brand', mb_strtolower(trim($brand)))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, VehiclePlatformAlias>
     */
    public function findAllActive(): array
    {
        return $this->findBy(['isActive' => true]);
    }
}
