<?php

namespace App\Repository;

use App\Entity\ShopManufacturer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopManufacturer>
 */
class ShopManufacturerRepository extends ServiceEntityRepository
{
    use ProvidesNextUidTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopManufacturer::class);
    }

    /**
     * @return list<ShopManufacturer>
     */
    public function findVisibleCatalogManufacturers(): array
    {
        $now = time();

        return $this->createQueryBuilder('m')
            ->andWhere('m.deleted = :deleted')
            ->andWhere('m.hidden = :hidden')
            ->andWhere('m.sysLanguageUid = :language')
            ->andWhere('m.starttime = 0 OR m.starttime <= :now')
            ->andWhere('m.endtime = 0 OR m.endtime >= :now')
            ->setParameter('deleted', false)
            ->setParameter('hidden', false)
            ->setParameter('language', 0)
            ->setParameter('now', $now)
            ->orderBy('m.sorting', 'ASC')
            ->addOrderBy('m.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
