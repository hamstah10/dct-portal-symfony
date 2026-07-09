<?php

namespace App\Repository;

use App\Entity\DetectedEcu;
use App\Entity\ToolNewsItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DetectedEcu>
 */
class DetectedEcuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetectedEcu::class);
    }

    /**
     * @return array<int, DetectedEcu>
     */
    public function findByNewsItem(ToolNewsItem $newsItem): array
    {
        return $this->findBy(['newsItem' => $newsItem]);
    }

    public function removeAllForNewsItem(ToolNewsItem $newsItem): int
    {
        return (int) $this->createQueryBuilder('e')
            ->delete()
            ->where('e.newsItem = :newsItem')
            ->setParameter('newsItem', $newsItem)
            ->getQuery()
            ->execute();
    }
}
