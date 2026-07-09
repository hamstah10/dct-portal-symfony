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
}
