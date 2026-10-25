<?php

namespace App\Repository;

use App\Entity\ShopCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopCategory>
 */
class ShopCategoryRepository extends ServiceEntityRepository
{
    use ProvidesNextUidTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopCategory::class);
    }

    /**
     * @return list<ShopCategory>
     */
    public function findVisibleCatalogCategories(): array
    {
        $now = time();

        return $this->createQueryBuilder('c')
            ->andWhere('c.deleted = :deleted')
            ->andWhere('c.hidden = :hidden')
            ->andWhere('c.sysLanguageUid = :language')
            ->andWhere('c.starttime = 0 OR c.starttime <= :now')
            ->andWhere('c.endtime = 0 OR c.endtime >= :now')
            ->setParameter('deleted', false)
            ->setParameter('hidden', false)
            ->setParameter('language', 0)
            ->setParameter('now', $now)
            ->orderBy('c.sorting', 'ASC')
            ->addOrderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
