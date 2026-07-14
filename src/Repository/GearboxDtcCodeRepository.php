<?php

namespace App\Repository;

use App\Entity\GearboxDtcCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GearboxDtcCode>
 */
class GearboxDtcCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GearboxDtcCode::class);
    }

    /**
     * @return array<int, GearboxDtcCode>
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.active = :active')
            ->setParameter('active', true)
            ->orderBy('c.sorting', 'ASC')
            ->addOrderBy('c.obdCode', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
