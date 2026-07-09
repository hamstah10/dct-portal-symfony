<?php

namespace App\Repository;

use App\Entity\ProfitCalculatorOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProfitCalculatorOption>
 */
class ProfitCalculatorOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfitCalculatorOption::class);
    }
}
