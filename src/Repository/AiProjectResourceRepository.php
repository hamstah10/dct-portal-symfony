<?php

namespace App\Repository;

use App\Entity\AiProject;
use App\Entity\AiProjectResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AiProjectResource>
 */
final class AiProjectResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AiProjectResource::class);
    }

    /**
     * @return list<AiProjectResource>
     */
    public function findByProject(AiProject $project): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.project = :project')
            ->setParameter('project', $project)
            ->orderBy('r.createdAt', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
