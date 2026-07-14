<?php

namespace App\Repository;

use App\Entity\GearboxStageOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GearboxStageOption>
 */
class GearboxStageOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GearboxStageOption::class);
    }

    /**
     * @return array<int, GearboxStageOption>
     */
    public function findForGearboxName(string $gearboxName): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.gearboxName = :gearboxName')
            ->andWhere('o.active = :active')
            ->setParameter('gearboxName', $gearboxName)
            ->setParameter('active', true)
            ->orderBy('o.sorting', 'ASC')
            ->addOrderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
