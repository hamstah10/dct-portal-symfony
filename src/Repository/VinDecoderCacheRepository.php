<?php

namespace App\Repository;

use App\Entity\VinDecoderCache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VinDecoderCache>
 */
class VinDecoderCacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VinDecoderCache::class);
    }
}
