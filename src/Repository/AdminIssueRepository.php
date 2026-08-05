<?php

namespace App\Repository;

use App\Entity\AdminIssue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminIssue>
 */
class AdminIssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminIssue::class);
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('issue')
            ->where('issue.status = :status')
            ->setParameter('status', $status)
            ->orderBy('issue.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findUnresolvedIssues(): array
    {
        return $this->createQueryBuilder('issue')
            ->where('issue.status != :resolved')
            ->setParameter('resolved', 'resolved')
            ->orderBy('issue.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByEntityReference(string $entityType, int $entityId): array
    {
        return $this->createQueryBuilder('issue')
            ->where('issue.entityType = :entityType')
            ->andWhere('issue.entityId = :entityId')
            ->setParameter('entityType', $entityType)
            ->setParameter('entityId', $entityId)
            ->orderBy('issue.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
