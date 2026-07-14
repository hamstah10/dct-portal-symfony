<?php

namespace App\Repository;

use App\Entity\SuperadminPinnedVehicleType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuperadminPinnedVehicleType>
 */
class SuperadminPinnedVehicleTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuperadminPinnedVehicleType::class);
    }
}
