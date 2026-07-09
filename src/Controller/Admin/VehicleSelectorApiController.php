<?php

namespace App\Controller\Admin;

use App\Entity\Ecu;
use App\Entity\VehicleBrand;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use App\Entity\VehicleSeries;
use App\Entity\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/vehicle-selector-api')]
final class VehicleSelectorApiController extends AbstractController
{
    private const POOL_KEY = 'complete';

    public function __construct(private readonly EntityManagerInterface $em) {}

    private function getPool(): ?VehicleDataPool
    {
        return $this->em->getRepository(VehicleDataPool::class)->findOneBy([
            'poolKey' => self::POOL_KEY,
            'active' => true,
        ]);
    }

    #[Route('/types', name: 'admin_vehicle_selector_types', methods: ['GET'])]
    public function types(): JsonResponse
    {
        $pool = $this->getPool();
        if (null === $pool) {
            return $this->json([]);
        }

        $items = $this->em->createQueryBuilder()
            ->select('t.id, t.name')
            ->from(VehicleType::class, 't')
            ->join('t.dataPools', 'p')
            ->where('t.active = true')
            ->andWhere('p.id = :poolId')
            ->andWhere('p.active = true')
            ->orderBy('t.sorting', 'ASC')
            ->addOrderBy('t.name', 'ASC')
            ->setParameter('poolId', $pool->getId())
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }

    #[Route('/brands/{typeId}', name: 'admin_vehicle_selector_brands', requirements: ['typeId' => '\d+'], methods: ['GET'])]
    public function brands(int $typeId): JsonResponse
    {
        $pool = $this->getPool();
        if (null === $pool) {
            return $this->json([]);
        }

        $items = $this->em->createQueryBuilder()
            ->select('b.id, b.name')
            ->from(VehicleBrand::class, 'b')
            ->join('b.vehicleType', 't')
            ->join('b.vehicleDataPool', 'p')
            ->where('t.id = :typeId')
            ->andWhere('p.id = :poolId')
            ->andWhere('p.active = true')
            ->andWhere('b.active = true')
            ->orderBy('b.name', 'ASC')
            ->setParameter('typeId', $typeId)
            ->setParameter('poolId', $pool->getId())
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }

    #[Route('/series/{brandId}', name: 'admin_vehicle_selector_series', requirements: ['brandId' => '\d+'], methods: ['GET'])]
    public function series(int $brandId): JsonResponse
    {
        $pool = $this->getPool();
        if (null === $pool) {
            return $this->json([]);
        }

        $items = $this->em->createQueryBuilder()
            ->select('s.id, s.name')
            ->from(VehicleSeries::class, 's')
            ->join('s.vehicleBrand', 'b')
            ->join('s.vehicleDataPool', 'p')
            ->where('b.id = :brandId')
            ->andWhere('p.id = :poolId')
            ->andWhere('p.active = true')
            ->andWhere('s.active = true')
            ->orderBy('s.name', 'ASC')
            ->setParameter('brandId', $brandId)
            ->setParameter('poolId', $pool->getId())
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }

    #[Route('/models/{seriesId}', name: 'admin_vehicle_selector_models', requirements: ['seriesId' => '\d+'], methods: ['GET'])]
    public function models(int $seriesId): JsonResponse
    {
        $pool = $this->getPool();
        if (null === $pool) {
            return $this->json([]);
        }

        $items = $this->em->createQueryBuilder()
            ->select('m.id, m.name')
            ->from(VehicleModel::class, 'm')
            ->join('m.vehicleSeries', 's')
            ->join('m.vehicleDataPool', 'p')
            ->where('s.id = :seriesId')
            ->andWhere('p.id = :poolId')
            ->andWhere('p.active = true')
            ->andWhere('m.active = true')
            ->orderBy('m.name', 'ASC')
            ->setParameter('seriesId', $seriesId)
            ->setParameter('poolId', $pool->getId())
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }

    #[Route('/ecus/{engineId}', name: 'admin_vehicle_selector_ecus', requirements: ['engineId' => '\d+'], methods: ['GET'])]
    public function ecus(int $engineId): JsonResponse
    {
        $ecus = $this->em->createQueryBuilder()
            ->select('e', 'master', 'manufacturer')
            ->from(Ecu::class, 'e')
            ->join('e.vehicleEngines', 'eng')
            ->leftJoin('e.masterEcu', 'master')
            ->leftJoin('e.manufacturer', 'manufacturer')
            ->where('eng.id = :engineId')
            ->orderBy('e.name', 'ASC')
            ->setParameter('engineId', $engineId)
            ->getQuery()
            ->getResult();

        $items = [];
        foreach ($ecus as $ecu) {
            if (!$ecu instanceof Ecu || Ecu::CONTROL_UNIT_KIND_MCU !== $ecu->getControlUnitKind()) {
                continue;
            }

            $name = trim($ecu->getName());
            $key = null !== $ecu->getMasterEcu()?->getId()
                ? 'master-' . $ecu->getMasterEcu()->getId()
                : ('' !== $name ? 'name-' . strtolower($name) : 'id-' . $ecu->getId());

            if (isset($items[$key])) {
                continue;
            }

            $items[$key] = [
                'id' => $ecu->getId(),
                'name' => $ecu->getName(),
                'manufacturer' => $ecu->getManufacturer()?->getName(),
                'key' => $key,
            ];
        }

        return $this->json(array_values($items));
    }

