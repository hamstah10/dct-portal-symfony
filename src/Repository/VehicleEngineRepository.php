<?php

namespace App\Repository;

use App\Entity\VehicleEngine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VehicleEngine>
 */
class VehicleEngineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VehicleEngine::class);
    }

    /**
     * Finds active engines whose brand name loosely matches any of the given
     * brand terms (case-insensitive). Used as a candidate pre-filter for
     * vehicle matching based on a free-text vehicle hint, independent of any
     * ECU-name match (e.g. platform-alias suggestions).
     *
     * @param list<string> $brandTerms
     *
     * @return array<int, VehicleEngine>
     */
    public function findActiveCandidatesByBrandTerms(array $brandTerms, int $limit = 200): array
    {
        $brandTerms = array_values(array_unique(array_filter(array_map(
            static fn (string $term): string => trim($term),
            $brandTerms
        ), static fn (string $term): bool => '' !== $term)));

        if ([] === $brandTerms) {
            return [];
        }

        $qb = $this->createQueryBuilder('engine')
            ->leftJoin('engine.vehicleModel', 'model')
            ->addSelect('model')
            ->leftJoin('model.vehicleSeries', 'series')
            ->addSelect('series')
            ->leftJoin('series.vehicleBrand', 'brand')
            ->addSelect('brand')
            ->where('engine.active = :active')
            ->setParameter('active', true)
            ->setMaxResults($limit);

        $orX = $qb->expr()->orX();
        foreach ($brandTerms as $index => $term) {
            $param = 'brandTerm' . $index;
            $orX->add($qb->expr()->like('LOWER(brand.name)', ':' . $param));
            $qb->setParameter($param, '%' . mb_strtolower($term) . '%');
        }

        $qb->andWhere($orX);

        return $qb->getQuery()->getResult();
    }
}
