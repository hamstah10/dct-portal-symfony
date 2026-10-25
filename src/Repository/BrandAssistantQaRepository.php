<?php

namespace App\Repository;

use App\Entity\BrandAssistantQa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BrandAssistantQa>
 */
final class BrandAssistantQaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BrandAssistantQa::class);
    }
}
