<?php

namespace App\Repository;

use App\Entity\ShopProductDeliveryScope;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopProductDeliveryScope>
 */
class ShopProductDeliveryScopeRepository extends ServiceEntityRepository
{
    use ProvidesNextUidTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopProductDeliveryScope::class);
    }
}
