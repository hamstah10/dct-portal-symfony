<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Ecu;
use App\Entity\EcuOptionMaster;
use App\Entity\EinbauHinweis;
use App\Entity\MediaAsset;
use App\Entity\Portal;
use App\Entity\Textbaustein;
use App\Entity\VehicleBrand;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use App\Entity\VehicleSeries;
use App\Entity\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class InstallationHintApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/operations-api/installation-hints', name: 'superadmin_portal_installation_hints_list', methods: ['GET'])]
    public function list(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsPortal($request, $portalRuntimeResolver);

        $query = trim((string) $request->query->get('q', ''));
        $active = trim((string) $request->query->get('active', ''));
        $limit = max(10, min(250, (int) $request->query->get('limit', 150)));

        $qb = $this->entityManager->getRepository(EinbauHinweis::class)->createQueryBuilder('hint')
            ->leftJoin('hint.ecuOptionMaster', 'optionMaster')->addSelect('optionMaster')
            ->leftJoin('hint.activeEcu', 'activeEcu')->addSelect('activeEcu')
            ->leftJoin('hint.activeEcus', 'activeEcus')->addSelect('activeEcus')
            ->leftJoin('hint.vehicleEngines', 'engine')->addSelect('engine')
            ->leftJoin('engine.vehicleModel', 'engineModel')->addSelect('engineModel')
            ->leftJoin('engineModel.vehicleSeries', 'engineSeries')->addSelect('engineSeries')
            ->leftJoin('engineSeries.vehicleBrand', 'engineBrand')->addSelect('engineBrand')
            ->leftJoin('hint.vehicleModels', 'model')->addSelect('model')
            ->leftJoin('model.vehicleSeries', 'modelSeries')->addSelect('modelSeries')
            ->leftJoin('modelSeries.vehicleBrand', 'modelBrand')->addSelect('modelBrand')
            ->leftJoin('hint.images', 'image')->addSelect('image')
            ->setMaxResults($limit)
            ->orderBy('hint.updatedAt', 'DESC')
            ->addOrderBy('hint.title', 'ASC');

        if ('' !== $query) {
            $needle = '%' . mb_strtolower($query) . '%';
            $qb
                ->andWhere('LOWER(hint.title) LIKE :needle OR LOWER(hint.description) LIKE :needle OR LOWER(optionMaster.name) LIKE :needle OR LOWER(engine.name) LIKE :needle OR LOWER(engineModel.name) LIKE :needle OR LOWER(engineBrand.name) LIKE :needle OR LOWER(model.name) LIKE :needle OR LOWER(modelBrand.name) LIKE :needle')
                ->setParameter('needle', $needle);
        }

        if ('true' === $active || '1' === $active) {
            $qb->andWhere('hint.active = :active')->setParameter('active', true);
        } elseif ('false' === $active || '0' === $active) {
            $qb->andWhere('hint.active = :active')->setParameter('active', false);
        }

        $rows = $qb->getQuery()->getResult();

        return $this->json(['data' => array_map(fn (EinbauHinweis $hint): array => $this->serializeHintSummary($hint), $rows)]);
    }

    #[Route('/operations-api/installation-hints/{id}', name: 'superadmin_portal_installation_hints_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $hint = $this->findHint($id);

        return $this->json(['data' => $this->serializeHint($hint)]);
    }

    #[Route('/operations-api/installation-hints', name: 'superadmin_portal_installation_hints_create', methods: ['POST'])]
    public function create(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $body = $this->readJsonBody($request);

        $hint = new EinbauHinweis();
        try {
            $this->applyPayload($hint, $body, $portal->getVehicleDataPool());
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], 400);
        }
        $this->entityManager->persist($hint);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeHint($hint)], 201);
    }

    #[Route('/operations-api/installation-hints/{id}', name: 'superadmin_portal_installation_hints_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $hint = $this->findHint($id);

        try {
            $this->applyPayload($hint, $this->readJsonBody($request), $portal->getVehicleDataPool());
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], 400);
        }
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeHint($hint)]);
    }

    #[Route('/operations-api/installation-hints/{id}', name: 'superadmin_portal_installation_hints_deactivate', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deactivate(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $hint = $this->findHint($id);
        $hint->setActive(false);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeHint($hint)]);
    }

    #[Route('/operations-api/installation-hints/{id}/images', name: 'superadmin_portal_installation_hints_images_upload', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function uploadImages(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $hint = $this->findHint($id);

        $files = $request->files->get('images', $request->files->get('files', []));
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        if (!is_array($files) || [] === $files) {
            return $this->json(['message' => 'Keine Bilder erhalten.'], 400);
        }

        $nextSorting = 0;
        foreach ($hint->getImages() as $image) {
            $nextSorting = max($nextSorting, $image->getSorting() + 10);
        }

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $mimeType = $file->getClientMimeType() ?: $file->getMimeType();
            if (null !== $mimeType && !str_starts_with((string) $mimeType, 'image/')) {
                continue;
            }

            $originalName = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);
            $fileSize = $file->getSize();
            $path = $this->storeUpload($file, 'uploads/media');

            $asset = new MediaAsset();
            $asset->setName(trim($originalName) ?: 'Einbau-Hinweis Bild');
            $asset->setFilePath($path);
            $asset->setMediaType(MediaAsset::TYPE_IMAGE);
            $asset->setMimeType($mimeType ? trim((string) $mimeType) : null);
            $asset->setFileSize(is_int($fileSize) ? $fileSize : null);
            $asset->setSorting($nextSorting);
            $this->entityManager->persist($asset);
            $hint->addImage($asset);
            $nextSorting += 10;
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeHint($hint)]);
    }

    #[Route('/operations-api/installation-hints/{id}/images/order', name: 'superadmin_portal_installation_hints_images_order', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function reorderImages(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $hint = $this->findHint($id);
        $body = $this->readJsonBody($request);
        $imageIds = array_values(array_filter(array_map('intval', $body['imageIds'] ?? [])));

        $imagesById = [];
        foreach ($hint->getImages() as $image) {
            if (null !== $image->getId()) {
                $imagesById[$image->getId()] = $image;
            }
        }

        $position = 0;
        foreach ($imageIds as $imageId) {
            $image = $imagesById[$imageId] ?? null;
            if (!$image instanceof MediaAsset) {
                continue;
            }
            $image->setSorting($position);
            unset($imagesById[$imageId]);
            $position += 10;
        }

        foreach ($imagesById as $image) {
            $image->setSorting($position);
            $position += 10;
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeHint($hint)]);
    }

    #[Route('/operations-api/installation-hints/{id}/images/{imageId}', name: 'superadmin_portal_installation_hints_images_delete', requirements: ['id' => '\d+', 'imageId' => '\d+'], methods: ['DELETE'])]
    public function deleteImage(int $id, int $imageId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $hint = $this->findHint($id);

        foreach ($hint->getImages()->toArray() as $image) {
            if ($image instanceof MediaAsset && $image->getId() === $imageId) {
                $hint->removeImage($image);
                break;
            }
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeHint($hint)]);
    }

    #[Route('/operations-api/installation-hints/{id}/images/{imageId}/annotations', name: 'superadmin_portal_installation_hints_images_annotations', requirements: ['id' => '\d+', 'imageId' => '\d+'], methods: ['PUT'])]
    public function saveImageAnnotations(int $id, int $imageId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $hint = $this->findHint($id);
        $image = $this->findHintImage($hint, $imageId);
        if (!$image instanceof MediaAsset) {
            return $this->json(['message' => 'Bild nicht gefunden.'], 404);
        }

        $body = $this->readJsonBody($request);
        if (!array_key_exists('annotations', $body)) {
            return $this->json(['message' => 'Annotationen fehlen.'], 400);
        }

        $annotations = is_array($body['annotations']) ? $body['annotations'] : [];
        $image->setAnnotations($annotations ?: null);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeHint($hint)]);
    }

    #[Route('/operations-api/installation-hints/lookup/options', name: 'superadmin_portal_installation_hints_lookup_options', methods: ['GET'])]
    public function lookupOptions(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $query = trim((string) $request->query->get('q', ''));

        $qb = $this->entityManager->getRepository(EcuOptionMaster::class)->createQueryBuilder('optionMaster')
            ->orderBy('optionMaster.name', 'ASC')
            ->setMaxResults(250);

        if ('' !== $query) {
            $qb
                ->andWhere('LOWER(optionMaster.name) LIKE :needle OR LOWER(optionMaster.slug) LIKE :needle')
                ->setParameter('needle', '%' . mb_strtolower($query) . '%');
        }

        $rows = $qb->getQuery()->getResult();

        return $this->json(['data' => array_map(static fn (EcuOptionMaster $option): array => [
            'id' => $option->getId(),
            'name' => $option->getName(),
            'slug' => $option->getSlug(),
        ], $rows)]);
    }

    #[Route('/operations-api/installation-hints/lookup/text-snippets', name: 'superadmin_portal_installation_hints_lookup_text_snippets', methods: ['GET'])]
    public function lookupTextSnippets(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsPortal($request, $portalRuntimeResolver);

        $items = $this->entityManager->getRepository(Textbaustein::class)
            ->createQueryBuilder('snippet')
            ->andWhere('snippet.active = :active')
            ->setParameter('active', true)
            ->orderBy('snippet.sorting', 'ASC')
            ->addOrderBy('snippet.key', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(['data' => array_map(static fn (Textbaustein $snippet): array => [
            'id' => $snippet->getId(),
            'key' => $snippet->getKey(),
            'type' => $snippet->getType(),
            'textDe' => $snippet->getTextDe() ?? '',
            'textEn' => $snippet->getTextEn() ?? '',
        ], $items)]);
    }

    #[Route('/operations-api/installation-hints/lookup/vehicles', name: 'superadmin_portal_installation_hints_lookup_vehicles', methods: ['GET'])]
    public function lookupVehicles(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $pool = $portal->getVehicleDataPool();
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['data' => []]);
        }

        $query = trim((string) $request->query->get('q', ''));
        if (mb_strlen($query) < 2) {
            return $this->json(['data' => []]);
        }

        $needle = '%' . mb_strtolower($query) . '%';
        $rows = $this->entityManager->getRepository(VehicleEngine::class)->createQueryBuilder('engine')
            ->leftJoin('engine.vehicleModel', 'model')->addSelect('model')
            ->leftJoin('model.vehicleSeries', 'series')->addSelect('series')
            ->leftJoin('series.vehicleBrand', 'brand')->addSelect('brand')
            ->leftJoin('engine.ecuRelations', 'ecu')->addSelect('ecu')
            ->leftJoin('ecu.masterEcu', 'masterEcu')->addSelect('masterEcu')
            ->leftJoin('ecu.manufacturer', 'ecuManufacturer')->addSelect('ecuManufacturer')
            ->andWhere('engine.active = :active')
            ->andWhere('engine.vehicleDataPool = :pool')
            ->andWhere('LOWER(engine.name) LIKE :needle OR LOWER(engine.engineCode) LIKE :needle OR LOWER(engine.fuel) LIKE :needle OR LOWER(model.name) LIKE :needle OR LOWER(series.name) LIKE :needle OR LOWER(brand.name) LIKE :needle')
            ->setParameter('active', true)
            ->setParameter('pool', $pool)
            ->setParameter('needle', $needle)
            ->orderBy('brand.name', 'ASC')
            ->addOrderBy('series.name', 'ASC')
            ->addOrderBy('model.name', 'ASC')
            ->addOrderBy('engine.name', 'ASC')
            ->setMaxResults(35)
            ->getQuery()
            ->getResult();

        return $this->json(['data' => array_map(fn (VehicleEngine $engine): array => $this->serializeEngine($engine), $rows)]);
    }

    #[Route('/operations-api/installation-hints/lookup/types', name: 'superadmin_portal_installation_hints_lookup_types', methods: ['GET'])]
    public function lookupTypes(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $pool = $portal->getVehicleDataPool();
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['data' => []]);
        }

        $rows = $this->entityManager->getRepository(VehicleType::class)->createQueryBuilder('type')
            ->join('type.dataPools', 'pool')
            ->andWhere('type.active = :active')
            ->andWhere('pool = :pool')
            ->setParameter('active', true)
            ->setParameter('pool', $pool)
            ->orderBy('type.sorting', 'ASC')
            ->addOrderBy('type.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(['data' => array_map(static fn (VehicleType $type): array => [
            'id' => $type->getId(),
            'name' => $type->getName(),
            'label' => $type->getName(),
        ], $rows)]);
    }

    #[Route('/operations-api/installation-hints/lookup/brands/{typeId}', name: 'superadmin_portal_installation_hints_lookup_brands', requirements: ['typeId' => '\d+'], methods: ['GET'])]
    public function lookupBrands(int $typeId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $pool = $portal->getVehicleDataPool();
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['data' => []]);
        }

        $rows = $this->entityManager->getRepository(VehicleBrand::class)->createQueryBuilder('brand')
            ->join('brand.vehicleType', 'type')
            ->andWhere('type.id = :typeId')
            ->andWhere('brand.vehicleDataPool = :pool')
            ->andWhere('brand.active = :active')
            ->setParameter('typeId', $typeId)
            ->setParameter('pool', $pool)
            ->setParameter('active', true)
            ->orderBy('brand.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(['data' => array_map(static fn (VehicleBrand $brand): array => [
            'id' => $brand->getId(),
            'name' => $brand->getName(),
            'label' => $brand->getName(),
        ], $rows)]);
    }

    #[Route('/operations-api/installation-hints/lookup/series/{brandId}', name: 'superadmin_portal_installation_hints_lookup_series', requirements: ['brandId' => '\d+'], methods: ['GET'])]
    public function lookupSeries(int $brandId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $pool = $portal->getVehicleDataPool();
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['data' => []]);
        }

        $rows = $this->entityManager->getRepository(VehicleSeries::class)->createQueryBuilder('series')
            ->join('series.vehicleBrand', 'brand')
            ->andWhere('brand.id = :brandId')
            ->andWhere('series.vehicleDataPool = :pool')
            ->andWhere('series.active = :active')
            ->setParameter('brandId', $brandId)
            ->setParameter('pool', $pool)
            ->setParameter('active', true)
            ->orderBy('series.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(['data' => array_map(static fn (VehicleSeries $series): array => [
            'id' => $series->getId(),
            'name' => $series->getName(),
            'label' => $series->getName(),
        ], $rows)]);
    }

    #[Route('/operations-api/installation-hints/lookup/models/{seriesId}', name: 'superadmin_portal_installation_hints_lookup_models', requirements: ['seriesId' => '\d+'], methods: ['GET'])]
    public function lookupModels(int $seriesId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $pool = $portal->getVehicleDataPool();
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['data' => []]);
        }

        $rows = $this->entityManager->getRepository(VehicleModel::class)->createQueryBuilder('model')
            ->join('model.vehicleSeries', 'series')->addSelect('series')
            ->leftJoin('series.vehicleBrand', 'brand')->addSelect('brand')
            ->andWhere('series.id = :seriesId')
            ->andWhere('model.vehicleDataPool = :pool')
            ->andWhere('model.active = :active')
            ->setParameter('seriesId', $seriesId)
            ->setParameter('pool', $pool)
            ->setParameter('active', true)
            ->orderBy('model.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(['data' => array_map(fn (VehicleModel $model): array => $this->serializeModel($model), $rows)]);
    }

    #[Route('/operations-api/installation-hints/lookup/engines/{modelId}', name: 'superadmin_portal_installation_hints_lookup_engines', requirements: ['modelId' => '\d+'], methods: ['GET'])]
    public function lookupEngines(int $modelId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $pool = $portal->getVehicleDataPool();
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['data' => []]);
        }

        $rows = $this->entityManager->getRepository(VehicleEngine::class)->createQueryBuilder('engine')
            ->join('engine.vehicleModel', 'model')->addSelect('model')
            ->leftJoin('model.vehicleSeries', 'series')->addSelect('series')
            ->leftJoin('series.vehicleBrand', 'brand')->addSelect('brand')
            ->leftJoin('engine.ecuRelations', 'ecu')->addSelect('ecu')
            ->leftJoin('ecu.masterEcu', 'masterEcu')->addSelect('masterEcu')
            ->leftJoin('ecu.manufacturer', 'ecuManufacturer')->addSelect('ecuManufacturer')
            ->andWhere('model.id = :modelId')
            ->andWhere('engine.vehicleDataPool = :pool')
            ->andWhere('engine.active = :active')
            ->setParameter('modelId', $modelId)
            ->setParameter('pool', $pool)
            ->setParameter('active', true)
            ->orderBy('engine.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(['data' => array_map(fn (VehicleEngine $engine): array => $this->serializeEngine($engine), $rows)]);
    }

    #[Route('/operations-api/installation-hints/lookup/ecus/{engineId}', name: 'superadmin_portal_installation_hints_lookup_ecus', requirements: ['engineId' => '\d+'], methods: ['GET'])]
    public function lookupEcus(int $engineId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $pool = $portal->getVehicleDataPool();
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['data' => []]);
        }

        $engine = $this->entityManager->getRepository(VehicleEngine::class)->createQueryBuilder('engine')
            ->leftJoin('engine.ecuRelations', 'ecu')->addSelect('ecu')
            ->leftJoin('ecu.masterEcu', 'masterEcu')->addSelect('masterEcu')
            ->leftJoin('ecu.manufacturer', 'ecuManufacturer')->addSelect('ecuManufacturer')
            ->andWhere('engine.id = :engineId')
            ->andWhere('engine.vehicleDataPool = :pool')
            ->setParameter('engineId', $engineId)
            ->setParameter('pool', $pool)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$engine instanceof VehicleEngine) {
            return $this->json(['data' => []]);
        }

        $items = [];
        foreach ($engine->getEcuRelations() as $ecu) {
            if (!$ecu instanceof Ecu || Ecu::CONTROL_UNIT_KIND_MCU !== $ecu->getControlUnitKind()) {
                continue;
            }

            $key = $this->ecuKey($ecu);
            if (isset($items[$key])) {
                continue;
            }

            $items[$key] = $this->serializeEcu($ecu);
        }

        return $this->json(['data' => array_values($items)]);
    }

    #[Route('/operations-api/installation-hints/lookup/ecu-links/{ecuId}', name: 'superadmin_portal_installation_hints_lookup_ecu_links', requirements: ['ecuId' => '\d+'], methods: ['GET'])]
    public function lookupEcuLinks(int $ecuId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $this->assertOperationsPortal($request, $portalRuntimeResolver);
        $pool = $portal->getVehicleDataPool();
        if (!$pool instanceof VehicleDataPool) {
            return $this->json(['data' => []]);
        }

        $ecu = $this->entityManager->find(Ecu::class, $ecuId);
        if (!$ecu instanceof Ecu || Ecu::CONTROL_UNIT_KIND_MCU !== $ecu->getControlUnitKind()) {
            return $this->json(['data' => []]);
        }

        $qb = $this->entityManager->getRepository(Ecu::class)->createQueryBuilder('related')
            ->join('related.vehicleEngines', 'engine')->addSelect('engine')
            ->leftJoin('engine.vehicleModel', 'model')->addSelect('model')
            ->leftJoin('model.vehicleSeries', 'series')->addSelect('series')
            ->leftJoin('series.vehicleBrand', 'brand')->addSelect('brand')
            ->leftJoin('engine.ecuRelations', 'engineEcu')->addSelect('engineEcu')
            ->leftJoin('engineEcu.masterEcu', 'engineEcuMaster')->addSelect('engineEcuMaster')
            ->leftJoin('engineEcu.manufacturer', 'engineEcuManufacturer')->addSelect('engineEcuManufacturer')
            ->andWhere('related.controlUnitKind = :kind')
            ->andWhere('engine.vehicleDataPool = :pool')
            ->setParameter('kind', Ecu::CONTROL_UNIT_KIND_MCU)
            ->setParameter('pool', $pool)
            ->orderBy('brand.name', 'ASC')
            ->addOrderBy('series.name', 'ASC')
            ->addOrderBy('model.name', 'ASC')
            ->addOrderBy('engine.name', 'ASC');

        if (null !== $ecu->getMasterEcu()?->getId()) {
            $qb
                ->andWhere('related.masterEcu = :masterEcu')
                ->setParameter('masterEcu', $ecu->getMasterEcu());
        } else {
            $name = mb_strtolower(trim($ecu->getName()));
            if ('' === $name) {
                return $this->json(['data' => []]);
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
                if (!$engine instanceof VehicleEngine || $engine->getVehicleDataPool()?->getId() !== $pool->getId()) {
                    continue;
                }

                $engineId = $engine->getId();
                if (null === $engineId || isset($items[$engineId])) {
                    continue;
                }

                $items[$engineId] = $this->serializeEngine($engine);
            }
        }

        return $this->json(['data' => array_values($items)]);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function applyPayload(EinbauHinweis $hint, array $body, ?VehicleDataPool $pool): void
    {
        $title = trim((string) ($body['title'] ?? ''));
        if ('' === $title) {
            throw new \InvalidArgumentException('Titel darf nicht leer sein.');
        }

        $hint->setTitle($title);
        $hint->setDescription($this->nullableText($body['description'] ?? null));
        $hint->setActive(filter_var($body['active'] ?? true, \FILTER_VALIDATE_BOOL));

        $optionMasterId = (int) ($body['ecuOptionMasterId'] ?? 0);
        $hint->setEcuOptionMaster($optionMasterId > 0 ? $this->entityManager->find(EcuOptionMaster::class, $optionMasterId) : null);

        $this->syncEngines($hint, $body['vehicleEngineIds'] ?? [], $pool);
        $this->syncModels($hint, $body['vehicleModelIds'] ?? [], $pool);
        $activeEcuIds = $body['activeEcuIds'] ?? null;
        if (!is_array($activeEcuIds) && isset($body['activeEcuId'])) {
            $activeEcuIds = [(int) $body['activeEcuId']];
        }
        $this->syncActiveEcus($hint, $activeEcuIds ?? []);

        $imageAnnotations = $body['imageAnnotations'] ?? null;
        if (is_array($imageAnnotations)) {
            foreach ($hint->getImages() as $image) {
                $id = $image->getId();
                if (null === $id) {
                    continue;
                }
                $payload = $imageAnnotations[(string) $id] ?? $imageAnnotations[$id] ?? null;
                if (is_array($payload)) {
                    $image->setAnnotations($payload);
                }
            }
        }
    }

    /**
     * @param mixed $engineIds
     */
    private function syncEngines(EinbauHinweis $hint, mixed $engineIds, ?VehicleDataPool $pool): void
    {
        $ids = is_array($engineIds) ? array_values(array_unique(array_filter(array_map('intval', $engineIds)))) : [];
        $allowed = [];

        if ([] !== $ids) {
            $qb = $this->entityManager->getRepository(VehicleEngine::class)->createQueryBuilder('engine')
                ->andWhere('engine.id IN (:ids)')
                ->setParameter('ids', $ids);
            if ($pool instanceof VehicleDataPool) {
                $qb->andWhere('engine.vehicleDataPool = :pool')->setParameter('pool', $pool);
            }
            foreach ($qb->getQuery()->getResult() as $engine) {
                if ($engine instanceof VehicleEngine && null !== $engine->getId()) {
                    $allowed[$engine->getId()] = $engine;
                }
            }
        }

        foreach ($hint->getVehicleEngines()->toArray() as $engine) {
            if (null === $engine->getId() || !isset($allowed[$engine->getId()])) {
                $hint->removeVehicleEngine($engine);
            }
        }

        foreach ($allowed as $engine) {
            $hint->addVehicleEngine($engine);
        }
    }

    /**
     * @param mixed $modelIds
     */
    private function syncModels(EinbauHinweis $hint, mixed $modelIds, ?VehicleDataPool $pool): void
    {
        $ids = is_array($modelIds) ? array_values(array_unique(array_filter(array_map('intval', $modelIds)))) : [];
        $allowed = [];

        if ([] !== $ids) {
            $qb = $this->entityManager->getRepository(VehicleModel::class)->createQueryBuilder('model')
                ->andWhere('model.id IN (:ids)')
                ->setParameter('ids', $ids);
            if ($pool instanceof VehicleDataPool) {
                $qb->andWhere('model.vehicleDataPool = :pool')->setParameter('pool', $pool);
            }
            foreach ($qb->getQuery()->getResult() as $model) {
                if ($model instanceof VehicleModel && null !== $model->getId()) {
                    $allowed[$model->getId()] = $model;
                }
            }
        }

        foreach ($hint->getVehicleModels()->toArray() as $model) {
            if (null === $model->getId() || !isset($allowed[$model->getId()])) {
                $hint->removeVehicleModel($model);
            }
        }

        foreach ($allowed as $model) {
            $hint->addVehicleModel($model);
        }
    }

    /**
     * @param mixed $activeEcuIds
     */
    private function syncActiveEcus(EinbauHinweis $hint, mixed $activeEcuIds): void
    {
        $ids = is_array($activeEcuIds) ? array_values(array_unique(array_filter(array_map('intval', $activeEcuIds)))) : [];
        $allowed = [];

        if ([] !== $ids) {
            foreach ($hint->getVehicleEngines() as $engine) {
                foreach ($engine->getEcuRelations() as $ecu) {
                    if (
                        $ecu instanceof Ecu
                        && null !== $ecu->getId()
                        && in_array($ecu->getId(), $ids, true)
                        && Ecu::CONTROL_UNIT_KIND_MCU === $ecu->getControlUnitKind()
                    ) {
                        $allowed[$ecu->getId()] = $ecu;
                    }
                }
            }
        }

        foreach ($hint->getActiveEcus()->toArray() as $ecu) {
            if (!$ecu instanceof Ecu || null === $ecu->getId() || !isset($allowed[$ecu->getId()])) {
                $hint->removeActiveEcu($ecu);
            }
        }

        foreach ($allowed as $ecu) {
            $hint->addActiveEcu($ecu);
        }

        $hint->setActiveEcu(reset($allowed) ?: null);
    }

    private function assertOperationsPortal(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Portal
    {
        $systemType = (string) $request->attributes->get('_brand_system_type', '');
        if (SystemType::SUPERADMIN === $systemType) {
            throw new NotFoundHttpException('Installation hints need a portal context.');
        }

        $portal = $portalRuntimeResolver->requirePortalSystem($request);
        if ($portal->getPortalType() !== Portal::TYPE_SUPERADMIN_PORTAL) {
            throw new NotFoundHttpException('Superadmin operations API not available for this portal.');
        }

        return $portal;
    }

    private function findHint(int $id): EinbauHinweis
    {
        $hint = $this->entityManager->find(EinbauHinweis::class, $id);
        if (!$hint instanceof EinbauHinweis) {
            throw new NotFoundHttpException('Einbau-Hinweis nicht gefunden.');
        }

        return $hint;
    }

    private function findHintImage(EinbauHinweis $hint, int $imageId): ?MediaAsset
    {
        foreach ($hint->getImages() as $image) {
            if ($image instanceof MediaAsset && $image->getId() === $imageId) {
                return $image;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function readJsonBody(Request $request): array
    {
        $body = json_decode((string) $request->getContent(), true);
        if (!is_array($body)) {
            return [];
        }

        return $body;
    }

    private function storeUpload(UploadedFile $uploadedFile, string $relativeUploadDir): string
    {
        $absoluteUploadDir = $this->getParameter('kernel.project_dir') . '/public/' . $relativeUploadDir;
        if (!is_dir($absoluteUploadDir)) {
            mkdir($absoluteUploadDir, 0775, true);
        }

        $extension = $uploadedFile->guessExtension() ?: $uploadedFile->getClientOriginalExtension() ?: 'bin';
        $fileName = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), strtolower($extension));
        $uploadedFile->move($absoluteUploadDir, $fileName);

        return $relativeUploadDir . '/' . $fileName;
    }

    private function serializeHintSummary(EinbauHinweis $hint): array
    {
        return [
            'id' => $hint->getId(),
            'title' => $hint->getTitle(),
            'description' => $hint->getDescription(),
            'active' => $hint->isActive(),
            'ecuOptionMaster' => $this->serializeOptionMaster($hint->getEcuOptionMaster()),
            'activeEcu' => $this->serializeEcu($hint->getActiveEcu()),
            'activeEcus' => array_values(array_map(fn (Ecu $ecu): array => $this->serializeEcu($ecu), $this->effectiveActiveEcus($hint))),
            'vehicleEngineCount' => $hint->getVehicleEngines()->count(),
            'vehicleModelCount' => $hint->getVehicleModels()->count(),
            'imageCount' => $hint->getImages()->count(),
            'thumbnailImage' => $this->serializeHintThumbnail($hint),
            'brandsLabel' => $hint->getBrandsLabel(),
            'brands' => $this->serializeHintBrands($hint),
            'vehicleAssignments' => $this->serializeHintAssignments($hint),
            'createdAt' => $hint->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $hint->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    private function serializeHintThumbnail(EinbauHinweis $hint): ?array
    {
        $image = $hint->getImages()->first();

        return $image instanceof MediaAsset ? $this->serializeImage($image) : null;
    }

    /**
     * @return array<int, array{type: string, model: string|null, engine: string|null}>
     */
    private function serializeHintAssignments(EinbauHinweis $hint): array
    {
        $assignments = [];
        $seen = [];

        foreach ($hint->getVehicleEngines() as $engine) {
            $model = $engine->getVehicleModel();
            $key = 'engine-' . ($engine->getId() ?? spl_object_id($engine));
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $assignments[] = [
                'type' => 'engine',
                'model' => $model instanceof VehicleModel ? $this->serializeModel($model)['label'] : null,
                'engine' => $engine->getName(),
            ];
        }

        foreach ($hint->getVehicleModels() as $model) {
            $key = 'model-' . ($model->getId() ?? spl_object_id($model));
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $assignments[] = [
                'type' => 'model',
                'model' => $this->serializeModel($model)['label'],
                'engine' => null,
            ];
        }

        usort($assignments, static function (array $a, array $b): int {
            $modelCompare = strnatcasecmp((string) ($a['model'] ?? ''), (string) ($b['model'] ?? ''));
            if (0 !== $modelCompare) {
                return $modelCompare;
            }

            return strnatcasecmp((string) ($a['engine'] ?? ''), (string) ($b['engine'] ?? ''));
        });

        return $assignments;
    }

    /**
     * @return array<int, array{id: int|null, name: string, iconSrc: string|null}>
     */
    private function serializeHintBrands(EinbauHinweis $hint): array
    {
        $brands = [];

        foreach ($hint->getVehicleEngines() as $engine) {
            $brand = $engine->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand();
            if ($brand instanceof VehicleBrand) {
                $brands[$brand->getId() ?? $brand->getName()] = [
                    'id' => $brand->getId(),
                    'name' => $brand->getName(),
                    'iconSrc' => $this->vehicleBrandIconSrc($brand),
                ];
            }
        }

        foreach ($hint->getVehicleModels() as $model) {
            $brand = $model->getVehicleSeries()?->getVehicleBrand();
            if ($brand instanceof VehicleBrand) {
                $brands[$brand->getId() ?? $brand->getName()] = [
                    'id' => $brand->getId(),
                    'name' => $brand->getName(),
                    'iconSrc' => $this->vehicleBrandIconSrc($brand),
                ];
            }
        }

        usort($brands, static fn (array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));

        return array_values($brands);
    }

    private function vehicleBrandIconSrc(VehicleBrand $brand): ?string
    {
        $path = trim((string) ($brand->getImageMediaAsset()?->getFilePath() ?? ''));
        if ('' === $path) {
            $path = trim((string) ($brand->getImage() ?? ''));
        }

        $master = $brand->getMasterBrand();
        if ('' === $path && null !== $master) {
            $path = trim((string) ($master->getImageMediaAsset()?->getFilePath() ?? ''));
            if ('' === $path) {
                $path = trim((string) ($master->getImage() ?? ''));
            }
        }

        if ('' === $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return '/' . ltrim($path, '/');
    }

    private function serializeHint(EinbauHinweis $hint): array
    {
        return array_merge($this->serializeHintSummary($hint), [
            'vehicleEngines' => array_values(array_map(fn (VehicleEngine $engine): array => $this->serializeEngine($engine), $hint->getVehicleEngines()->toArray())),
            'vehicleModels' => array_values(array_map(fn (VehicleModel $model): array => $this->serializeModel($model), $hint->getVehicleModels()->toArray())),
            'images' => array_values(array_map(fn (MediaAsset $image): array => $this->serializeImage($image), $hint->getImages()->toArray())),
        ]);
    }

    /**
     * @return array<int, Ecu>
     */
    private function effectiveActiveEcus(EinbauHinweis $hint): array
    {
        $ecus = [];

        foreach ($hint->getActiveEcus() as $ecu) {
            if ($ecu instanceof Ecu && null !== $ecu->getId()) {
                $ecus[$ecu->getId()] = $ecu;
            }
        }

        $legacyEcu = $hint->getActiveEcu();
        if ($legacyEcu instanceof Ecu && null !== $legacyEcu->getId()) {
            $ecus[$legacyEcu->getId()] = $legacyEcu;
        }

        uasort($ecus, fn (Ecu $a, Ecu $b): int => strnatcasecmp(
            (string) ($this->serializeEcu($a)['label'] ?? ''),
            (string) ($this->serializeEcu($b)['label'] ?? '')
        ));

        return array_values($ecus);
    }

    private function serializeOptionMaster(?EcuOptionMaster $optionMaster): ?array
    {
        if (!$optionMaster instanceof EcuOptionMaster) {
            return null;
        }

        return [
            'id' => $optionMaster->getId(),
            'name' => $optionMaster->getName(),
            'slug' => $optionMaster->getSlug(),
        ];
    }

    private function serializeEngine(VehicleEngine $engine): array
    {
        $model = $engine->getVehicleModel();

        return [
            'id' => $engine->getId(),
            'name' => $engine->getName(),
            'label' => $this->engineLabel($engine),
            'brand' => $model?->getVehicleSeries()?->getVehicleBrand()?->getName(),
            'series' => $model?->getVehicleSeries()?->getName(),
            'model' => $model instanceof VehicleModel ? $this->serializeModel($model) : null,
            'fuel' => $engine->getFuel(),
            'engineCode' => $engine->getEngineCode(),
            'horsepower' => $engine->getHorsepower(),
            'torque' => $engine->getTorque(),
            'ecus' => array_values(array_map(fn (Ecu $ecu): array => $this->serializeEcu($ecu), array_filter(
                $engine->getEcuRelations()->toArray(),
                static fn (mixed $ecu): bool => $ecu instanceof Ecu && Ecu::CONTROL_UNIT_KIND_MCU === $ecu->getControlUnitKind()
            ))),
        ];
    }

    private function serializeModel(VehicleModel $model): array
    {
        $series = $model->getVehicleSeries();
        $brand = $series?->getVehicleBrand();
        $parts = array_values(array_filter([$brand?->getName(), $series?->getName(), $model->getName()], static fn (?string $part): bool => null !== $part && '' !== trim($part)));

        return [
            'id' => $model->getId(),
            'name' => $model->getName(),
            'label' => implode(' - ', $parts) ?: $model->getName(),
            'brand' => $brand?->getName(),
            'series' => $series?->getName(),
            'typeId' => $brand?->getVehicleType()?->getId(),
            'brandId' => $brand?->getId(),
            'seriesId' => $series?->getId(),
        ];
    }

    private function serializeEcu(?Ecu $ecu): ?array
    {
        if (!$ecu instanceof Ecu) {
            return null;
        }

        $name = trim((string) ($ecu->getMasterEcu()?->getName() ?: $ecu->getName()));
        $manufacturer = trim((string) ($ecu->getManufacturer()?->getName() ?? ''));
        $type = trim((string) ($ecu->getType() ?? ''));
        $parts = array_values(array_unique(array_filter([$manufacturer, $name, $type], static fn (string $part): bool => '' !== $part)));

        return [
            'id' => $ecu->getId(),
            'name' => '' !== $name ? $name : $ecu->getName(),
            'label' => implode(' · ', $parts) ?: $ecu->getName(),
            'manufacturer' => '' !== $manufacturer ? $manufacturer : null,
            'type' => '' !== $type ? $type : null,
            'kind' => $ecu->getControlUnitKind(),
            'key' => $this->ecuKey($ecu),
        ];
    }

    private function serializeImage(MediaAsset $image): array
    {
        $path = trim((string) ($image->getFilePath() ?? ''));

        return [
            'id' => $image->getId(),
            'name' => $image->getName(),
            'filePath' => '' !== $path ? $path : null,
            'src' => '' !== $path ? '/' . ltrim($path, '/') : null,
            'sorting' => $image->getSorting(),
            'mimeType' => $image->getMimeType(),
            'fileSize' => $image->getFileSize(),
            'annotations' => $image->getAnnotations(),
        ];
    }

    private function engineLabel(VehicleEngine $engine): string
    {
        $model = $engine->getVehicleModel();
        $parts = array_values(array_filter([
            $model?->getVehicleSeries()?->getVehicleBrand()?->getName(),
            $model?->getVehicleSeries()?->getName(),
            $model?->getName(),
            $engine->getName(),
        ], static fn (?string $part): bool => null !== $part && '' !== trim($part)));

        return implode(' - ', $parts) ?: $engine->getName();
    }

    private function ecuKey(Ecu $ecu): string
    {
        if (null !== $ecu->getMasterEcu()?->getId()) {
            return 'master-' . $ecu->getMasterEcu()->getId();
        }

        $name = mb_strtolower(trim($ecu->getName()));

        return '' !== $name ? 'name-' . $name : 'id-' . (string) $ecu->getId();
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return '' !== $text ? $text : null;
    }
}
