<?php

namespace App\Repository;

use App\Entity\EcuOptionHint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EcuOptionHint>
 */
class EcuOptionHintRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EcuOptionHint::class);
    }

    /**
     * Returns all distinct, non-empty moduleSlug values ordered alphabetically.
     *
     * @return string[]
     */
    public function findDistinctModuleSlugs(): array
    {
        $rows = $this->createQueryBuilder('h')
            ->select('DISTINCT h.moduleSlug')
            ->where('h.moduleSlug IS NOT NULL')
            ->andWhere('h.moduleSlug != :empty')
            ->setParameter('empty', '')
            ->orderBy('h.moduleSlug', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($rows, 'moduleSlug');
    }

    /**
     * Finds hints matching the given ECU context.
     *
     * @param string[] $brandNames VehicleBrand names from linked VehicleEngines
     * @return EcuOptionHint[]
     */
    public function findMatchingForEcuOption(
        string $moduleSlug,
        string $manufacturerName,
        string $ecuType,
        array $brandNames,
    ): array {
        $qb = $this->createQueryBuilder('h')
            ->where('h.moduleSlug = :moduleSlug')
            ->andWhere('h.active = true')
            ->andWhere('LOWER(h.manufacturer) LIKE LOWER(:manufacturer)')
            ->andWhere('LOWER(h.model) LIKE LOWER(:model)')
            ->setParameter('moduleSlug', $moduleSlug)
            ->setParameter('manufacturer', '%' . $manufacturerName . '%')
            ->setParameter('model', '%' . $ecuType . '%')
            ->orderBy('h.brandName', 'ASC')
            ->addOrderBy('h.manufacturer', 'ASC');

        if ([] !== $brandNames) {
            $qb->andWhere('h.brandName IN (:brands)')
               ->setParameter('brands', $brandNames);
        }

        return $qb->getQuery()->getResult();
    }
}
