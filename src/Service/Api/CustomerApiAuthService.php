<?php

namespace App\Service\Api;

use App\Entity\CustomerApiToken;
use App\Entity\Kunde;
use App\Repository\CustomerApiTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CustomerApiAuthService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CustomerApiTokenRepository $tokenRepository,
        private readonly int $accessTokenTtl,
        private readonly int $refreshTokenTtl,
    ) {
    }

    /**
     * @return array{
     *   tokenType: string,
     *   accessToken: string,
     *   accessTokenExpiresAt: string,
     *   refreshToken: string,
     *   refreshTokenExpiresAt: string
     * }
     */
    public function issueTokenPair(Kunde $customer, ?string $deviceName = null, ?string $sessionId = null): array
    {
        $sessionId ??= bin2hex(random_bytes(16));
        $accessToken = $this->createToken(CustomerApiToken::TYPE_ACCESS, $customer, $sessionId, $deviceName, $this->accessTokenTtl);
        $refreshToken = $this->createToken(CustomerApiToken::TYPE_REFRESH, $customer, $sessionId, $deviceName, $this->refreshTokenTtl);

        $this->entityManager->persist($accessToken->entity);
        $this->entityManager->persist($refreshToken->entity);
        $this->entityManager->flush();

        return [
            'tokenType' => 'Bearer',
            'accessToken' => $accessToken->plainToken,
            'accessTokenExpiresAt' => $accessToken->entity->getExpiresAt()?->format(\DateTimeInterface::ATOM),
            'refreshToken' => $refreshToken->plainToken,
            'refreshTokenExpiresAt' => $refreshToken->entity->getExpiresAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    public function findCustomerByAccessToken(string $plainToken): ?Kunde
    {
        $token = $this->tokenRepository->findActiveByPlainToken($plainToken, CustomerApiToken::TYPE_ACCESS);
        if (null === $token) {
            return null;
        }

        $customer = $token->getCustomer();
        if (!$customer instanceof Kunde || $customer->isGesperrt()) {
            $now = new \DateTimeImmutable();
            $token->setRevokedAt($now);
            $token->setLastUsedAt($now);
            $this->entityManager->flush();

            return null;
        }

        $token->setLastUsedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $customer;
    }

    /**
     * @return array{
     *   tokenType: string,
     *   accessToken: string,
     *   accessTokenExpiresAt: string,
     *   refreshToken: string,
     *   refreshTokenExpiresAt: string
     * }|null
     */
    public function refreshByRefreshToken(string $plainRefreshToken): ?array
    {
        $refreshToken = $this->tokenRepository->findActiveByPlainToken($plainRefreshToken, CustomerApiToken::TYPE_REFRESH);
        if (null === $refreshToken) {
            return null;
        }

        $customer = $refreshToken->getCustomer();
        if (!$customer instanceof Kunde || $customer->isGesperrt()) {
            $now = new \DateTimeImmutable();
            $refreshToken->setRevokedAt($now);
            $refreshToken->setLastUsedAt($now);
            $this->entityManager->flush();

            return null;
        }

        $refreshToken->setRevokedAt(new \DateTimeImmutable());
        $refreshToken->setLastUsedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->issueTokenPair(
            $customer,
            $refreshToken->getDeviceName(),
            $refreshToken->getSessionId()
        );
    }

    public function revokeSessionByAccessToken(string $plainAccessToken): void
    {
        $accessToken = $this->tokenRepository->findActiveByPlainToken($plainAccessToken, CustomerApiToken::TYPE_ACCESS);
        if (null === $accessToken) {
            return;
        }

        $now = new \DateTimeImmutable();
        $tokens = $this->tokenRepository->createQueryBuilder('t')
            ->andWhere('t.customer = :customer')
            ->andWhere('t.sessionId = :sessionId')
            ->andWhere('t.revokedAt IS NULL')
            ->setParameter('customer', $accessToken->getCustomer())
            ->setParameter('sessionId', $accessToken->getSessionId())
            ->getQuery()
            ->getResult();

        foreach ($tokens as $token) {
            $token->setRevokedAt($now);
            $token->setLastUsedAt($now);
        }

        $this->entityManager->flush();
    }

    public function isValidAccessToken(string $plainAccessToken): bool
    {
        $token = $this->tokenRepository->findActiveByPlainToken($plainAccessToken, CustomerApiToken::TYPE_ACCESS);
        if (null === $token) {
            return false;
        }

        $customer = $token->getCustomer();

        return $customer instanceof Kunde && !$customer->isGesperrt();
    }

    private function createToken(
        string $type,
        Kunde $customer,
        string $sessionId,
        ?string $deviceName,
        int $ttl,
    ): TokenWithPlainValue {
        $plainToken = bin2hex(random_bytes(32));

        $entity = (new CustomerApiToken())
            ->setCustomer($customer)
            ->setType($type)
            ->setTokenHash(CustomerApiTokenRepository::hashToken($plainToken))
            ->setSessionId($sessionId)
            ->setDeviceName($this->normalizeDeviceName($deviceName))
            ->setExpiresAt((new \DateTimeImmutable())->modify(sprintf('+%d seconds', max(1, $ttl))));

        return new TokenWithPlainValue($entity, $plainToken);
    }

    private function normalizeDeviceName(?string $deviceName): ?string
    {
        if (null === $deviceName) {
            return null;
        }

        $value = trim($deviceName);
        if ('' === $value) {
            return null;
        }

        return substr($value, 0, 120);
    }
}

final readonly class TokenWithPlainValue
{
    public function __construct(
        public CustomerApiToken $entity,
        public string $plainToken,
    ) {
    }
}
