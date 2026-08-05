<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SuperadminDashboardLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuperadminDashboardLink>
 */
class SuperadminDashboardLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuperadminDashboardLink::class);
    }

    /**
     * @return list<SuperadminDashboardLink>
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('link')
            ->andWhere('link.active = :active')
            ->setParameter('active', true)
            ->orderBy('link.sortOrder', 'ASC')
            ->addOrderBy('link.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
