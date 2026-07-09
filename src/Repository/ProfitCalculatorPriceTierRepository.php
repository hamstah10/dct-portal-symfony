<?php

namespace App\Repository;

use App\Entity\ProfitCalculatorPriceTier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProfitCalculatorPriceTier>
 */
class ProfitCalculatorPriceTierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfitCalculatorPriceTier::class);
    }
}
