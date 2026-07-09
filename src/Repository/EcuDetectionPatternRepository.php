<?php

namespace App\Repository;

use App\Entity\EcuDetectionPattern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EcuDetectionPattern>
 */
class EcuDetectionPatternRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EcuDetectionPattern::class);
    }

    /**
     * @return array<int, EcuDetectionPattern>
     */
    public function findActiveByType(string $type): array
    {
        return $this->findBy(['type' => $type, 'isActive' => true]);
    }

    /**
     * @return array<int, EcuDetectionPattern>
     */
    public function findAllActive(): array
    {
        return $this->findBy(['isActive' => true]);
    }
}
