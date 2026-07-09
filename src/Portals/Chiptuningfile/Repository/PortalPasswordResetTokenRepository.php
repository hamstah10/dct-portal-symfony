<?php

namespace App\Portals\Chiptuningfile\Repository;

use App\Entity\Kunde;
use App\Portals\Chiptuningfile\Entity\PortalPasswordResetToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PortalPasswordResetToken>
 */
final class PortalPasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PortalPasswordResetToken::class);
    }

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', trim($plainToken));
    }

    public function findValidByPlainToken(string $plainToken): ?PortalPasswordResetToken
    {
        $token = $this->findOneBy(['tokenHash' => self::hashToken($plainToken)]);
        if (!$token instanceof PortalPasswordResetToken) {
            return null;
        }

        if (null !== $token->getUsedAt()) {
            return null;
        }

        if ($token->getExpiresAt() < new \DateTimeImmutable()) {
            return null;
        }

        return $token;
    }

    public function invalidateActiveForCustomer(Kunde $customer): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update(PortalPasswordResetToken::class, 't')
            ->set('t.usedAt', ':usedAt')
            ->where('t.kunde = :kunde')
            ->andWhere('t.usedAt IS NULL')
            ->setParameter('usedAt', new \DateTimeImmutable())
            ->setParameter('kunde', $customer)
            ->getQuery()
            ->execute();
    }
}
