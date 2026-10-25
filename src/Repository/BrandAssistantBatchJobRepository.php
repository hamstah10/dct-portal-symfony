<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BrandAssistantBatchJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BrandAssistantBatchJob>
 */
final class BrandAssistantBatchJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BrandAssistantBatchJob::class);
    }

    public function findPendingJobs(): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.status = :status')
            ->setParameter('status', BrandAssistantBatchJob::STATUS_PENDING)
            ->orderBy('j.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentJobs(int $limit = 20): array
    {
        return $this->createQueryBuilder('j')
            ->orderBy('j.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
