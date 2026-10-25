<?php

namespace App\Repository;

use App\Entity\ShopProduct;
use App\Entity\ShopProductLink;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopProductLink>
 */
class ShopProductLinkRepository extends ServiceEntityRepository
{
    use ProvidesNextUidTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopProductLink::class);
    }

    /**
     * @return list<ShopProductLink>
     */
    public function findVisibleByProduct(ShopProduct $product): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.product = :product')
            ->andWhere('l.deleted = :deleted')
            ->andWhere('l.hidden = :hidden')
            ->setParameter('product', $product)
            ->setParameter('deleted', false)
            ->setParameter('hidden', false)
            ->orderBy('l.sorting', 'ASC')
            ->addOrderBy('l.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
