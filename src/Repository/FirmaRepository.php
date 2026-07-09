<?php

namespace App\Repository;

use App\Entity\Firma;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Firma>
 */
class FirmaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Firma::class);
    }

    public function findActiveByKuerzel(string $kuerzel): ?Firma
    {
        return $this->createQueryBuilder('firma')
            ->andWhere('UPPER(TRIM(COALESCE(firma.kuerzel, \'\'))) = :kuerzel')
            ->andWhere('firma.aktiv = true')
            ->setParameter('kuerzel', strtoupper(trim($kuerzel)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
