<?php

namespace App\Repository;

use App\Entity\ToolNewsSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ToolNewsSource>
 */
class ToolNewsSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ToolNewsSource::class);
    }

    public function findOneBySpiderName(string $spiderName): ?ToolNewsSource
    {
        return $this->findOneBy(['spiderName' => $spiderName]);
    }
}
