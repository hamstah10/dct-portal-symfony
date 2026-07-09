<?php

namespace App\Repository;

use App\Entity\ScraperImportLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScraperImportLog>
 */
final class ScraperImportLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScraperImportLog::class);
    }

    /**
     * @return list<ScraperImportLog>
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('sil')
            ->orderBy('sil.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<ScraperImportLog>
     */
    public function findLatestBySource(string $source, int $limit = 10): array
    {
        return $this->createQueryBuilder('sil')
            ->andWhere('sil.source = :source')
            ->setParameter('source', $source)
            ->orderBy('sil.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<string> $paths
     * @return list<ScraperImportLog>
     */
    public function findLatestForPathsBySource(array $paths, string $source): array
    {
        if ([] === $paths) {
            return [];
        }

        $results = $this->createQueryBuilder('sil')
            ->andWhere('sil.source = :source')
            ->andWhere('sil.filePath IN (:paths)')
            ->setParameter('source', $source)
            ->setParameter('paths', $paths)
            ->orderBy('sil.filePath', 'ASC')
            ->addOrderBy('sil.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $latestByPath = [];
        foreach ($results as $result) {
            if (!$result instanceof ScraperImportLog) {
                continue;
            }

            if (!isset($latestByPath[$result->getFilePath()])) {
                $latestByPath[$result->getFilePath()] = $result;
            }
        }

        return array_values($latestByPath);
    }

    /**
     * @return list<ScraperImportLog>
     */
    public function findLatestByPathPrefix(string $pathPrefix, int $limit = 10): array
    {
        return $this->createQueryBuilder('sil')
            ->andWhere('sil.filePath LIKE :pathPrefix')
            ->setParameter('pathPrefix', rtrim($pathPrefix, '/') . '/%')
            ->orderBy('sil.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<ScraperImportLog>
     */
    public function findLatestExcludingPathPrefix(string $pathPrefix, int $limit = 10): array
    {
        return $this->createQueryBuilder('sil')
            ->andWhere('sil.filePath NOT LIKE :pathPrefix')
            ->setParameter('pathPrefix', rtrim($pathPrefix, '/') . '/%')
            ->orderBy('sil.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countOrphaned(): int
    {
        return (int) $this->createQueryBuilder('sil')
            ->select('COUNT(sil.id)')
            ->andWhere('sil.vehicleDataPool IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function deleteOrphaned(): int
    {
        return $this->createQueryBuilder('sil')
            ->delete()
            ->andWhere('sil.vehicleDataPool IS NULL')
            ->getQuery()
            ->execute();
    }
}
