<?php

namespace App\Repository;

use App\Entity\ProjectIdeaCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectIdeaCard>
 */
final class ProjectIdeaCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectIdeaCard::class);
    }

    /**
     * @return list<ProjectIdeaCard>
     */
    public function findByStatusOrdered(string $status): array
    {
        return $this->createQueryBuilder('card')
            ->andWhere('card.status = :status')
            ->setParameter('status', $status)
            ->orderBy('card.position', 'ASC')
            ->addOrderBy('card.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function nextPositionForStatus(string $status): int
    {
        $maxPosition = $this->createQueryBuilder('card')
            ->select('MAX(card.position)')
            ->andWhere('card.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();

        return null === $maxPosition ? 1 : ((int) $maxPosition + 1);
    }
}
