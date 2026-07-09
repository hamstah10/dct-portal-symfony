<?php

namespace App\Repository;

use App\Entity\ScraperRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ScraperRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScraperRun::class);
    }

    /**
     * @return list<ScraperRun>
     */
    public function findLatest(int $limit = 20): array
    {
        return $this->createQueryBuilder('sr')
            ->orderBy('sr.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
