<?php

namespace App\Repository;

use App\Entity\CustomerApiToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerApiToken>
 */
class CustomerApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerApiToken::class);
    }

    public function findActiveByPlainToken(string $plainToken, string $type): ?CustomerApiToken
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('t')
            ->andWhere('t.type = :type')
            ->andWhere('t.tokenHash = :tokenHash')
            ->andWhere('t.revokedAt IS NULL')
            ->andWhere('t.expiresAt > :now')
            ->setParameter('type', $type)
            ->setParameter('tokenHash', self::hashToken($plainToken))
            ->setParameter('now', $now)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
