<?php

namespace App\Repository;

use App\Entity\DtcCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DtcCode>
 */
class DtcCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DtcCode::class);
    }

    public function getNextUid(): int
    {
        $max = (int) $this->createQueryBuilder('code')
            ->select('MAX(code.uid)')
            ->getQuery()
            ->getSingleScalarResult();

        return $max + 1;
    }

    /**
     * @return list<DtcCode>
     */
    public function findVisibleForPortal(): array
    {
        /** @var list<DtcCode> $codes */
        $codes = $this->createQueryBuilder('code')
            ->innerJoin('code.category', 'category')
            ->addSelect('category')
            ->andWhere('code.hidden = false')
            ->andWhere('code.deleted = false')
            ->andWhere('category.hidden = false')
            ->andWhere('category.deleted = false')
            ->orderBy('category.buchstabe', 'ASC')
            ->addOrderBy('category.name', 'ASC')
            ->addOrderBy('code.fehlercode', 'ASC')
            ->getQuery()
            ->getResult();

        return $codes;
    }
}
