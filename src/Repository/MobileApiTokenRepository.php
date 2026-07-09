<?php

namespace App\Repository;

use App\Entity\MobileApiToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MobileApiToken>
 */
class MobileApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MobileApiToken::class);
    }

    public function findActiveByPlainToken(string $plainToken, string $type): ?MobileApiToken
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
