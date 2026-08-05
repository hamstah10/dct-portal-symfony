<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\WinolsConfigurationRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WinolsConfigurationRule>
 */
class WinolsConfigurationRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WinolsConfigurationRule::class);
    }
}
