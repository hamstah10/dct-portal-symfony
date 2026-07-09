<?php

namespace App\Repository;

use App\Entity\AboAuswahlPreisstaffel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AboAuswahlPreisstaffel>
 */
class AboAuswahlPreisstaffelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AboAuswahlPreisstaffel::class);
    }
}
