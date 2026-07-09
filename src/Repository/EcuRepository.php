<?php

namespace App\Repository;

use App\Entity\Ecu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ecu>
 */
class EcuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ecu::class);
    }

    /**
     * Finds ECUs whose name or type loosely matches any of the given search terms.
     * Used as a candidate pre-filter for vehicle matching (avoids loading the
     * full vehicle database into memory).
     *
     * @param list<string> $terms
     *
     * @return array<int, Ecu>
     */
    public function findCandidatesByTerms(array $terms, int $limit = 200): array
    {
        $terms = array_values(array_unique(array_filter(array_map(
            static fn (string $term): string => trim($term),
            $terms
        ), static fn (string $term): bool => mb_strlen($term) >= 3)));

        if ([] === $terms) {
            return [];
        }

        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.vehicleEngines', 've')
            ->addSelect('ve')
            ->setMaxResults($limit);

        $orX = $qb->expr()->orX();
        foreach ($terms as $index => $term) {
            $param = 'term' . $index;
            $orX->add($qb->expr()->like('LOWER(e.name)', ':' . $param));
            $qb->setParameter($param, '%' . mb_strtolower($term) . '%');
        }

        $qb->andWhere($orX);

        return $qb->getQuery()->getResult();
    }
}
