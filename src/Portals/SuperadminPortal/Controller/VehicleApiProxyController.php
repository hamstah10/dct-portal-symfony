<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Repository\VehicleBrandRepository;
use App\Service\VehicleApiProxy;
use App\Service\VehiclePayloadMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class VehicleApiProxyController extends AbstractController
{
    public function __construct(
        private readonly VehicleApiProxy $vehicleApiProxy,
        private readonly HttpClientInterface $httpClient,
        private readonly VehicleBrandRepository $vehicleBrandRepository,
        private readonly VehiclePayloadMapper $vehiclePayloadMapper,
    ) {
    }

    #[Route('/vehicle-api/types', name: 'superadmin_portal_vehicle_api_types', methods: ['GET'])]
    public function types(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/types', [], null, $portal);
    }

    #[Route('/vehicle-api/brands/{typeId}', name: 'superadmin_portal_vehicle_api_brands', requirements: ['typeId' => '\d+'], methods: ['GET'])]
    public function brands(int $typeId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/brands/' . $typeId, [], null, $portal);
    }

    #[Route('/vehicle-api/series/{brandId}', name: 'superadmin_portal_vehicle_api_series', requirements: ['brandId' => '\d+'], methods: ['GET'])]
    public function series(int $brandId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/series/' . $brandId, [], null, $portal);
    }

    #[Route('/vehicle-api/models/{seriesId}', name: 'superadmin_portal_vehicle_api_models', requirements: ['seriesId' => '\d+'], methods: ['GET'])]
    public function models(int $seriesId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/models/' . $seriesId, [], null, $portal);
    }

    #[Route('/vehicle-api/engines/{modelId}', name: 'superadmin_portal_vehicle_api_engines', requirements: ['modelId' => '\d+'], methods: ['GET'])]
    public function engines(int $modelId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/engines/' . $modelId, [], null, $portal);
    }

    #[Route('/vehicle-api/engine-details/{engineId}', name: 'superadmin_portal_vehicle_api_engine_details', requirements: ['engineId' => '\d+'], methods: ['GET'])]
    public function engineDetails(int $engineId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        $query = [];
        $stageId = trim((string) $request->query->get('stageId', ''));
        if ($stageId !== '') {
            $query['stageId'] = $stageId;
        }

        $templateVariant = trim((string) $request->query->get('templateVariant', ''));
        if ($templateVariant !== '') {
            $query['templateVariant'] = $templateVariant;
        }

        return $this->vehicleApiProxy->proxy('/engine-details/' . $engineId, $query, null, $portal, true);
    }

    #[Route('/vehicle-api/track', name: 'superadmin_portal_vehicle_api_track', methods: ['POST'])]
    public function track(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        return $this->vehicleApiProxy->proxyPost('/track', $payload, null, $portal);
    }

    #[Route('/vehicle-api/asset', name: 'superadmin_portal_vehicle_api_asset', methods: ['GET'])]
    public function asset(Request $request): Response
    {
        $path = (string) $request->query->get('path', '');

        return $this->vehicleApiProxy->proxyAsset($path);
    }

    #[Route('/vehicle-api/admin/stages/save', name: 'superadmin_portal_vehicle_api_admin_stages_save', methods: ['POST'])]
    public function saveStage(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/stages/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/series/save', name: 'superadmin_portal_vehicle_api_admin_series_save', methods: ['POST'])]
    public function saveSeries(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/series/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/models/save', name: 'superadmin_portal_vehicle_api_admin_models_save', methods: ['POST'])]
    public function saveModel(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/models/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/engines/save', name: 'superadmin_portal_vehicle_api_admin_engines_save', methods: ['POST'])]
    public function saveEngine(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/engines/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/options/save', name: 'superadmin_portal_vehicle_api_admin_options_save', methods: ['POST'])]
    public function saveOption(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/options/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/ecus/save', name: 'superadmin_portal_vehicle_api_admin_ecus_save', methods: ['POST'])]
    public function saveEcu(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/ecus/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/gearboxes/save', name: 'superadmin_portal_vehicle_api_admin_gearboxes_save', methods: ['POST'])]
    public function saveGearbox(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/gearboxes/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/hints/save', name: 'superadmin_portal_vehicle_api_admin_hints_save', methods: ['POST'])]
    public function saveHint(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/hints/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/tool-entries/save', name: 'superadmin_portal_vehicle_api_admin_tool_entries_save', methods: ['POST'])]
    public function saveToolEntry(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/tool-entries/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/stages/{id}/inactive', name: 'superadmin_portal_vehicle_api_admin_stages_inactive', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deactivateStage(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/stages/' . $id . '/inactive', $request, $portalRuntimeResolver);
    }

    /** Ziel-Ordner je Bildtyp, gleiche Ablage wie im EasyAdmin (public/uploads/...). */
    private const IMAGE_UPLOAD_DIRS = [
        'vehicle-type' => 'uploads/vehicle-type',
        'vehicle-brand' => 'uploads/vehicle-brand',
        'vehicle-series' => 'uploads/vehicle-series',
        'vehicle-model' => 'uploads/vehicle-model',
        'vehicle-engine' => 'uploads/vehicle-engine',
    ];

    private const IMAGE_UPLOAD_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    private const IMAGE_UPLOAD_MAX_BYTES = 8 * 1024 * 1024;

    #[Route('/vehicle-api/admin/upload/{target}', name: 'superadmin_portal_vehicle_api_upload_image', requirements: ['target' => 'vehicle-type|vehicle-brand|vehicle-series|vehicle-model|vehicle-engine'], methods: ['POST'])]
    public function uploadImage(string $target, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portalRuntimeResolver->requirePortalSystem($request);

        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return $this->json(['message' => 'Keine gültige Bilddatei hochgeladen.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($file->getSize() > self::IMAGE_UPLOAD_MAX_BYTES) {
            return $this->json(['message' => 'Die Datei ist zu groß (max. 8 MB).'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Dateityp aus dem Inhalt, nicht aus dem Dateinamen; SVG bewusst nicht erlaubt (aktive Inhalte).
        $mime = (string) $file->getMimeType();
        $extension = self::IMAGE_UPLOAD_MIME_EXTENSIONS[$mime] ?? null;
        if (null === $extension) {
            return $this->json(['message' => 'Nur JPG, PNG, WebP oder GIF erlaubt.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $relativeDir = self::IMAGE_UPLOAD_DIRS[$target];
        $absoluteDir = $this->getParameter('kernel.project_dir') . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0775, true);
        }

        $fileName = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), $extension);
        $file->move($absoluteDir, $fileName);

        return $this->json(['data' => ['path' => $relativeDir . '/' . $fileName]]);
    }

    #[Route('/vehicle-api/admin/ecus', name: 'superadmin_portal_vehicle_api_ecus_list', methods: ['GET'])]
    public function ecuList(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/admin/ecus', [], null, $portal, true);
    }

    #[Route('/vehicle-api/admin/engines/{id}/ecus', name: 'superadmin_portal_vehicle_api_engine_ecus_list', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function engineEcus(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/admin/engines/' . $id . '/ecus', [], null, $portal, true);
    }

    #[Route('/vehicle-api/admin/engines/{id}/ecus', name: 'superadmin_portal_vehicle_api_engine_ecus_link', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function linkEngineEcu(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/engines/' . $id . '/ecus', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/engines/{id}/ecus/{ecuId}', name: 'superadmin_portal_vehicle_api_engine_ecus_unlink', requirements: ['id' => '\d+', 'ecuId' => '\d+'], methods: ['DELETE'])]
    public function unlinkEngineEcu(int $id, int $ecuId, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxyDelete('/admin/engines/' . $id . '/ecus/' . $ecuId, null, $portal, true);
    }

    #[Route('/vehicle-api/admin/editing-allowed', name: 'superadmin_portal_vehicle_api_editing_allowed', methods: ['GET'])]
    public function editingAllowed(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/admin/editing-allowed', [], null, $portal, true);
    }

    #[Route('/vehicle-api/admin/performance-brands', name: 'superadmin_portal_vehicle_api_admin_performance_brands', methods: ['GET'])]
    public function performanceBrands(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/admin/performance-brands', [], null, $portal, true);
    }

    #[Route('/vehicle-api/admin/{entity}/{id}', name: 'superadmin_portal_vehicle_api_hierarchy_detail', requirements: ['entity' => 'series|models|engines', 'id' => '\d+'], methods: ['GET'])]
    public function hierarchyDetail(string $entity, int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/admin/' . $entity . '/' . $id, [], null, $portal, true);
    }

    #[Route('/vehicle-api/admin/brands/{id}', name: 'superadmin_portal_vehicle_api_admin_brands_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function brandDetail(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/admin/brands/' . $id, [], null, $portal, true);
    }

    #[Route('/vehicle-api/admin/types/{id}', name: 'superadmin_portal_vehicle_api_admin_types_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function typeDetail(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/admin/types/' . $id, [], null, $portal, true);
    }

    #[Route('/vehicle-api/admin/brands/save', name: 'superadmin_portal_vehicle_api_admin_brands_save', methods: ['POST'])]
    public function saveBrand(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/brands/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/types/save', name: 'superadmin_portal_vehicle_api_admin_types_save', methods: ['POST'])]
    public function saveType(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/types/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/brands/reorder', name: 'superadmin_portal_vehicle_api_admin_brands_reorder', methods: ['POST'])]
    public function reorderBrands(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/brands/reorder', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/{entity}/{id}/move', name: 'superadmin_portal_vehicle_api_admin_move', requirements: ['entity' => 'series|models|engines', 'id' => '\d+'], methods: ['POST'])]
    public function moveHierarchyEntity(string $entity, int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/' . $entity . '/' . $id . '/move', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/{entity}/{id}/merge', name: 'superadmin_portal_vehicle_api_admin_merge', requirements: ['entity' => 'series|models|engines', 'id' => '\d+'], methods: ['POST'])]
    public function mergeHierarchyEntity(string $entity, int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/' . $entity . '/' . $id . '/merge', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/master/{resource}', name: 'superadmin_portal_vehicle_api_master_list', requirements: ['resource' => 'tool-manufacturers|tool-methodes|gearbox-dtc-codes|gearbox-stage-infos|gearbox-stage-options'], methods: ['GET'])]
    public function masterList(string $resource, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxy('/admin/master/' . $resource, [], null, $portal, true);
    }

    #[Route('/vehicle-api/admin/master/{resource}/save', name: 'superadmin_portal_vehicle_api_master_save', requirements: ['resource' => 'tool-manufacturers|tool-methodes|gearbox-dtc-codes|gearbox-stage-infos|gearbox-stage-options'], methods: ['POST'])]
    public function masterSave(string $resource, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        return $this->proxyAdminSave('/admin/master/' . $resource . '/save', $request, $portalRuntimeResolver);
    }

    #[Route('/vehicle-api/admin/master/{resource}/{id}', name: 'superadmin_portal_vehicle_api_master_delete', requirements: ['resource' => 'tool-manufacturers|tool-methodes|gearbox-dtc-codes|gearbox-stage-infos|gearbox-stage-options', 'id' => '\d+'], methods: ['DELETE'])]
    public function masterDelete(string $resource, int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxyDelete('/admin/master/' . $resource . '/' . $id, null, $portal, true);
    }

    #[Route('/vehicle-api/admin/{entity}/{id}', name: 'superadmin_portal_vehicle_api_admin_delete',requirements: ['entity' => 'series|models|engines|stages', 'id' => '\d+'], methods: ['DELETE'])]
    public function deleteHierarchyEntity(string $entity, int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->vehicleApiProxy->proxyDelete('/admin/' . $entity . '/' . $id, null, $portal, true);
    }

    #[Route('/vehicle-api/search', name: 'superadmin_portal_vehicle_api_search', methods: ['GET'])]
    public function search(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portalRuntimeResolver->requirePortalSystem($request);
        $q = trim((string) $request->query->get('q', ''));
        $limit = max(1, min(100, (int) $request->query->get('limit', 30)));

        if ($q === '') {
            return $this->json([
                'items' => [],
                'facets' => [],
                'facetStats' => [],
                'processingTimeMs' => 0,
                'estimatedTotalHits' => 0,
            ]);
        }

        $meiliHost = rtrim((string) ($_ENV['MEILISEARCH_URL'] ?? ''), '/');
        $meiliKey = trim((string) ($_ENV['MEILISEARCH_KEY'] ?? ''));
        if ($meiliHost === '' || $meiliKey === '') {
            return $this->json(['message' => 'MEILISEARCH_URL oder MEILISEARCH_KEY fehlt.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $filter = ['active = true'];
        foreach (['fuel' => 'fuel', 'brand' => 'brandName'] as $queryKey => $attribute) {
            $value = trim((string) $request->query->get($queryKey, ''));
            if ($value !== '') {
                $filter[] = $attribute . ' = "' . str_replace('"', '\"', $value) . '"';
            }
        }

        $horsepowerMin = $request->query->get('horsepowerMin');
        $horsepowerMax = $request->query->get('horsepowerMax');
        if (is_numeric($horsepowerMin)) {
            $filter[] = 'horsepower >= ' . (int) $horsepowerMin;
        }
        if (is_numeric($horsepowerMax)) {
            $filter[] = 'horsepower <= ' . (int) $horsepowerMax;
        }

        $payload = [
            'q' => $q,
            'limit' => $limit,
            'filter' => implode(' AND ', $filter),
            'facets' => ['fuel', 'brandName', 'vehicleTypeName'],
            'attributesToRetrieve' => [
                'id',
                'name',
                'fuel',
                'displacement',
                'horsepower',
                'torque',
                'modelName',
                'seriesName',
                'brandName',
                'vehicleTypeName',
                'dataPoolKey',
                'performanceBrandName',
            ],
        ];

        $sort = trim((string) $request->query->get('sort', ''));
        $sortMap = [
            'horsepower:desc' => 'horsepower:desc',
            'horsepower:asc' => 'horsepower:asc',
            'brand:asc' => 'brandName:asc',
            'yearFrom:desc' => null,
            'yearFrom:asc' => null,
        ];
        if ($sort !== '' && array_key_exists($sort, $sortMap) && is_string($sortMap[$sort])) {
            $payload['sort'] = [$sortMap[$sort]];
        }

        $indexCandidates = ['prod_vehicle_engines', 'dctportal_prod_vehicle_engines'];
        $data = null;
        $lastStatus = Response::HTTP_SERVICE_UNAVAILABLE;
        $lastBody = '';

        foreach ($indexCandidates as $indexName) {
            $response = $this->httpClient->request('POST', $meiliHost . '/indexes/' . $indexName . '/search', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $meiliKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 8,
            ]);

            $lastStatus = $response->getStatusCode();
            $lastBody = $response->getContent(false);
            $decoded = json_decode($lastBody, true);

            if ($lastStatus < 400 && is_array($decoded)) {
                $data = $decoded;
                break;
            }

            if (!is_array($decoded) || ($decoded['code'] ?? '') !== 'index_not_found') {
                break;
            }
        }

        if (!is_array($data)) {
            return $this->json([
                'message' => 'Meilisearch-Fahrzeugsuche fehlgeschlagen.',
                'status' => $lastStatus,
                'error' => json_decode($lastBody, true)['message'] ?? $lastBody,
            ], Response::HTTP_BAD_GATEWAY);
        }

        $hits = is_array($data['hits'] ?? null) ? $data['hits'] : [];
        $brandImages = $this->resolveBrandImages($hits);

        return $this->json([
            'items' => array_map(function (array $hit) use ($brandImages): array {
                $horsepower = is_numeric($hit['horsepower'] ?? null) ? (int) $hit['horsepower'] : null;
                $brandName = (string) ($hit['brandName'] ?? '');
                $dataPoolKey = (string) ($hit['dataPoolKey'] ?? '');
                $brandImage = $brandImages[$this->brandImageMapKey($brandName, $dataPoolKey)]
                    ?? $brandImages[$this->brandImageMapKey($brandName, '')]
                    ?? null;

                return [
                    'id' => $hit['id'] ?? $hit['objectID'] ?? null,
                    'typeId' => null,
                    'brandId' => null,
                    'seriesId' => null,
                    'modelId' => null,
                    'engineId' => $hit['id'] ?? $hit['objectID'] ?? null,
                    'engineDetailId' => $hit['id'] ?? $hit['objectID'] ?? null,
                    'type' => (string) ($hit['vehicleTypeName'] ?? ''),
                    'brand' => $brandName,
                    'series' => (string) ($hit['seriesName'] ?? ''),
                    'model' => (string) ($hit['modelName'] ?? ''),
                    'engine' => (string) ($hit['name'] ?? ''),
                    'engineCode' => null,
                    'fuel' => (string) ($hit['fuel'] ?? ''),
                    'horsepower' => $horsepower,
                    'kilowatts' => $horsepower !== null ? (int) round($horsepower / 1.36) : null,
                    'torque' => is_numeric($hit['torque'] ?? null) ? (int) $hit['torque'] : null,
                    'yearFrom' => null,
                    'yearTo' => null,
                    'ecu' => null,
                    'ecuManufacturer' => null,
                    'protocol' => null,
                    'image' => $brandImage,
                    'brandImage' => $brandImage,
                    'label' => trim(implode(' ', array_filter([
                        (string) ($hit['brandName'] ?? ''),
                        (string) ($hit['seriesName'] ?? ''),
                        (string) ($hit['modelName'] ?? ''),
                        (string) ($hit['name'] ?? ''),
                    ]))),
                    'searchText' => strtolower(trim(implode(' ', array_filter([
                        (string) ($hit['brandName'] ?? ''),
                        (string) ($hit['seriesName'] ?? ''),
                        (string) ($hit['modelName'] ?? ''),
                        (string) ($hit['name'] ?? ''),
                        (string) ($hit['fuel'] ?? ''),
                    ])))),
                ];
            }, $hits),
            'facets' => [
                'fuel' => $data['facetDistribution']['fuel'] ?? [],
                'brand' => $data['facetDistribution']['brandName'] ?? [],
                'type' => $data['facetDistribution']['vehicleTypeName'] ?? [],
            ],
            'facetStats' => $data['facetStats'] ?? [],
            'processingTimeMs' => $data['processingTimeMs'] ?? 0,
            'estimatedTotalHits' => $data['estimatedTotalHits'] ?? count($hits),
        ]);
    }

    /**
     * @param list<array<string, mixed>> $hits
     *
     * @return array<string, string>
     */
    private function resolveBrandImages(array $hits): array
    {
        $brandNames = [];
        $poolKeys = [];

        foreach ($hits as $hit) {
            $brandName = trim((string) ($hit['brandName'] ?? ''));
            if ($brandName !== '') {
                $brandNames[strtolower($brandName)] = $brandName;
            }

            $poolKey = trim((string) ($hit['dataPoolKey'] ?? ''));
            if ($poolKey !== '') {
                $poolKeys[strtolower($poolKey)] = $poolKey;
            }
        }

        if ($brandNames === []) {
            return [];
        }

        $qb = $this->vehicleBrandRepository->createQueryBuilder('brand')
            ->leftJoin('brand.vehicleDataPool', 'pool')
            ->addSelect('pool')
            ->leftJoin('brand.masterBrand', 'masterBrand')
            ->addSelect('masterBrand')
            ->where('LOWER(brand.name) IN (:brandNames)')
            ->setParameter('brandNames', array_keys($brandNames));

        if ($poolKeys !== []) {
            $qb->andWhere('pool.poolKey IS NULL OR LOWER(pool.poolKey) IN (:poolKeys)')
                ->setParameter('poolKeys', array_keys($poolKeys));
        }

        $map = [];
        foreach ($qb->getQuery()->getResult() as $brand) {
            $name = trim((string) $brand->getName());
            if ($name === '') {
                continue;
            }

            $image = $this->vehiclePayloadMapper->normalizePublicPath(
                $this->vehiclePayloadMapper->resolveBrandImage($brand)
            );
            if (!is_string($image) || $image === '') {
                continue;
            }

            $poolKey = trim((string) ($brand->getVehicleDataPool()?->getPoolKey() ?? ''));
            $map[$this->brandImageMapKey($name, $poolKey)] = $image;
            $map[$this->brandImageMapKey($name, '')] ??= $image;
        }

        return $map;
    }

    private function brandImageMapKey(string $brandName, string $poolKey): string
    {
        return strtolower(trim($brandName)) . '::' . strtolower(trim($poolKey));
    }

    private function proxyAdminSave(string $path, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        return $this->vehicleApiProxy->proxyPost($path, $payload, null, $portal, true);
    }
}
