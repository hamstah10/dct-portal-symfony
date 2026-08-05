<?php

namespace App\Repository;

use App\Entity\ChiptuningWidgetConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChiptuningWidgetConfig>
 */
final class ChiptuningWidgetConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChiptuningWidgetConfig::class);
    }

    public function findActiveByEmbedKey(string $embedKey): ?ChiptuningWidgetConfig
    {
        $key = strtolower(trim($embedKey));
        if ('' === $key) {
            return null;
        }

        return $this->findOneBy([
            'embedKey' => $key,
            'active' => true,
        ]);
    }
}
