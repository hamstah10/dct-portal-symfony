<?php

namespace App\Repository;

use App\Entity\AdminPageAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdminPageAccess>
 */
class AdminPageAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminPageAccess::class);
    }

    /**
     * @return list<string>
     */
    public function findUsedPageKeys(): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('r.pageKey')
            ->getQuery()
            ->getScalarResult();

        $keys = [];
        foreach ($rows as $row) {
            $value = $row['pageKey'] ?? null;
            if (null === $value || '' === $value) {
                continue;
            }

            $keys[] = (string) $value;
        }

        return array_values(array_unique($keys));
    }
}
