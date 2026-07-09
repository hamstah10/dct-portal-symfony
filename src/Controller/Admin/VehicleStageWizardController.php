<?php

namespace App\Controller\Admin;

use App\Entity\MediaAsset;
use App\Entity\VehicleBrand;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use App\Entity\VehicleSeries;
use App\Entity\VehicleStage;
use App\Entity\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class VehicleStageWizardController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[AdminRoute('/fahrzeugdatenbank/vehiclestage/wizard', name: 'admin_vehiclestage_wizard')]
    public function index(): Response
    {
        $pools = $this->em->createQueryBuilder()
            ->select('p.id, p.name, p.poolKey')
            ->from(VehicleDataPool::class, 'p')
            ->where('p.active = true')
            ->orderBy('p.name', 'ASC')
            ->getQuery()->getArrayResult();

        $mediaAssets = $this->em->createQueryBuilder()
            ->select('m.id, m.name, m.filePath')
            ->from(MediaAsset::class, 'm')
            ->where('m.active = true')
            ->andWhere('m.mediaType IN (:types)')
            ->setParameter('types', [MediaAsset::TYPE_ICON, MediaAsset::TYPE_IMAGE])
            ->orderBy('m.name', 'ASC')
            ->getQuery()->getArrayResult();

        return $this->render('admin/vehiclestage/wizard.html.twig', [
            'pools' => $pools,
            'mediaAssets' => $mediaAssets,
        ]);
    }

    #[Route('/admin/fahrzeugdatenbank/vehiclestage/wizard/api/types/{poolId}', name: 'admin_vehiclestage_wizard_api_types', requirements: ['poolId' => '\d+'], methods: ['GET'])]
    public function apiTypes(int $poolId): JsonResponse
    {
        $pool = $this->em->find(VehicleDataPool::class, $poolId);
        if (!$pool instanceof VehicleDataPool || !$pool->isActive()) {
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
            ->setParameter('poolId', $poolId)
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }

    #[Route('/admin/fahrzeugdatenbank/vehiclestage/wizard/api/brands/{poolId}/{typeId}', name: 'admin_vehiclestage_wizard_api_brands', requirements: ['poolId' => '\d+', 'typeId' => '\d+'], methods: ['GET'])]
    public function apiBrands(int $poolId, int $typeId): JsonResponse
    {
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
            ->setParameter('poolId', $poolId)
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }

    #[Route('/admin/fahrzeugdatenbank/vehiclestage/wizard/api/series/{poolId}/{brandId}', name: 'admin_vehiclestage_wizard_api_series', requirements: ['poolId' => '\d+', 'brandId' => '\d+'], methods: ['GET'])]
    public function apiSeries(int $poolId, int $brandId): JsonResponse
    {
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
            ->setParameter('poolId', $poolId)
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }

    #[Route('/admin/fahrzeugdatenbank/vehiclestage/wizard/api/models/{poolId}/{seriesId}', name: 'admin_vehiclestage_wizard_api_models', requirements: ['poolId' => '\d+', 'seriesId' => '\d+'], methods: ['GET'])]
    public function apiModels(int $poolId, int $seriesId): JsonResponse
    {
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
            ->setParameter('poolId', $poolId)
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }

    #[Route('/admin/fahrzeugdatenbank/vehiclestage/wizard/api/engines/{poolId}/{modelId}', name: 'admin_vehiclestage_wizard_api_engines', requirements: ['poolId' => '\d+', 'modelId' => '\d+'], methods: ['GET'])]
    public function apiEngines(int $poolId, int $modelId): JsonResponse
    {
        $items = $this->em->createQueryBuilder()
            ->select('e.id, e.name, e.horsepower, e.fuel, e.displacement')
            ->from(VehicleEngine::class, 'e')
            ->join('e.vehicleModel', 'm')
            ->join('e.vehicleDataPool', 'p')
            ->where('m.id = :modelId')
            ->andWhere('p.id = :poolId')
            ->andWhere('p.active = true')
            ->andWhere('e.active = true')
            ->orderBy('e.name', 'ASC')
            ->setParameter('modelId', $modelId)
            ->setParameter('poolId', $poolId)
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }

    #[Route('/admin/fahrzeugdatenbank/vehiclestage/wizard/api/stages/{engineId}', name: 'admin_vehiclestage_wizard_api_stages', requirements: ['engineId' => '\d+'], methods: ['GET'])]
    public function apiStages(int $engineId): JsonResponse
    {
        $items = $this->em->createQueryBuilder()
            ->select('s.id, s.name, s.hp, s.nm, s.price, s.active')
            ->from(VehicleStage::class, 's')
            ->join('s.vehicleEngine', 'e')
            ->join('s.vehicleDataPool', 'p')
            ->where('e.id = :engineId')
            ->andWhere('p.active = true')
            ->orderBy('s.name', 'ASC')
            ->setParameter('engineId', $engineId)
            ->getQuery()->getArrayResult();

        return $this->json($items);
    }

    #[Route('/admin/fahrzeugdatenbank/vehiclestage/wizard/save', name: 'admin_vehiclestage_wizard_save', methods: ['POST'])]
    public function save(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid data'], 400);
        }

        $engineId = isset($data['engineId']) ? (int) $data['engineId'] : 0;
        $poolId   = isset($data['poolId'])   ? (int) $data['poolId']   : 0;

        if ($engineId <= 0 || $poolId <= 0) {
            return $this->json(['error' => 'Motor und Datenbestand erforderlich'], 400);
        }

        $engine = $this->em->find(VehicleEngine::class, $engineId);
        $pool   = $this->em->find(VehicleDataPool::class, $poolId);

        if (null === $engine || !$pool instanceof VehicleDataPool || !$pool->isActive()) {
            return $this->json(['error' => 'Motor oder Datenbestand nicht gefunden'], 404);
        }

        $name = trim((string) ($data['name'] ?? ''));
        if ('' === $name) {
            return $this->json(['error' => 'Name ist erforderlich'], 400);
        }

        $iconAsset = null;
        $iconAssetId = isset($data['iconMediaAssetId']) ? (int) $data['iconMediaAssetId'] : 0;
        if ($iconAssetId > 0) {
            $iconAsset = $this->em->find(MediaAsset::class, $iconAssetId);
        }

        $stage = (new VehicleStage())
            ->setVehicleEngine($engine)
            ->setVehicleDataPool($pool)
            ->setName($name)
            ->setDescription(isset($data['description']) ? (string) $data['description'] : null)
            ->setPrice(isset($data['price']) && '' !== trim((string) $data['price']) ? (string) $data['price'] : null)
            ->setHp(isset($data['hp']) && '' !== trim((string) $data['hp']) ? (int) $data['hp'] : null)
            ->setNm(isset($data['nm']) && '' !== trim((string) $data['nm']) ? (int) $data['nm'] : null)
            ->setOriginalPs(isset($data['originalPs']) && '' !== trim((string) $data['originalPs']) ? (int) $data['originalPs'] : null)
            ->setOriginalNm(isset($data['originalNm']) && '' !== trim((string) $data['originalNm']) ? (int) $data['originalNm'] : null)
            ->setIconMediaAsset($iconAsset)
            ->setActive((bool) ($data['active'] ?? true));

        $this->em->persist($stage);
        $this->em->flush();

        return $this->json([
            'id' => $stage->getId(),
            'editUrl' => $this->generateUrl('admin_vehicle_stage_edit', [
                'entityId' => $stage->getId(),
            ]),
        ]);
    }
}
