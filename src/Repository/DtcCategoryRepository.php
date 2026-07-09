<?php

namespace App\Repository;

use App\Entity\DtcCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DtcCategory>
 */
class DtcCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DtcCategory::class);
    }

    public function getNextUid(): int
    {
        $max = (int) $this->createQueryBuilder('category')
            ->select('MAX(category.uid)')
            ->getQuery()
            ->getSingleScalarResult();

        return $max + 1;
    }
}
