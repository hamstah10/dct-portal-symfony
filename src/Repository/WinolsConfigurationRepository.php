<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Portal;
use App\Entity\WinolsConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WinolsConfiguration>
 */
class WinolsConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WinolsConfiguration::class);
    }

    public function findActiveForPortal(Portal $portal): ?WinolsConfiguration
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.portals', 'p')
            ->andWhere('c.active = true')
            ->andWhere('p = :portal')
            ->setParameter('portal', $portal)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<WinolsConfiguration>
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.active = true')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
