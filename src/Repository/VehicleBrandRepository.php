<?php

namespace App\Repository;

use App\Entity\VehicleBrand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VehicleBrand>
 */
class VehicleBrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VehicleBrand::class);
    }

    public function findOneBySlugOrName(?string $slug, ?string $name): ?VehicleBrand
    {
        $slug = trim((string) ($slug ?? ''));
        $name = trim((string) ($name ?? ''));
        if ('' === $slug && '' === $name) {
            return null;
        }

        $qb = $this->createQueryBuilder('b');
        $qb->setMaxResults(1);
        $orX = $qb->expr()->orX();
        if ('' !== $slug) {
            $orX->add($qb->expr()->eq('b.slug', ':slug'));
            $qb->setParameter('slug', $slug);
        }
        if ('' !== $name) {
            $orX->add($qb->expr()->eq('LOWER(b.name)', ':nameLower'));
            $qb->setParameter('nameLower', strtolower($name));
        }

        $qb->where($orX);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
