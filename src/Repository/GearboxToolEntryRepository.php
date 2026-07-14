<?php

namespace App\Repository;

use App\Entity\GearboxToolEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GearboxToolEntry>
 */
class GearboxToolEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GearboxToolEntry::class);
    }
}