    #[Route('/ecu-links/{ecuId}', name: 'admin_vehicle_selector_ecu_links', requirements: ['ecuId' => '\d+'], methods: ['GET'])]
    public function ecuLinks(int $ecuId): JsonResponse
    {
        $ecu = $this->em->find(Ecu::class, $ecuId);
        if (!$ecu instanceof Ecu || Ecu::CONTROL_UNIT_KIND_MCU !== $ecu->getControlUnitKind()) {
            return $this->json([]);
        }

        $qb = $this->em->createQueryBuilder()
            ->select('related', 'eng', 'model', 'series', 'brand')
            ->from(Ecu::class, 'related')
            ->join('related.vehicleEngines', 'eng')
            ->leftJoin('eng.vehicleModel', 'model')
            ->leftJoin('model.vehicleSeries', 'series')
            ->leftJoin('series.vehicleBrand', 'brand')
            ->where('related.controlUnitKind = :kind')
            ->orderBy('brand.name', 'ASC')
            ->addOrderBy('series.name', 'ASC')
            ->addOrderBy('model.name', 'ASC')
            ->addOrderBy('eng.name', 'ASC')
            ->setParameter('kind', Ecu::CONTROL_UNIT_KIND_MCU);

        if (null !== $ecu->getVehicleDataPool()?->getId()) {
            $qb
                ->andWhere('related.vehicleDataPool = :pool')
                ->setParameter('pool', $ecu->getVehicleDataPool());
        }

        if (null !== $ecu->getMasterEcu()?->getId()) {
            $qb
                ->andWhere('related.masterEcu = :masterEcu')
                ->setParameter('masterEcu', $ecu->getMasterEcu());
        } else {
            $name = strtolower(trim($ecu->getName()));
            if ('' === $name) {
                return $this->json([]);
            }

            $qb
                ->andWhere('LOWER(related.name) = :ecuName')
                ->setParameter('ecuName', $name);
        }

        $items = [];
        foreach ($qb->getQuery()->getResult() as $relatedEcu) {
            if (!$relatedEcu instanceof Ecu) {
                continue;
            }

            foreach ($relatedEcu->getVehicleEngines() as $engine) {
                $engineId = $engine->getId();
                if (null === $engineId || isset($items[$engineId])) {
                    continue;
                }

                $model = $engine->getVehicleModel();
                $series = $model?->getVehicleSeries();
                $brand = $series?->getVehicleBrand();
                $parts = array_filter([
                    $brand?->getName(),
                    $series?->getName(),
                    $model?->getName(),
                    $engine->getName(),
                ], static fn (?string $part): bool => null !== $part && '' !== trim($part));

                $items[$engineId] = [
                    'id' => $engineId,
                    'name' => $engine->getName(),
                    'model' => $model?->getName(),
                    'series' => $series?->getName(),
                    'brand' => $brand?->getName(),
                    'label' => implode(' - ', $parts),
                ];
            }
        }

        return $this->json(array_values($items));
    }

    #[Route('/engines/{modelId}', name: 'admin_vehicle_selector_engines', requirements: ['modelId' => '\d+'], methods: ['GET'])]
    public function engines(int $modelId): JsonResponse
    {
        $pool = $this->getPool();
        if (null === $pool) {
            return $this->json([]);
        }

        $items = $this->em->createQueryBuilder()
            ->select('e.id, e.name, m.name AS model, s.name AS series, b.name AS brand')
            ->from(VehicleEngine::class, 'e')
            ->join('e.vehicleModel', 'm')
            ->leftJoin('m.vehicleSeries', 's')
            ->leftJoin('s.vehicleBrand', 'b')
            ->join('e.vehicleDataPool', 'p')
            ->where('m.id = :modelId')
            ->andWhere('p.id = :poolId')
            ->andWhere('p.active = true')
            ->andWhere('e.active = true')
            ->orderBy('e.name', 'ASC')
            ->setParameter('modelId', $modelId)
            ->setParameter('poolId', $pool->getId())
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }
}
