<?php

namespace App\Portals\Chiptuningfile\Service;

use App\Entity\VehicleBrand;
use App\Entity\VehicleEngine;
use App\Repository\VehicleBrandRepository;
use App\Repository\VehicleEngineRepository;

final class PortalVehicleDatabaseService
{
    public function __construct(
        private readonly VehicleEngineRepository $vehicleEngineRepository,
        private readonly VehicleBrandRepository $vehicleBrandRepository,
    ) {
    }

    /**
     * @return array{filters: array<string, mixed>, brands: list<VehicleBrand>, items: list<array<string, mixed>>}
     */
    public function search(?string $query, ?int $brandId, int $limit = 30): array
    {
        $qb = $this->vehicleEngineRepository->createQueryBuilder('engine')
            ->leftJoin('engine.vehicleModel', 'model')->addSelect('model')
            ->leftJoin('model.vehicleSeries', 'series')->addSelect('series')
            ->leftJoin('series.vehicleBrand', 'brand')->addSelect('brand')
            ->leftJoin('brand.vehicleType', 'type')->addSelect('type')
            ->leftJoin('engine.stages', 'stage')->addSelect('stage')
            ->andWhere('engine.active = true');

        $needle = trim((string) $query);
        if ('' !== $needle) {
            $qb
                ->andWhere('LOWER(engine.name) LIKE :needle OR LOWER(model.name) LIKE :needle OR LOWER(series.name) LIKE :needle OR LOWER(brand.name) LIKE :needle OR LOWER(engine.engineCode) LIKE :needle')
                ->setParameter('needle', '%' . mb_strtolower($needle) . '%');
        }

        if (null !== $brandId && $brandId > 0) {
            $qb
                ->andWhere('brand.id = :brandId')
                ->setParameter('brandId', $brandId);
        }

        $engines = $qb
            ->orderBy('brand.name', 'ASC')
            ->addOrderBy('series.name', 'ASC')
            ->addOrderBy('model.name', 'ASC')
            ->addOrderBy('engine.sorting', 'ASC')
            ->setMaxResults(max(1, min(100, $limit)))
            ->getQuery()
            ->getResult();

        $items = array_map([$this, 'normalizeEngine'], array_filter($engines, static fn (mixed $engine): bool => $engine instanceof VehicleEngine));

        return [
            'filters' => [
                'query' => $needle,
                'brandId' => $brandId,
            ],
            'brands' => $this->getActiveBrands(),
            'items' => $items,
        ];
    }

    /**
     * @return list<VehicleBrand>
     */
    private function getActiveBrands(): array
    {
        return $this->vehicleBrandRepository->createQueryBuilder('brand')
            ->andWhere('brand.active = true')
            ->orderBy('brand.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeEngine(VehicleEngine $engine): array
    {
        $model = $engine->getVehicleModel();
        $series = $model?->getVehicleSeries();
        $brand = $series?->getVehicleBrand();
        $type = $brand?->getVehicleType();

        $activeStages = [];
        foreach ($engine->getStages() as $stage) {
            if (!$stage->isActive()) {
                continue;
            }

            $activeStages[] = [
                'id' => $stage->getId(),
                'name' => $stage->getName(),
                'hp' => $stage->getHp(),
                'nm' => $stage->getNm(),
                'price' => $stage->getPrice(),
            ];
        }

        return [
            'id' => $engine->getId(),
            'vehicleType' => $type?->getName(),
            'brand' => $brand?->getName(),
            'series' => $series?->getName(),
            'model' => $model?->getName(),
            'engine' => $engine->getName(),
            'engineCode' => $engine->getEngineCode(),
            'fuel' => $engine->getFuel(),
            'horsepower' => $engine->getHorsepower(),
            'torque' => $engine->getTorque(),
            'displacement' => $engine->getDisplacement(),
            'stages' => $activeStages,
        ];
    }
}
