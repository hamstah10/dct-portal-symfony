<?php

namespace App\Repository;

use App\Entity\DetectedEcu;
use App\Entity\VehicleCoverageMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VehicleCoverageMatch>
 */
class VehicleCoverageMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VehicleCoverageMatch::class);
    }

    /**
     * @return array<int, VehicleCoverageMatch>
     */
    public function findByDetectedEcu(DetectedEcu $detectedEcu): array
    {
        return $this->findBy(['detectedEcu' => $detectedEcu]);
    }

    public function removeAllForDetectedEcu(DetectedEcu $detectedEcu): int
    {
        return (int) $this->createQueryBuilder('m')
            ->delete()
            ->where('m.detectedEcu = :detectedEcu')
            ->setParameter('detectedEcu', $detectedEcu)
            ->getQuery()
            ->execute();
    }

    public function findOneByEngineId(DetectedEcu $detectedEcu, int $vehicleEngineId): ?VehicleCoverageMatch
    {
        return $this->findOneBy([
            'detectedEcu' => $detectedEcu,
            'vehicleEngineId' => $vehicleEngineId,
        ]);
    }

    public function findOneByVehicleTextTuple(
        DetectedEcu $detectedEcu,
        ?string $vehicleBrand,
        ?string $vehicleSeries,
        ?string $vehicleModel,
        ?string $vehicleEngine,
    ): ?VehicleCoverageMatch {
        return $this->findOneBy([
            'detectedEcu' => $detectedEcu,
            'vehicleBrand' => $vehicleBrand,
            'vehicleSeries' => $vehicleSeries,
            'vehicleModel' => $vehicleModel,
            'vehicleEngine' => $vehicleEngine,
        ]);
    }
}
