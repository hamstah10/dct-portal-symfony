<?php

namespace App\Repository;

use App\Entity\ToolNewsItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ToolNewsItem>
 */
class ToolNewsItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ToolNewsItem::class);
    }

    public function findOneByUrl(string $url): ?ToolNewsItem
    {
        return $this->findOneBy(['url' => $url]);
    }

    public function findOneByContentHash(string $contentHash): ?ToolNewsItem
    {
        return $this->findOneBy(['contentHash' => $contentHash]);
    }
}
