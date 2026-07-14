<?php

namespace App\Repository;

use App\Entity\GearboxStageInfoFeature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GearboxStageInfoFeature>
 */
class GearboxStageInfoFeatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GearboxStageInfoFeature::class);
    }
}
