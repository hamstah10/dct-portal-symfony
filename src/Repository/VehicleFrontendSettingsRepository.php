<?php

namespace App\Repository;

use App\Entity\VehicleDataPool;
use App\Entity\VehicleFrontendSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VehicleFrontendSettings>
 */
class VehicleFrontendSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VehicleFrontendSettings::class);
    }

    public function findCurrent(): ?VehicleFrontendSettings
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.active = :active')
            ->setParameter('active', true)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCurrentForPool(?VehicleDataPool $vehicleDataPool): ?VehicleFrontendSettings
    {
        if (!$vehicleDataPool instanceof VehicleDataPool || null === $vehicleDataPool->getId()) {
            return $this->findCurrent();
        }

        return $this->createQueryBuilder('s')
            ->andWhere('s.active = :active')
            ->andWhere('s.vehicleDataPool = :vehicleDataPool')
            ->setParameter('active', true)
            ->setParameter('vehicleDataPool', $vehicleDataPool)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getCurrentOrDefault(): VehicleFrontendSettings
    {
        return $this->findCurrent() ?? new VehicleFrontendSettings();
    }

    public function getCurrentOrDefaultForPool(?VehicleDataPool $vehicleDataPool): VehicleFrontendSettings
    {
        $settings = $this->findCurrentForPool($vehicleDataPool);
        if ($settings instanceof VehicleFrontendSettings) {
            return $settings;
        }

        $fallback = $this->findCurrent();
        if ($fallback instanceof VehicleFrontendSettings) {
            return $fallback;
        }

        return (new VehicleFrontendSettings())
            ->setVehicleDataPool($vehicleDataPool);
    }
}
