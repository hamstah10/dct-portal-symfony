<?php

namespace App\Service\Api;

use App\Entity\MobileApiToken;
use App\Entity\User;
use App\Repository\MobileApiTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

final class MobileApiAuthService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MobileApiTokenRepository $tokenRepository,
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
    public function issueTokenPair(User $user, ?string $deviceName = null, ?string $sessionId = null): array
    {
        $sessionId ??= bin2hex(random_bytes(16));
        $accessToken = $this->createToken(MobileApiToken::TYPE_ACCESS, $user, $sessionId, $deviceName, $this->accessTokenTtl);
        $refreshToken = $this->createToken(MobileApiToken::TYPE_REFRESH, $user, $sessionId, $deviceName, $this->refreshTokenTtl);

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

    public function findUserByAccessToken(string $plainToken): ?User
    {
        $token = $this->tokenRepository->findActiveByPlainToken($plainToken, MobileApiToken::TYPE_ACCESS);
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        if (!$user instanceof User || !$user->isActive() || !\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $now = new \DateTimeImmutable();
            $token->setRevokedAt($now);
            $token->setLastUsedAt($now);
            $this->entityManager->flush();

            return null;
        }

        $token->setLastUsedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $user;
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
        $refreshToken = $this->tokenRepository->findActiveByPlainToken($plainRefreshToken, MobileApiToken::TYPE_REFRESH);
        if (null === $refreshToken) {
            return null;
        }

        $user = $refreshToken->getUser();
        if (!$user instanceof User || !$user->isActive() || !\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
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
            $user,
            $refreshToken->getDeviceName(),
            $refreshToken->getSessionId()
        );
    }

    public function revokeSessionByAccessToken(string $plainAccessToken): void
    {
        $accessToken = $this->tokenRepository->findActiveByPlainToken($plainAccessToken, MobileApiToken::TYPE_ACCESS);
        if (null === $accessToken) {
            return;
        }

        $now = new \DateTimeImmutable();
        $tokens = $this->tokenRepository->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.sessionId = :sessionId')
            ->andWhere('t.revokedAt IS NULL')
            ->setParameter('user', $accessToken->getUser())
            ->setParameter('sessionId', $accessToken->getSessionId())
            ->getQuery()
            ->getResult();

        foreach ($tokens as $token) {
            $token->setRevokedAt($now);
            $token->setLastUsedAt($now);
        }

        $this->entityManager->flush();
    }

    private function createToken(
        string $type,
        User $user,
        string $sessionId,
        ?string $deviceName,
        int $ttl,
    ): MobileTokenWithPlainValue {
        $plainToken = bin2hex(random_bytes(32));

        $entity = (new MobileApiToken())
            ->setUser($user)
            ->setType($type)
            ->setTokenHash(MobileApiTokenRepository::hashToken($plainToken))
            ->setSessionId($sessionId)
            ->setDeviceName($this->normalizeDeviceName($deviceName))
            ->setExpiresAt((new \DateTimeImmutable())->modify(sprintf('+%d seconds', max(1, $ttl))));

        return new MobileTokenWithPlainValue($entity, $plainToken);
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

final readonly class MobileTokenWithPlainValue
{
    public function __construct(
        public MobileApiToken $entity,
        public string $plainToken,
    ) {
    }
}
