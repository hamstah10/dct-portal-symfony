<?php

namespace App\Repository;

use App\Entity\GearboxStageInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GearboxStageInfo>
 */
class GearboxStageInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GearboxStageInfo::class);
    }

    /**
     * @return array<int, GearboxStageInfo>
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.active = :active')
            ->setParameter('active', true)
            ->orderBy('i.sorting', 'ASC')
            ->addOrderBy('i.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
