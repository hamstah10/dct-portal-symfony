<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Portal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Portal>
 */
final class PortalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Portal::class);
    }

    public function findOneByPortalHost(string $host): ?Portal
    {
        return $this->findOneByHostField('portalHost', $host);
    }

    public function findOneByWebsiteHost(string $host): ?Portal
    {
        return $this->findOneByHostField('websiteHost', $host);
    }

    public function findOneByKnownHost(string $host): ?Portal
    {
        $normalizedHost = strtolower(trim($host));
        if ($normalizedHost === '') {
            return null;
        }

        return $this->createQueryBuilder('portal')
            ->andWhere('LOWER(portal.portalHost) = :host OR LOWER(portal.websiteHost) = :host')
            ->setParameter('host', $normalizedHost)
            ->orderBy('portal.sortOrder', 'ASC')
            ->addOrderBy('portal.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function findOneByHostField(string $field, string $host): ?Portal
    {
        $normalizedHost = strtolower(trim($host));
        if ($normalizedHost === '') {
            return null;
        }

        return $this->createQueryBuilder('portal')
            ->andWhere(sprintf('LOWER(portal.%s) = :host', $field))
            ->setParameter('host', $normalizedHost)
            ->orderBy('portal.sortOrder', 'ASC')
            ->addOrderBy('portal.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
