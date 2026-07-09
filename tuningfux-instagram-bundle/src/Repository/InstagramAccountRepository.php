<?php

declare(strict_types=1);

namespace Tuningfux\InstagramBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tuningfux\InstagramBundle\Entity\InstagramAccount;

final class InstagramAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstagramAccount::class);
    }

    public function findOneByInstagramUserId(string $instagramUserId): ?InstagramAccount
    {
        return $this->findOneBy(['instagramUserId' => $instagramUserId]);
    }
}
