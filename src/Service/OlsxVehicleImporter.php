<?php

namespace App\Service;

use App\Entity\BrandMaster;
use App\Entity\Ecu;
use App\Entity\EcuManufacturer;
use App\Entity\EcuOption;
use App\Entity\ToolMethode;
use App\Entity\VehicleBrand;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use App\Entity\VehicleSeries;
use App\Entity\VehicleStage;
use App\Entity\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class OlsxVehicleImporter
{
    private const TYPE_MAP = [
        'car' => 'pkw',
        'truck' => 'lkw',
        'moto' => 'motorrad',
        'agricultural' => 'agrar',
        'boat' => 'boat',
        'jetski' => 'jetski',
        'atv' => 'atv',
    ];

    private readonly AsciiSlugger $slugger;

    /**
     * @var array<string, VehicleBrand>
     */
    private array $brandCache = [];

    /**
     * @var array<string, VehicleSeries>
     */
    private array $seriesCache = [];

    /**
     * @var array<string, VehicleModel>
     */
    private array $modelCache = [];

    /**
     * @var array<string, VehicleEngine>
     */
    private array $engineCache = [];

    /**
     * @var array<string, VehicleStage>
     */
    private array $stageCache = [];

    /**
     * @var array<string, EcuManufacturer>
     */
    private array $ecuManufacturerCache = [];

    /**
     * @var array<string, Ecu>
     */
    private array $ecuCache = [];

    /**
     * @var array<string, EcuOption>
     */
    private array $ecuOptionCache = [];

    /**
     * @var array<string, ToolMethode>
     */
    private array $toolMethodCache = [];

    /**
     * @var array{Created:int,Updated:int,Unchanged:int}
     */
    private array $writeStats = [
        'Created' => 0,
        'Updated' => 0,
        'Unchanged' => 0,
    ];

    private string $currentScraperSource = 'legacy';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        $this->slugger = new AsciiSlugger();
    }

    /**
     * @param array<int, mixed> $payload
     *
     * @return array<string, int>
     */
    public function import(array $payload, VehicleDataPool $pool, VehicleType $vehicleType, ?string $scraperSource = null): array
    {
        $this->resetImportState();
        $this->currentScraperSource = $this->normalizeScraperSource($scraperSource);

        $stats = [
            'Brands' => 0,
            'Series' => 0,
            'Models' => 0,
            'Engines' => 0,
            'Stages' => 0,
            'ECUs' => 0,
            'Options' => 0,
            'Manufacturers' => 0,
            'Created' => 0,
            'Updated' => 0,
            'Unchanged' => 0,
        ];

        foreach ($payload as $brandRow) {
            if (!is_array($brandRow)) {
                continue;
            }

            $brand = $this->upsertBrand($brandRow, $pool, $vehicleType);
            ++$stats['Brands'];

            $seriesSorting = 0;
            foreach ($this->toList($brandRow['models'] ?? null) as $seriesRow) {
                if (!is_array($seriesRow)) {
                    continue;
                }

                $series = $this->upsertSeries($seriesRow, $brand, $pool, $seriesSorting++);
                ++$stats['Series'];

                $modelSorting = 0;
                foreach ($this->toList($seriesRow['versions'] ?? null) as $modelRow) {
                    if (!is_array($modelRow)) {
                        continue;
                    }

                    $model = $this->upsertModel($modelRow, $series, $pool, $modelSorting++);
                    ++$stats['Models'];

                    $engineSorting = 0;
                    foreach ($this->toList($modelRow['engines'] ?? null) as $engineRow) {
                        if (!is_array($engineRow)) {
                            continue;
                        }

                        $engine = $this->upsertEngine($engineRow, $model, $pool, $engineSorting++);
                        ++$stats['Engines'];

                        foreach ($this->toList($engineRow['stages'] ?? null) as $stageIndex => $stageRow) {
                            if (!is_array($stageRow)) {
                                continue;
                            }

                            $this->upsertStage($stageRow, $engine, $pool, $stageIndex);
                            ++$stats['Stages'];
                        }

                        $flatControllers = $this->flattenControllers($engineRow['controllers'] ?? null);
                        $engineEcus = [];
                        foreach ($flatControllers as $controllerRow) {
                            $manufacturer = $this->resolveEcuManufacturer((string) ($controllerRow['manufacturer'] ?? ''));
                            if ($manufacturer instanceof EcuManufacturer && !array_key_exists('__counted', $controllerRow)) {
                                ++$stats['Manufacturers'];
                            }

                            $ecu = $this->upsertEcu($controllerRow, $engine, $pool, $manufacturer);
                            $engine->addEcuRelation($ecu);
                            $engineEcus[] = $ecu;
                            ++$stats['ECUs'];
                        }

                        $optionTargetEcu = $this->resolvePreferredEcu($engineEcus);
                        if ($optionTargetEcu instanceof Ecu) {
                            foreach ($this->toList($engineRow['options'] ?? null) as $optionRow) {
                                if (!is_array($optionRow)) {
                                    continue;
                                }

                                $option = $this->upsertEcuOption($optionRow, $pool);
                                $optionTargetEcu->addOption($option);
                                ++$stats['Options'];
                            }
                        }
                    }
                }
            }
        }

        $stats['Created'] = $this->writeStats['Created'];
        $stats['Updated'] = $this->writeStats['Updated'];
        $stats['Unchanged'] = $this->writeStats['Unchanged'];

        return $stats;
    }

    public function resolveVehicleTypeFromFile(string $file): ?VehicleType
    {
        $lookup = $this->detectVehicleTypeFromFile($file);
        $mapped = self::TYPE_MAP[$lookup] ?? $lookup;
        $repository = $this->entityManager->getRepository(VehicleType::class);
        $type = $repository->findOneBy(['slug' => $mapped]);
        if ($type instanceof VehicleType) {
            return $type;
        }

        $candidates = $repository->findAll();
        foreach ($candidates as $candidate) {
            if (!$candidate instanceof VehicleType) {
                continue;
            }
            $name = mb_strtolower(trim($candidate->getName()));
            $slug = mb_strtolower(trim((string) $candidate->getSlug()));
            if ($name === $mapped || $slug === $mapped) {
                return $candidate;
            }
        }

        return null;
    }

    public function detectScraperSourceFromFile(string $file): string
    {
        $normalizedPath = str_replace('\\', '/', mb_strtolower($file));

        if (preg_match('#/data/output/([^/]+)/[^/]+/#', $normalizedPath, $matches)) {
            $source = $matches[1] ?? '';
            if (is_string($source) && !array_key_exists($source, self::TYPE_MAP)) {
                return $this->normalizeScraperSource($source);
            }
        }

        if (preg_match('#/scrapy-remote-scraper/olsx_brands_#', $normalizedPath)) {
            return 'olsx_brands_spider';
        }

        return 'legacy';
    }

    /**
     * @param array<mixed>|null $value
     *
     * @return array<int,array<mixed>>
     */
    private function toList($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values($value);
    }

    private function detectVehicleTypeFromFile(string $file): string
    {
        $normalizedPath = str_replace('\\', '/', mb_strtolower($file));

        if (preg_match('#/data/output/(?:[^/]+/)?([^/]+)/#', $normalizedPath, $matches)) {
            $fromFolder = $matches[1] ?? '';
            if (is_string($fromFolder) && array_key_exists($fromFolder, self::TYPE_MAP)) {
                return $fromFolder;
            }
        }

        if (preg_match('/olsx_brands_([a-z]+)_/', $normalizedPath, $matches)) {
            $fromLegacyName = $matches[1] ?? '';
            if (is_string($fromLegacyName) && array_key_exists($fromLegacyName, self::TYPE_MAP)) {
                return $fromLegacyName;
            }
        }

        $filename = mb_strtolower(basename($file));

        if (preg_match('/(?:^|[_-])(car|truck|moto|agricultural|boat|jetski|atv)(?:[_.-]|$)/', $filename, $matches)) {
            $fromFilename = $matches[1] ?? '';
            if (is_string($fromFilename) && array_key_exists($fromFilename, self::TYPE_MAP)) {
                return $fromFilename;
            }
        }

        foreach (array_keys(self::TYPE_MAP) as $sourceType) {
            if (str_contains($filename, '_' . $sourceType . '_') || str_contains($filename, '-' . $sourceType . '-')) {
                return $sourceType;
            }
        }

        return 'car';
    }

    private function upsertBrand(array $brandRow, VehicleDataPool $pool, VehicleType $vehicleType): VehicleBrand
    {
        $slug = $this->slug($brandRow['brand_slug'] ?? $brandRow['brand_name'] ?? '');
        $name = trim((string) ($brandRow['brand_name'] ?? $brandRow['name'] ?? '—'));
        $source = $this->currentScraperSource;
        $cacheKey = implode('|', [
            $this->entityCacheKey($pool),
            $vehicleType->getId() ?? 'type-new',
            $source,
            $slug,
        ]);
        if ($slug && isset($this->brandCache[$cacheKey])) {
            return $this->brandCache[$cacheKey];
        }

        $repository = $this->entityManager->getRepository(VehicleBrand::class);
        $brand = $repository->findOneBy([
            'slug' => $slug,
            'vehicleDataPool' => $pool,
            'vehicleType' => $vehicleType,
            'scraperSource' => $source,
        ]);
        $isNew = false;
        if (!$brand instanceof VehicleBrand) {
            $brand = new VehicleBrand();
            $brand->setVehicleDataPool($pool);
            $this->entityManager->persist($brand);
            $isNew = true;
        }

        $before = [
            'name' => $brand->getName(),
            'slug' => $brand->getSlug(),
            'vehicleTypeId' => $brand->getVehicleType()?->getId(),
            'masterBrandId' => $brand->getMasterBrand()?->getId(),
            'scraperSource' => $brand->getScraperSource(),
            'image' => $brand->getImage(),
        ];

        $brand
            ->setName($name)
            ->setSlug($slug)
            ->setScraperSource($source)
            ->setVehicleType($vehicleType);

        $masterBrand = $this->findMatchingBrandMaster($slug, $name);
        if ($masterBrand instanceof BrandMaster) {
            $brand->setMasterBrand($masterBrand);

            $masterImage = trim((string) ($masterBrand->getImage() ?? ''));
            if ('' !== $masterImage) {
                $brand->setImage($masterImage);
            }
        }

        $after = [
            'name' => $brand->getName(),
            'slug' => $brand->getSlug(),
            'vehicleTypeId' => $brand->getVehicleType()?->getId(),
            'masterBrandId' => $brand->getMasterBrand()?->getId(),
            'scraperSource' => $brand->getScraperSource(),
            'image' => $brand->getImage(),
        ];
        $this->trackWriteResult($isNew, $before, $after);

        return $this->brandCache[$cacheKey] = $brand;
    }

    private function findMatchingBrandMaster(?string $slug, ?string $name): ?BrandMaster
    {
        $slug = trim((string) ($slug ?? ''));
        $name = trim((string) ($name ?? ''));
        if ('' === $slug && '' === $name) {
            return null;
        }

        $repository = $this->entityManager->getRepository(BrandMaster::class);

        if ('' !== $slug) {
            $bySlug = $repository->findOneBy(['slug' => $slug]);
            if ($bySlug instanceof BrandMaster) {
                return $bySlug;
            }
        }

        if ('' === $name) {
            return null;
        }

        return $repository->createQueryBuilder('bm')
            ->andWhere('LOWER(bm.name) = :nameLower')
            ->setParameter('nameLower', mb_strtolower($name))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function upsertSeries(array $seriesRow, VehicleBrand $brand, VehicleDataPool $pool, int $sorting): VehicleSeries
    {
        $slug = $this->slug($seriesRow['slug'] ?? $seriesRow['name'] ?? '');
        $key = $this->entityCacheKey($brand) . '|' . $slug . '|' . $this->entityCacheKey($pool);
        if ($slug && isset($this->seriesCache[$key])) {
            return $this->seriesCache[$key];
        }

        $series = $this->entityManager->getRepository(VehicleSeries::class)->findOneBy([
            'slug' => $slug,
            'vehicleBrand' => $brand,
            'vehicleDataPool' => $pool,
        ]);
        $isNew = false;
        if (!$series instanceof VehicleSeries) {
            $series = new VehicleSeries();
            $series->setVehicleBrand($brand);
            $series->setVehicleDataPool($pool);
            $this->entityManager->persist($series);
            $isNew = true;
        }

        $before = [
            'name' => $series->getName(),
            'slug' => $series->getSlug(),
            'sorting' => $series->getSorting(),
        ];

        $series
            ->setName($seriesRow['name'] ?? $seriesRow['display_name'] ?? '—')
            ->setSlug($slug)
            ->setSorting($sorting);

        $after = [
            'name' => $series->getName(),
            'slug' => $series->getSlug(),
            'sorting' => $series->getSorting(),
        ];
        $this->trackWriteResult($isNew, $before, $after);

        return $this->seriesCache[$key] = $series;
    }

    private function upsertModel(array $modelRow, VehicleSeries $series, VehicleDataPool $pool, int $sorting): VehicleModel
    {
        $slug = $this->slug($modelRow['slug'] ?? $modelRow['name'] ?? '');
        $key = $this->entityCacheKey($series) . '|' . $slug . '|' . $this->entityCacheKey($pool);
        if ($slug && isset($this->modelCache[$key])) {
            return $this->modelCache[$key];
        }

        $model = $this->entityManager->getRepository(VehicleModel::class)->findOneBy([
            'slug' => $slug,
            'vehicleSeries' => $series,
            'vehicleDataPool' => $pool,
        ]);
        $isNew = false;
        if (!$model instanceof VehicleModel) {
            $model = new VehicleModel();
            $model->setVehicleSeries($series);
            $model->setVehicleDataPool($pool);
            $this->entityManager->persist($model);
            $isNew = true;
        }

        $before = [
            'name' => $model->getName(),
            'slug' => $model->getSlug(),
            'sorting' => $model->getSorting(),
        ];

        $model
            ->setName($modelRow['name'] ?? $modelRow['display_name'] ?? '—')
            ->setSlug($slug)
            ->setSorting($sorting);

        $after = [
            'name' => $model->getName(),
            'slug' => $model->getSlug(),
            'sorting' => $model->getSorting(),
        ];
        $this->trackWriteResult($isNew, $before, $after);

        return $this->modelCache[$key] = $model;
    }

    private function upsertEngine(array $engineRow, VehicleModel $model, VehicleDataPool $pool, int $sorting): VehicleEngine
    {
        $slug = $this->slug($engineRow['slug'] ?? $engineRow['name'] ?? '');
        $key = $this->entityCacheKey($model) . '|' . $slug . '|' . $this->entityCacheKey($pool);
        if ($slug && isset($this->engineCache[$key])) {
            return $this->engineCache[$key];
        }

        $engine = $this->entityManager->getRepository(VehicleEngine::class)->findOneBy([
            'slug' => $slug,
            'vehicleModel' => $model,
            'vehicleDataPool' => $pool,
        ]);
        $isNew = false;
        if (!$engine instanceof VehicleEngine) {
            $engine = new VehicleEngine();
            $engine->setVehicleModel($model);
            $engine->setVehicleDataPool($pool);
            $this->entityManager->persist($engine);
            $isNew = true;
        }

        $before = [
            'name' => $engine->getName(),
            'slug' => $engine->getSlug(),
            'description' => $engine->getDescription(),
            'horsepower' => $engine->getHorsepower(),
            'torque' => $engine->getTorque(),
            'fuel' => $engine->getFuel(),
            'sorting' => $engine->getSorting(),
        ];

        $original = is_array($engineRow['original'] ?? null) ? $engineRow['original'] : [];

        $engine
            ->setName($engineRow['name'] ?? $engineRow['display_name'] ?? '—')
            ->setSlug($slug)
            ->setDescription($this->nullableString($engineRow['url'] ?? null))
            ->setHorsepower($this->toNullableInt($original['power_ps'] ?? null))
            ->setTorque($this->toNullableInt($original['torque_nm'] ?? null))
            ->setFuel($this->nullableString($engineRow['fuel_type'] ?? null))
            ->setSorting($sorting)
            ->setActive(true);

        $after = [
            'name' => $engine->getName(),
            'slug' => $engine->getSlug(),
            'description' => $engine->getDescription(),
            'horsepower' => $engine->getHorsepower(),
            'torque' => $engine->getTorque(),
            'fuel' => $engine->getFuel(),
            'sorting' => $engine->getSorting(),
        ];
        $this->trackWriteResult($isNew, $before, $after);

        return $this->engineCache[$key] = $engine;
    }

    private function upsertStage(array $stageRow, VehicleEngine $engine, VehicleDataPool $pool, int $index): VehicleStage
    {
        $slug = $this->slug($stageRow['slug'] ?? $stageRow['name'] ?? 'stage-' . $index);
        $key = $this->entityCacheKey($engine) . '|' . $slug . '|' . $this->entityCacheKey($pool);
        if ($slug && isset($this->stageCache[$key])) {
            return $this->stageCache[$key];
        }

        $stage = $this->entityManager->getRepository(VehicleStage::class)->findOneBy([
            'slug' => $slug,
            'vehicleEngine' => $engine,
            'vehicleDataPool' => $pool,
        ]);
        $isNew = false;
        if (!$stage instanceof VehicleStage) {
            $stage = new VehicleStage();
            $stage->setVehicleEngine($engine);
            $stage->setVehicleDataPool($pool);
            $this->entityManager->persist($stage);
            $isNew = true;
        }

        $before = [
            'name' => $stage->getName(),
            'slug' => $stage->getSlug(),
            'power' => $stage->getPower(),
            'torque' => $stage->getTorque(),
            'description' => $stage->getDescription(),
            'originalPs' => $stage->getOriginalPs(),
            'originalNm' => $stage->getOriginalNm(),
        ];

        $stage
            ->setName($stageRow['name'] ?? $stageRow['display_name'] ?? 'Stage')
            ->setSlug($slug)
            ->setPower($this->toNullableInt($stageRow['power_ps'] ?? null))
            ->setTorque($this->toNullableInt($stageRow['torque_nm'] ?? null))
            ->setDescription($this->nullableString($stageRow['url'] ?? null))
            ->setOriginalPs($engine->getHorsepower())
            ->setOriginalNm($engine->getTorque())
            ->setIcon(null)
            ->setImages(null)
            ->setVideos(null)
            ->setEcu(null)
            ->setActive(true);

        $after = [
            'name' => $stage->getName(),
            'slug' => $stage->getSlug(),
            'power' => $stage->getPower(),
            'torque' => $stage->getTorque(),
            'description' => $stage->getDescription(),
            'originalPs' => $stage->getOriginalPs(),
            'originalNm' => $stage->getOriginalNm(),
        ];
        $this->trackWriteResult($isNew, $before, $after);

        return $this->stageCache[$key] = $stage;
    }

    /**
     * @param array<string, mixed>|null $value
     *
     * @return array<int,array<string, mixed>>
     */
    private function flattenControllers($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $flat = [];
        foreach ($this->toList($value) as $controllerGroup) {
            if (!is_array($controllerGroup)) {
                continue;
            }

            $manufacturer = $this->stringValue($controllerGroup['manufacturer'] ?? null, '');
            foreach ($this->toList($controllerGroup['items'] ?? null) as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $flat[] = [
                    'manufacturer' => $manufacturer,
                    'model' => $item['model'] ?? null,
                    'ecu_type' => $item['ecu_type'] ?? null,
                    'tools' => $this->toList($item['tools'] ?? null),
                ];
            }
        }

        return $flat;
    }

    private function upsertEcuManufacturer(string $name): ?EcuManufacturer
    {
        $key = mb_strtolower(trim($name));
        if ('' === $key) {
            return null;
        }

        if (isset($this->ecuManufacturerCache[$key])) {
            return $this->ecuManufacturerCache[$key];
        }

        $manufacturer = $this->entityManager->getRepository(EcuManufacturer::class)->findOneBy(['name' => $name]);
        $isNew = false;
        if (!$manufacturer instanceof EcuManufacturer) {
            $manufacturer = new EcuManufacturer();
            $manufacturer->setName($name);
            $this->entityManager->persist($manufacturer);
            $isNew = true;
        }

        $before = ['name' => $manufacturer->getName()];
        $manufacturer->setName($name);
        $after = ['name' => $manufacturer->getName()];
        $this->trackWriteResult($isNew, $before, $after);

        return $this->ecuManufacturerCache[$key] = $manufacturer;
    }

    private function resolveEcuManufacturer(string $name): ?EcuManufacturer
    {
        $key = mb_strtolower(trim($name));
        if ('' === $key) {
            return null;
        }

        return $this->upsertEcuManufacturer($name);
    }

    private function upsertEcu(array $controllerRow, VehicleEngine $engine, VehicleDataPool $pool, ?EcuManufacturer $manufacturer): Ecu
    {
        $model = $controllerRow['model'] ?? $controllerRow['name'] ?? 'ECU';
        $key = $this->entityCacheKey($engine) . '|' . (string) $model . '|' . $this->entityCacheKey($pool);
        if (isset($this->ecuCache[$key])) {
            return $this->ecuCache[$key];
        }

        $ecu = $this->entityManager->getRepository(Ecu::class)->findOneBy([
            'name' => $model,
            'vehicleEngine' => $engine,
            'vehicleDataPool' => $pool,
        ]);
        $isNew = false;
        if (!$ecu instanceof Ecu) {
            $ecu = new Ecu();
            $ecu->setVehicleEngine($engine);
            $ecu->setVehicleDataPool($pool);
            $this->entityManager->persist($ecu);
            $isNew = true;
        }

        $beforeToolNames = $ecu->getToolMethods()->map(static fn (ToolMethode $method): string => mb_strtolower($method->getName()))->toArray();
        sort($beforeToolNames);
        $before = [
            'name' => $ecu->getName(),
            'type' => $ecu->getType(),
            'controlUnitKind' => $ecu->getControlUnitKind(),
            'description' => $ecu->getDescription(),
            'manufacturerId' => $ecu->getManufacturer()?->getId(),
            'toolMethods' => $beforeToolNames,
        ];

        if ($manufacturer instanceof EcuManufacturer) {
            $ecu->setManufacturer($manufacturer);
        }

        $toolNames = $this->sanitizeToolNames($controllerRow['tools'] ?? []);
        $ecu
            ->setName($model)
            ->setType($this->nullableString($controllerRow['ecu_type'] ?? null) ?? null)
            ->setControlUnitKind($this->normalizeControlUnitKind($controllerRow['ecu_type'] ?? null))
            ->setDescription($toolNames ? implode(', ', $toolNames) : null)
            ->setImage(null);
        $this->clearToolMethods($ecu);

        foreach ($this->resolveToolMethods($toolNames) as $toolMethod) {
            $ecu->addToolMethod($toolMethod);
        }

        $afterToolNames = $ecu->getToolMethods()->map(static fn (ToolMethode $method): string => mb_strtolower($method->getName()))->toArray();
        sort($afterToolNames);
        $after = [
            'name' => $ecu->getName(),
            'type' => $ecu->getType(),
            'controlUnitKind' => $ecu->getControlUnitKind(),
            'description' => $ecu->getDescription(),
            'manufacturerId' => $ecu->getManufacturer()?->getId(),
            'toolMethods' => $afterToolNames,
        ];
        $this->trackWriteResult($isNew, $before, $after);

        return $this->ecuCache[$key] = $ecu;
    }

    private function resolvePreferredEcu(array $ecus): ?Ecu
    {
        return $ecus[0] ?? null;
    }

    private function upsertEcuOption(array $optionRow, VehicleDataPool $pool): EcuOption
    {
        $name = trim($optionRow['name'] ?? '');
        $key = mb_strtolower($name) . '|' . $this->entityCacheKey($pool);
        if (isset($this->ecuOptionCache[$key])) {
            return $this->ecuOptionCache[$key];
        }

        $option = $this->entityManager->getRepository(EcuOption::class)->findOneBy([
            'name' => $name,
            'vehicleDataPool' => $pool,
        ]);
        $isNew = false;
        if (!$option instanceof EcuOption) {
            $option = new EcuOption();
            $option->setVehicleDataPool($pool);
            $this->entityManager->persist($option);
            $isNew = true;
        }

        $before = [
            'name' => $option->getName(),
            'description' => $option->getDescription(),
        ];

        $option->setName($name);
        $option->setDescription($optionRow['description'] ?? null);

        $after = [
            'name' => $option->getName(),
            'description' => $option->getDescription(),
        ];
        $this->trackWriteResult($isNew, $before, $after);

        return $this->ecuOptionCache[$key] = $option;
    }

    private function slug(string $value): string
    {
        $slug = trim($value);
        if ('' === $slug) {
            return '';
        }

        return strtolower((string) $this->slugger->slug($slug));
    }

    private function normalizeScraperSource(?string $scraperSource): string
    {
        $scraperSource = trim((string) ($scraperSource ?? ''));
        if ('' === $scraperSource) {
            return 'legacy';
        }

        $scraperSource = strtolower((string) $this->slugger->slug($scraperSource, '_'));

        return '' !== $scraperSource ? mb_substr($scraperSource, 0, 80) : 'legacy';
    }

    private function entityCacheKey(object $entity): string
    {
        if (method_exists($entity, 'getId')) {
            $id = $entity->getId();
            if (null !== $id && '' !== (string) $id) {
                return 'id:' . (string) $id;
            }
        }

        return 'obj:' . spl_object_id($entity);
    }

    private function resetImportState(): void
    {
        $this->brandCache = [];
        $this->seriesCache = [];
        $this->modelCache = [];
        $this->engineCache = [];
        $this->stageCache = [];
        $this->ecuManufacturerCache = [];
        $this->ecuCache = [];
        $this->ecuOptionCache = [];
        $this->toolMethodCache = [];
        $this->writeStats = [
            'Created' => 0,
            'Updated' => 0,
            'Unchanged' => 0,
        ];
    }

    /**
     * @param array<string,mixed> $before
     * @param array<string,mixed> $after
     */
    private function trackWriteResult(bool $isNew, array $before, array $after): void
    {
        if ($isNew) {
            ++$this->writeStats['Created'];

            return;
        }

        if ($before !== $after) {
            ++$this->writeStats['Updated'];

            return;
        }

        ++$this->writeStats['Unchanged'];
    }

    private function nullableString(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $resolved = trim((string) $value);

        return '' !== $resolved ? $resolved : null;
    }

    private function stringValue(mixed $value, string $fallback): string
    {
        return $this->nullableString($value) ?? $fallback;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if (null === $value) {
            return null;
        }

        $resolved = trim((string) $value);
        if ('' === $resolved) {
            return null;
        }

        return (int) $resolved;
    }

    /**
     * @param array<int, mixed> $tools
     *
     * @return list<ToolMethode>
     */
    private function resolveToolMethods(array $tools): array
    {
        $methods = [];
        foreach ($tools as $toolName) {
            $normalized = $this->nullableString($toolName);
            if (null === $normalized || '--' === $normalized) {
                continue;
            }

            $methods[] = $this->resolveToolMethod($normalized);
        }

        return $methods;
    }

    private function resolveToolMethod(string $name): ToolMethode
    {
        $key = mb_strtolower($name);
        if (isset($this->toolMethodCache[$key])) {
            return $this->toolMethodCache[$key];
        }

        $repository = $this->entityManager->getRepository(ToolMethode::class);
        $tool = $repository->findOneBy(['name' => $name]);
        if (!$tool instanceof ToolMethode) {
            $tool = new ToolMethode();
            $tool->setName($name);
            $this->entityManager->persist($tool);
        }

        return $this->toolMethodCache[$key] = $tool;
    }

    private function clearToolMethods(Ecu $ecu): void
    {
        foreach ($ecu->getToolMethods() as $toolMethod) {
            $ecu->removeToolMethod($toolMethod);
        }
    }

    private function sanitizeToolNames(mixed $value): array
    {
        if (!is_iterable($value)) {
            return [];
        }

        $names = [];
        foreach ($value as $entry) {
            $resolved = $this->nullableString($entry);
            if (null === $resolved || '--' === $resolved) {
                continue;
            }

            $names[] = $resolved;
        }

        return $names;
    }

    private function normalizeControlUnitKind(mixed $value): ?string
    {
        $resolved = strtoupper(trim((string) $value));
        if ('' === $resolved) {
            return null;
        }

        if (in_array($resolved, [Ecu::CONTROL_UNIT_KIND_TCU, Ecu::CONTROL_UNIT_KIND_MCU], true)) {
            return $resolved;
        }

        return null;
    }
}
