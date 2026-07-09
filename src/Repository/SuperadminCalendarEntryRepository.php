<?php

namespace App\Repository;

use App\Entity\SuperadminCalendarEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuperadminCalendarEntry>
 */
class SuperadminCalendarEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuperadminCalendarEntry::class);
    }
}
