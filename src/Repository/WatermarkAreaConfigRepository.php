<?php

namespace App\Repository;

use App\Entity\WatermarkAreaConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WatermarkAreaConfig>
 */
class WatermarkAreaConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WatermarkAreaConfig::class);
    }

    public function findByArea(string $area): ?WatermarkAreaConfig
    {
        return $this->findOneBy(['area' => $area, 'portalKey' => null]);
    }

    /**
     * Finds config for a specific portal, falling back to global (portalKey=null).
     */
    public function findForPortal(string $area, ?string $portalKey): ?WatermarkAreaConfig
    {
        if ($portalKey !== null && $portalKey !== '') {
            $specific = $this->findOneBy(['area' => $area, 'portalKey' => strtolower(trim($portalKey))]);
            if ($specific !== null) {
                return $specific;
            }
        }

        return $this->findOneBy(['area' => $area, 'portalKey' => null]);
    }
}
