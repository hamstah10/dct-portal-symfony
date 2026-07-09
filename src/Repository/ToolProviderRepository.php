<?php

namespace App\Repository;

use App\Entity\ToolProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ToolProvider>
 */
class ToolProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ToolProvider::class);
    }

    public function findOneBySlug(string $slug): ?ToolProvider
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
