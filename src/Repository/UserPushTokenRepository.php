<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserPushToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPushToken>
 */
class UserPushTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPushToken::class);
    }

    public function findOneByToken(string $token): ?UserPushToken
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * @return list<UserPushToken>
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }
}
