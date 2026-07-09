<?php

namespace App\Repository;

use App\Entity\ApiToken;
use App\Entity\Kunde;
use App\Entity\VehicleDataPool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApiToken>
 */
class ApiTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }

    public function findValidByToken(string $token, ?\DateTimeImmutable $reference = null): ?ApiToken
    {
        $normalizedToken = trim($token);
        if ('' === $normalizedToken) {
            return null;
        }

        $reference ??= new \DateTimeImmutable();

        return $this->createQueryBuilder('t')
            ->andWhere('t.apiToken = :token')
            ->andWhere('t.active = :active')
            ->andWhere('t.gueltigBis IS NULL OR t.gueltigBis >= :reference')
            ->setParameter('token', $normalizedToken)
            ->setParameter('active', true)
            ->setParameter('reference', $reference)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findValidByTokenAndIdentifier(
        string $token,
        string $tokenIdentifier,
        ?\DateTimeImmutable $reference = null
    ): ?ApiToken {
        $normalizedToken = trim($token);
        $normalizedIdentifier = trim($tokenIdentifier);
        if ('' === $normalizedToken || '' === $normalizedIdentifier) {
            return null;
        }

        $reference ??= new \DateTimeImmutable();

        return $this->createQueryBuilder('t')
            ->andWhere('t.apiToken = :token')
            ->andWhere('t.tokenIdentifier = :tokenIdentifier')
            ->andWhere('t.active = :active')
            ->andWhere('t.gueltigBis IS NULL OR t.gueltigBis >= :reference')
            ->setParameter('token', $normalizedToken)
            ->setParameter('tokenIdentifier', $normalizedIdentifier)
            ->setParameter('active', true)
            ->setParameter('reference', $reference)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveByApplicationName(string $applicationName, ?\DateTimeImmutable $reference = null): ?ApiToken
    {
        $normalizedName = trim($applicationName);
        if ('' === $normalizedName) {
            return null;
        }

        $reference ??= new \DateTimeImmutable();

        return $this->createQueryBuilder('t')
            ->andWhere('LOWER(t.applicationName) = :name')
            ->andWhere('t.active = :active')
            ->andWhere('t.gueltigBis IS NULL OR t.gueltigBis >= :reference')
            ->setParameter('name', strtolower($normalizedName))
            ->setParameter('active', true)
            ->setParameter('reference', $reference)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestActiveForCustomer(Kunde $customer, ?\DateTimeImmutable $reference = null): ?ApiToken
    {
        $customerId = $customer->getId();
        if (null === $customerId) {
            return null;
        }

        $reference ??= new \DateTimeImmutable();

        return $this->createQueryBuilder('t')
            ->andWhere('t.kunde = :customer')
            ->andWhere('t.active = :active')
            ->andWhere('t.gueltigBis IS NULL OR t.gueltigBis >= :reference')
            ->setParameter('customer', $customer)
            ->setParameter('active', true)
            ->setParameter('reference', $reference)
            ->orderBy('t.createdAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestActiveForCustomerAndPool(
        Kunde $customer,
        VehicleDataPool $vehicleDataPool,
        ?\DateTimeImmutable $reference = null
    ): ?ApiToken {
        $customerId = $customer->getId();
        $poolId = $vehicleDataPool->getId();
        if (null === $customerId || null === $poolId) {
            return null;
        }

        $reference ??= new \DateTimeImmutable();

        return $this->createQueryBuilder('t')
            ->innerJoin('t.vehicleDataContext', 'c')
            ->innerJoin('c.allowedDataPools', 'p')
            ->andWhere('t.kunde = :customer')
            ->andWhere('t.active = :active')
            ->andWhere('c.active = :contextActive')
            ->andWhere('p = :vehicleDataPool')
            ->andWhere('t.gueltigBis IS NULL OR t.gueltigBis >= :reference')
            ->setParameter('customer', $customer)
            ->setParameter('vehicleDataPool', $vehicleDataPool)
            ->setParameter('active', true)
            ->setParameter('contextActive', true)
            ->setParameter('reference', $reference)
            ->orderBy('t.createdAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestActiveGlobal(?\DateTimeImmutable $reference = null): ?ApiToken
    {
        $reference ??= new \DateTimeImmutable();

        return $this->createQueryBuilder('t')
            ->andWhere('t.active = :active')
            ->andWhere('t.gueltigBis IS NULL OR t.gueltigBis >= :reference')
            ->setParameter('active', true)
            ->setParameter('reference', $reference)
            ->orderBy('t.createdAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestActiveGlobalForPool(
        VehicleDataPool $vehicleDataPool,
        ?\DateTimeImmutable $reference = null
    ): ?ApiToken {
        $poolId = $vehicleDataPool->getId();
        if (null === $poolId) {
            return null;
        }

        $reference ??= new \DateTimeImmutable();

        return $this->createQueryBuilder('t')
            ->innerJoin('t.vehicleDataContext', 'c')
            ->innerJoin('c.allowedDataPools', 'p')
            ->andWhere('t.active = :active')
            ->andWhere('c.active = :contextActive')
            ->andWhere('p = :vehicleDataPool')
            ->andWhere('t.gueltigBis IS NULL OR t.gueltigBis >= :reference')
            ->setParameter('vehicleDataPool', $vehicleDataPool)
            ->setParameter('active', true)
            ->setParameter('contextActive', true)
            ->setParameter('reference', $reference)
            ->orderBy('t.createdAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
