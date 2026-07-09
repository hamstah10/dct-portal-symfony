<?php

namespace App\Command;

use App\Entity\BrandMaster;
use App\Entity\Ecu;
use App\Entity\EcuManufacturer;
use App\Entity\EcuManufacturerMaster;
use App\Entity\EcuMaster;
use App\Entity\EcuOption;
use App\Entity\EcuOptionMaster;
use App\Entity\VehicleBrand;
use App\Entity\VehicleDataContext;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleEngine;
use App\Entity\VehicleModel;
use App\Entity\VehicleSeries;
use App\Entity\VehicleStage;
use App\Entity\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(
    name: 'app:vehicle-data:import-olsx-json',
    description: 'Importiert eine OLSX-Fahrzeug-JSON in einen separaten Datenbestand.',
)]
class ImportOlsxVehiclePoolCommand extends Command
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
     * @var array<string, BrandMaster>
     */
    private array $brandMasterCache = [];

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
     * @var array<string, EcuManufacturerMaster>
     */
    private array $ecuManufacturerMasterCache = [];

    /**
     * @var array<string, Ecu>
     */
    private array $ecuCache = [];

    /**
     * @var array<string, EcuMaster>
     */
    private array $ecuMasterCache = [];

    /**
     * @var array<string, EcuOption>
     */
    private array $ecuOptionCache = [];

    /**
     * @var array<string, EcuOptionMaster>
     */
    private array $ecuOptionMasterCache = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        parent::__construct();
        $this->slugger = new AsciiSlugger();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Pfad zur OLSX-JSON-Datei')
            ->addOption('pool-key', null, InputOption::VALUE_REQUIRED, 'Technischer Key fuer den Datenbestand')
            ->addOption('pool-name', null, InputOption::VALUE_REQUIRED, 'Name des Datenbestands')
            ->addOption('context-key', null, InputOption::VALUE_REQUIRED, 'Technischer Key fuer den Datenkontext')
            ->addOption('context-name', null, InputOption::VALUE_REQUIRED, 'Name des Datenkontexts')
            ->addOption('vehicle-type', null, InputOption::VALUE_REQUIRED, 'VehicleType-Slug oder Name, z. B. pkw')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Import nur pruefen, nicht speichern');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $this->resolveFile((string) $input->getArgument('file'));
        if (!is_file($file)) {
            $output->writeln(sprintf('<error>Datei nicht gefunden: %s</error>', $file));

            return Command::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($file), true);
        if (!is_array($payload) || [] === $payload) {
            $output->writeln('<error>JSON konnte nicht gelesen werden oder ist leer.</error>');

            return Command::FAILURE;
        }

        $vehicleType = $this->resolveVehicleType((string) ($input->getOption('vehicle-type') ?? ''), $file);
        if (!$vehicleType instanceof VehicleType) {
            $output->writeln('<error>Passender Fahrzeugtyp wurde nicht gefunden.</error>');

            return Command::FAILURE;
        }

        $basename = pathinfo($file, PATHINFO_FILENAME);
        $poolKey = $this->normalizeKey((string) ($input->getOption('pool-key') ?? ''), 'test-' . $basename);
        $poolName = $this->normalizeLabel((string) ($input->getOption('pool-name') ?? ''), 'Test Pool ' . $basename);
        $contextKey = $this->normalizeKey((string) ($input->getOption('context-key') ?? ''), $poolKey . '-preview');
        $contextName = $this->normalizeLabel((string) ($input->getOption('context-name') ?? ''), $poolName . ' Preview');
        $dryRun = (bool) $input->getOption('dry-run');

        $connection = $this->entityManager->getConnection();
        if (!$dryRun) {
            $connection->beginTransaction();
        }

        try {
            $pool = $this->upsertPool($poolKey, $poolName, $vehicleType, $file);
            $context = $this->upsertContext($contextKey, $contextName, $pool);
            $stats = $this->importBrands($payload, $pool, $vehicleType);

            if ($dryRun) {
                $this->entityManager->clear();
            } else {
                $this->entityManager->flush();
                $connection->commit();
            }

            $output->writeln(sprintf('Datei: %s', $file));
            $output->writeln(sprintf('Pool: %s (%s)', $poolName, $poolKey));
            $output->writeln(sprintf('Kontext: %s (%s)', $contextName, $contextKey));
            $output->writeln(sprintf('Fahrzeugtyp: %s', $vehicleType->getName()));
            foreach ($stats as $label => $count) {
                $output->writeln(sprintf('%s: %d', $label, $count));
            }
            if ($dryRun) {
                $output->writeln('<comment>Dry-Run: Keine Daten gespeichert.</comment>');
            }

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            if (!$dryRun && $connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    private function resolveFile(string $file): string
    {
        $value = trim($file);
        if ('' === $value) {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        $candidate = $this->projectDir . '/' . ltrim($value, '/');
        if (is_file($candidate)) {
            return $candidate;
        }

        return $value;
    }

    private function normalizeKey(string $value, string $fallback): string
    {
        $resolved = trim($value);
        if ('' === $resolved) {
            $resolved = $fallback;
        }

        return strtolower((string) $this->slugger->slug($resolved)->toString());
    }

    private function normalizeLabel(string $value, string $fallback): string
    {
        $resolved = trim($value);

        return '' !== $resolved ? $resolved : $fallback;
    }

    private function resolveVehicleType(string $requestedType, string $file): ?VehicleType
    {
        $lookup = trim($requestedType);
        if ('' === $lookup) {
            $lookup = $this->detectVehicleTypeFromFile($file);
        }

        $normalized = mb_strtolower(trim($lookup));
        $mapped = self::TYPE_MAP[$normalized] ?? $normalized;

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

    private function detectVehicleTypeFromFile(string $file): string
    {
        $filename = mb_strtolower(basename($file));
        foreach (array_keys(self::TYPE_MAP) as $sourceType) {
            if (str_contains($filename, '_' . $sourceType . '_') || str_contains($filename, '-' . $sourceType . '-')) {
                return $sourceType;
            }
        }

        return 'car';
    }

    private function upsertPool(string $poolKey, string $poolName, VehicleType $vehicleType, string $file): VehicleDataPool
    {
        $pool = $this->entityManager->getRepository(VehicleDataPool::class)->findOneBy(['poolKey' => $poolKey]);
        if (!$pool instanceof VehicleDataPool) {
            $pool = new VehicleDataPool();
            $pool->setPoolKey($poolKey);
            $this->entityManager->persist($pool);
        }

        $pool
            ->setName($poolName)
            ->setDescription(sprintf('Import aus OLSX JSON: %s', basename($file)))
            ->setActive(true)
            ->addVehicleType($vehicleType);

        return $pool;
    }

    private function upsertContext(string $contextKey, string $contextName, VehicleDataPool $pool): VehicleDataContext
    {
        $context = $this->entityManager->getRepository(VehicleDataContext::class)->findOneBy(['contextKey' => $contextKey]);
        if (!$context instanceof VehicleDataContext) {
            $context = new VehicleDataContext();
            $context->setContextKey($contextKey);
            $this->entityManager->persist($context);
        }

        $context
            ->setName($contextName)
            ->setDescription(sprintf('Preview-Kontext fuer %s', $pool->getName()))
            ->setUsageType(VehicleDataContext::USAGE_PREVIEW)
            ->setActive(true)
            ->setDefaultVehicleDataPool($pool)
            ->addAllowedDataPool($pool);

        return $context;
    }

    /**
     * @param array<int, mixed> $payload
     *
     * @return array<string, int>
     */
    private function importBrands(array $payload, VehicleDataPool $pool, VehicleType $vehicleType): array
    {
        $stats = [
            'Brands' => 0,
            'Series' => 0,
            'Models' => 0,
            'Engines' => 0,
            'Stages' => 0,
            'ECUs' => 0,
            'Options' => 0,
            'Manufacturers' => 0,
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

                        $this->assignPreferredEcuToStages($engine);
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * @param array<string, mixed> $brandRow
     */
    private function upsertBrand(array $brandRow, VehicleDataPool $pool, VehicleType $vehicleType): VehicleBrand
    {
        $slug = $this->nullableString($brandRow['brand_slug'] ?? null);
        $name = $this->stringValue($brandRow['brand_name'] ?? null, 'Unbekannte Marke');
        $masterBrand = $this->upsertBrandMaster($slug, $name, $this->nullableString($brandRow['brand_logo'] ?? null), $this->nullableString($brandRow['brand_url'] ?? null));
        $cacheKey = $this->buildCacheKey([(string) $pool->getId(), $slug ?? $name]);

        $brand = $this->brandCache[$cacheKey] ?? null;
        if (!$brand instanceof VehicleBrand) {
            $brand = $this->entityManager->getRepository(VehicleBrand::class)->findOneBy([
                'vehicleDataPool' => $pool,
                'slug' => $slug,
            ]);
            if (!$brand instanceof VehicleBrand) {
                $brand = $this->entityManager->getRepository(VehicleBrand::class)->findOneBy([
                    'vehicleDataPool' => $pool,
                    'name' => $name,
                ]);
            }
        }
        if (!$brand instanceof VehicleBrand) {
            $brand = new VehicleBrand();
            $this->entityManager->persist($brand);
        }

        $brand
            ->setVehicleDataPool($pool)
            ->setVehicleType($vehicleType)
            ->setMasterBrand($masterBrand)
            ->setName($name)
            ->setSlug($slug)
            ->setImage($this->nullableString($brandRow['brand_logo'] ?? null))
            ->setDescription($this->nullableString($brandRow['brand_url'] ?? null))
            ->setActive(true);

        $this->brandCache[$cacheKey] = $brand;

        return $brand;
    }

    private function upsertBrandMaster(?string $slug, string $name, ?string $image, ?string $description): BrandMaster
    {
        $normalizedSlug = $this->normalizeSlug($slug);
        $cacheKey = $this->buildCacheKey([$normalizedSlug ?? $name]);
        $master = $this->brandMasterCache[$cacheKey] ?? null;
        if (!$master instanceof BrandMaster) {
            $repository = $this->entityManager->getRepository(BrandMaster::class);
            if (null !== $normalizedSlug) {
                $master = $repository->findOneBy(['slug' => $normalizedSlug]);
            }
            if (!$master instanceof BrandMaster) {
                $master = $repository->findOneBy(['name' => $name]);
            }
        }
        if (!$master instanceof BrandMaster) {
            $master = new BrandMaster();
            $this->entityManager->persist($master);
        }

        $master
            ->setName($name)
            ->setSlug($normalizedSlug)
            ->setImage($this->resolveBrandMasterImage($normalizedSlug, $name, $image, $master->getImage()))
            ->setDescription($description);

        $this->brandMasterCache[$cacheKey] = $master;

        return $master;
    }

    private function resolveBrandMasterImage(?string $slug, string $name, ?string ...$fallbacks): ?string
    {
        foreach ($this->buildBrandMasterImageCandidates($slug, $name) as $candidate) {
            $absolutePath = sprintf('%s/%s', $this->projectDir, $candidate);
            if (is_file($absolutePath)) {
                return $candidate;
            }
        }

        return $this->nullableString($fallbacks[0] ?? null)
            ?? $this->nullableString($fallbacks[1] ?? null);
    }

    /**
     * @return array<int, string>
     */
    private function buildBrandMasterImageCandidates(?string $slug, string $name): array
    {
        $keys = [];

        $normalizedSlug = $this->normalizeSlug($slug);
        if (null !== $normalizedSlug) {
            $keys[] = $normalizedSlug;
        }

        $normalizedName = $this->normalizeSlug((string) $this->slugger->slug($name)->toString());
        if (null !== $normalizedName) {
            $keys[] = $normalizedName;
        }

        $aliases = [
            'mercedes' => 'mercedes-benz',
        ];

        $paths = [];
        foreach (array_unique(array_filter($keys)) as $key) {
            $paths[] = 'assets/icons/brands-cars/Icons/' . $key . '.svg';
            if (isset($aliases[$key])) {
                $paths[] = 'assets/icons/brands-cars/Icons/' . $aliases[$key] . '.svg';
            }
        }

        return $paths;
    }

    /**
     * @param array<string, mixed> $seriesRow
     */
    private function upsertSeries(array $seriesRow, VehicleBrand $brand, VehicleDataPool $pool, int $sorting): VehicleSeries
    {
        $slug = $this->nullableString($seriesRow['slug'] ?? null);
        $name = $this->stringValue($seriesRow['display_name'] ?? $seriesRow['name'] ?? null, 'Ohne Baureihe');
        $cacheKey = $this->buildCacheKey([(string) $pool->getId(), (string) spl_object_id($brand), $slug ?? $name]);

        $series = $this->seriesCache[$cacheKey] ?? null;
        if (!$series instanceof VehicleSeries) {
            $series = $this->entityManager->getRepository(VehicleSeries::class)->findOneBy([
                'vehicleDataPool' => $pool,
                'vehicleBrand' => $brand,
                'slug' => $slug,
            ]);
            if (!$series instanceof VehicleSeries) {
                $series = $this->entityManager->getRepository(VehicleSeries::class)->findOneBy([
                    'vehicleDataPool' => $pool,
                    'vehicleBrand' => $brand,
                    'name' => $name,
                ]);
            }
        }
        if (!$series instanceof VehicleSeries) {
            $series = new VehicleSeries();
            $this->entityManager->persist($series);
        }

        $series
            ->setVehicleDataPool($pool)
            ->setVehicleBrand($brand)
            ->setName($name)
            ->setSlug($slug)
            ->setImage($this->nullableString($seriesRow['image'] ?? null))
            ->setDescription($this->nullableString($seriesRow['url'] ?? null))
            ->setSorting($sorting)
            ->setActive(true);

        $this->seriesCache[$cacheKey] = $series;

        return $series;
    }

    /**
     * @param array<string, mixed> $modelRow
     */
    private function upsertModel(array $modelRow, VehicleSeries $series, VehicleDataPool $pool, int $sorting): VehicleModel
    {
        $slug = $this->nullableString($modelRow['slug'] ?? null);
        $name = $this->stringValue($modelRow['display_name'] ?? $modelRow['name'] ?? null, 'Ohne Modell');
        $cacheKey = $this->buildCacheKey([(string) $pool->getId(), (string) spl_object_id($series), $slug ?? $name]);

        $model = $this->modelCache[$cacheKey] ?? null;
        if (!$model instanceof VehicleModel) {
            $model = $this->entityManager->getRepository(VehicleModel::class)->findOneBy([
                'vehicleDataPool' => $pool,
                'vehicleSeries' => $series,
                'slug' => $slug,
            ]);
            if (!$model instanceof VehicleModel) {
                $model = $this->entityManager->getRepository(VehicleModel::class)->findOneBy([
                    'vehicleDataPool' => $pool,
                    'vehicleSeries' => $series,
                    'name' => $name,
                ]);
            }
        }
        if (!$model instanceof VehicleModel) {
            $model = new VehicleModel();
            $this->entityManager->persist($model);
        }

        $model
            ->setVehicleDataPool($pool)
            ->setVehicleSeries($series)
            ->setName($name)
            ->setSlug($slug)
            ->setDescription($this->nullableString($modelRow['url'] ?? null))
            ->setSorting($sorting)
            ->setActive(true);

        $this->modelCache[$cacheKey] = $model;

        return $model;
    }

    /**
     * @param array<string, mixed> $engineRow
     */
    private function upsertEngine(array $engineRow, VehicleModel $model, VehicleDataPool $pool, int $sorting): VehicleEngine
    {
        $slug = $this->nullableString($engineRow['slug'] ?? null);
        $name = $this->stringValue($engineRow['name'] ?? null, 'Ohne Motor');
        $original = is_array($engineRow['original'] ?? null) ? $engineRow['original'] : [];
        $cacheKey = $this->buildCacheKey([(string) $pool->getId(), (string) spl_object_id($model), $slug ?? $name]);

        $engine = $this->engineCache[$cacheKey] ?? null;
        if (!$engine instanceof VehicleEngine) {
            $engine = $this->entityManager->getRepository(VehicleEngine::class)->findOneBy([
                'vehicleDataPool' => $pool,
                'vehicleModel' => $model,
                'slug' => $slug,
            ]);
            if (!$engine instanceof VehicleEngine) {
                $engine = $this->entityManager->getRepository(VehicleEngine::class)->findOneBy([
                    'vehicleDataPool' => $pool,
                    'vehicleModel' => $model,
                    'name' => $name,
                ]);
            }
        }
        if (!$engine instanceof VehicleEngine) {
            $engine = new VehicleEngine();
            $this->entityManager->persist($engine);
        }

        $engine
            ->setVehicleDataPool($pool)
            ->setVehicleModel($model)
            ->setName($name)
            ->setSlug($slug)
            ->setDescription($this->nullableString($engineRow['url'] ?? null))
            ->setHorsepower($this->toNullableInt($original['power_ps'] ?? null))
            ->setTorque($this->toNullableInt($original['torque_nm'] ?? null))
            ->setFuel($this->nullableString($engineRow['fuel_type'] ?? null))
            ->setSorting($sorting)
            ->setActive(true);

        $this->engineCache[$cacheKey] = $engine;

        return $engine;
    }

    /**
     * @param array<string, mixed> $stageRow
     */
    private function upsertStage(array $stageRow, VehicleEngine $engine, VehicleDataPool $pool, int $sorting): VehicleStage
    {
        $name = $this->stringValue($stageRow['name'] ?? null, 'Stage');
        $cacheKey = $this->buildCacheKey([(string) $pool->getId(), (string) spl_object_id($engine), $name]);

        $stage = $this->stageCache[$cacheKey] ?? null;
        if (!$stage instanceof VehicleStage) {
            $stage = $this->entityManager->getRepository(VehicleStage::class)->findOneBy([
                'vehicleDataPool' => $pool,
                'vehicleEngine' => $engine,
                'name' => $name,
            ]);
        }
        if (!$stage instanceof VehicleStage) {
            $stage = new VehicleStage();
            $this->entityManager->persist($stage);
        }

        $stage
            ->setVehicleDataPool($pool)
            ->setVehicleEngine($engine)
            ->setName($name)
            ->setDescription($this->nullableString($stageRow['url'] ?? null))
            ->setHp($this->toNullableInt($stageRow['power_ps'] ?? null))
            ->setNm($this->toNullableInt($stageRow['torque_nm'] ?? null))
            ->setOriginalPs($engine->getHorsepower())
            ->setOriginalNm($engine->getTorque())
            ->setIcon(null)
            ->setImages(null)
            ->setVideos(null)
            ->setEcu(null)
            ->setActive(true);

        $this->stageCache[$cacheKey] = $stage;

        return $stage;
    }

    private function resolveEcuManufacturer(string $name): ?EcuManufacturer
    {
        $resolvedName = trim($name);
        if ('' === $resolvedName) {
            return null;
        }

        $cacheKey = mb_strtolower($resolvedName);
        $manufacturer = $this->ecuManufacturerCache[$cacheKey] ?? null;
        $masterManufacturer = $this->upsertEcuManufacturerMaster($resolvedName);
        if (!$manufacturer instanceof EcuManufacturer) {
            $manufacturer = $this->entityManager->getRepository(EcuManufacturer::class)->findOneBy(['name' => $resolvedName]);
        }
        if ($manufacturer instanceof EcuManufacturer) {
            $manufacturer->setMasterManufacturer($masterManufacturer);
            $this->ecuManufacturerCache[$cacheKey] = $manufacturer;

            return $manufacturer;
        }

        $manufacturer = new EcuManufacturer();
        $manufacturer
            ->setName($resolvedName)
            ->setMasterManufacturer($masterManufacturer);
        $this->entityManager->persist($manufacturer);
        $this->ecuManufacturerCache[$cacheKey] = $manufacturer;

        return $manufacturer;
    }

    private function upsertEcuManufacturerMaster(string $name): EcuManufacturerMaster
    {
        $normalizedName = trim($name);
        $slug = $this->normalizeSlug((string) $this->slugger->slug($normalizedName)->toString());
        $cacheKey = $this->buildCacheKey([$slug ?? $normalizedName]);
        $master = $this->ecuManufacturerMasterCache[$cacheKey] ?? null;
        if (!$master instanceof EcuManufacturerMaster) {
            $repository = $this->entityManager->getRepository(EcuManufacturerMaster::class);
            if (null !== $slug) {
                $master = $repository->findOneBy(['slug' => $slug]);
            }
            if (!$master instanceof EcuManufacturerMaster) {
                $master = $repository->findOneBy(['name' => $normalizedName]);
            }
        }
        if (!$master instanceof EcuManufacturerMaster) {
            $master = new EcuManufacturerMaster();
            $this->entityManager->persist($master);
        }

        $master
            ->setName($normalizedName)
            ->setSlug($slug);

        $this->ecuManufacturerMasterCache[$cacheKey] = $master;

        return $master;
    }

    /**
     * @param array<string, mixed> $controllerRow
     */
    private function upsertEcu(array $controllerRow, VehicleEngine $engine, VehicleDataPool $pool, ?EcuManufacturer $manufacturer): Ecu
    {
        $modelName = $this->stringValue($controllerRow['model'] ?? null, 'Unknown ECU');
        $kind = $this->normalizeControlUnitKind($controllerRow['ecu_type'] ?? null);
        $masterEcu = $this->upsertEcuMaster($modelName, $kind, null !== $kind ? strtolower($kind) : null, $manufacturer?->getMasterManufacturer());
        $cacheKey = $this->buildCacheKey([
            (string) $pool->getId(),
            (string) spl_object_id($engine),
            (string) ($manufacturer?->getName() ?? ''),
            $modelName,
            $kind ?? '',
        ]);

        $ecu = $this->ecuCache[$cacheKey] ?? null;
        if (!$ecu instanceof Ecu) {
            $ecu = new Ecu();
            $this->entityManager->persist($ecu);
        }

        $ecu
            ->setVehicleDataPool($pool)
            ->setMasterEcu($masterEcu)
            ->setManufacturer($manufacturer)
            ->setName($modelName)
            ->setType(null !== $kind ? strtolower($kind) : null)
            ->setControlUnitKind($kind)
            ->setImage(null);

        $this->ecuCache[$cacheKey] = $ecu;

        return $ecu;
    }

    private function upsertEcuMaster(
        string $name,
        ?string $controlUnitKind,
        ?string $type,
        ?EcuManufacturerMaster $manufacturerMaster,
    ): EcuMaster {
        $slugBase = implode('-', array_filter([
            mb_strtolower(trim((string) $manufacturerMaster?->getName())),
            mb_strtolower(trim($name)),
            mb_strtolower(trim((string) $controlUnitKind)),
            mb_strtolower(trim((string) $type)),
        ]));
        $slug = $this->normalizeSlug((string) $this->slugger->slug($slugBase)->toString());
        $cacheKey = $this->buildCacheKey([
            (string) ($manufacturerMaster?->getId() ?? 0),
            $slug ?? $name,
            $controlUnitKind ?? '',
            $type ?? '',
        ]);

        $master = $this->ecuMasterCache[$cacheKey] ?? null;
        if (!$master instanceof EcuMaster) {
            $repository = $this->entityManager->getRepository(EcuMaster::class);
            if (null !== $slug) {
                $master = $repository->findOneBy(['slug' => $slug]);
            }
            if (
                !$master instanceof EcuMaster
                && $manufacturerMaster instanceof EcuManufacturerMaster
            ) {
                $master = $repository->findOneBy([
                    'name' => $name,
                    'manufacturerMaster' => $manufacturerMaster,
                    'controlUnitKind' => $controlUnitKind,
                ]);
            }
        }
        if (!$master instanceof EcuMaster) {
            $master = new EcuMaster();
            $this->entityManager->persist($master);
        }

        $master
            ->setName($name)
            ->setSlug($slug)
            ->setType($type)
            ->setControlUnitKind($controlUnitKind)
            ->setImage(null)
            ->setManufacturerMaster($manufacturerMaster);

        $this->ecuMasterCache[$cacheKey] = $master;

        return $master;
    }

    /**
     * @param array<string, mixed> $optionRow
     */
    private function upsertEcuOption(array $optionRow, VehicleDataPool $pool): EcuOption
    {
        $name = $this->stringValue($optionRow['name'] ?? null, 'Option');
        $slug = $this->normalizeSlug((string) $this->slugger->slug($name)->toString());
        $masterOption = $this->upsertEcuOptionMaster(
            $name,
            $slug,
            $this->nullableString($optionRow['description'] ?? null),
            $this->nullableString($optionRow['icon'] ?? null)
        );
        $cacheKey = $this->buildCacheKey([(string) $pool->getId(), $slug ?? $name]);

        $option = $this->ecuOptionCache[$cacheKey] ?? null;
        if (!$option instanceof EcuOption) {
            $option = $this->entityManager->getRepository(EcuOption::class)->findOneBy([
                'vehicleDataPool' => $pool,
                'slug' => $slug,
            ]);
            if (!$option instanceof EcuOption) {
                $option = $this->entityManager->getRepository(EcuOption::class)->findOneBy([
                    'vehicleDataPool' => $pool,
                    'name' => $name,
                ]);
            }
        }
        if (!$option instanceof EcuOption) {
            $option = new EcuOption();
            $this->entityManager->persist($option);
        }

        $option
            ->setVehicleDataPool($pool)
            ->setMasterOption($masterOption)
            ->setName($name)
            ->setSlug($slug)
            ->setDescription($this->nullableString($optionRow['description'] ?? null))
            ->setPrice(null)
            ->setIcon(null)
            ->setImage(null)
            ->setImages(null)
            ->setIsMainOption(false);

        $this->ecuOptionCache[$cacheKey] = $option;

        return $option;
    }

    private function upsertEcuOptionMaster(string $name, ?string $slug, ?string $description, ?string $icon = null): EcuOptionMaster
    {
        $cacheKey = $this->buildCacheKey([$slug ?? $name]);
        $master = $this->ecuOptionMasterCache[$cacheKey] ?? null;
        if (!$master instanceof EcuOptionMaster) {
            $repository = $this->entityManager->getRepository(EcuOptionMaster::class);
            if (null !== $slug) {
                $master = $repository->findOneBy(['slug' => $slug]);
            }
            if (!$master instanceof EcuOptionMaster) {
                $master = $repository->findOneBy(['name' => $name]);
            }
        }
        if (!$master instanceof EcuOptionMaster) {
            $master = new EcuOptionMaster();
            $this->entityManager->persist($master);
        }

        $master
            ->setName($name)
            ->setSlug($slug)
            ->setDescription($description)
            ->setIcon($icon ?? $master->getIcon());

        $this->ecuOptionMasterCache[$cacheKey] = $master;

        return $master;
    }

    /**
     * @param mixed $controllers
     *
     * @return array<int, array<string, mixed>>
     */
    private function flattenControllers(mixed $controllers): array
    {
        $flat = [];
        foreach ($this->toList($controllers) as $controllerGroup) {
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
                ];
            }
        }

        return $flat;
    }

    /**
     * @param mixed $value
     *
     * @return array<int, mixed>
     */
    private function toList(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
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

    private function normalizeSlug(?string $value): ?string
    {
        $resolved = $this->nullableString($value);

        return null !== $resolved ? strtolower($resolved) : null;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if (null === $value || '' === trim((string) $value)) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeControlUnitKind(mixed $value): ?string
    {
        $resolved = strtoupper(trim((string) $value));
        if (in_array($resolved, [Ecu::CONTROL_UNIT_KIND_MCU, Ecu::CONTROL_UNIT_KIND_TCU], true)) {
            return $resolved;
        }

        return null;
    }

    /**
     * @param array<int, string> $parts
     */
    private function buildCacheKey(array $parts): string
    {
        return implode('|', array_map(
            static fn (string $part): string => str_replace('|', '-', mb_strtolower(trim($part))),
            $parts
        ));
    }

    private function assignPreferredEcuToStages(VehicleEngine $engine): void
    {
        $preferredEcu = $this->resolvePreferredEcu(array_values(array_filter(
            $engine->getEcuRelations()->toArray(),
            static fn (mixed $ecu): bool => $ecu instanceof Ecu
        )));
        if (!$preferredEcu instanceof Ecu || null === $preferredEcu->getId()) {
            return;
        }

        foreach ($engine->getStages() as $stage) {
            if (!$stage instanceof VehicleStage) {
                continue;
            }

            $stage->setEcu($preferredEcu->getId());
        }
    }

    /**
     * @param array<int, Ecu> $ecus
     */
    private function resolvePreferredEcu(array $ecus): ?Ecu
    {
        if ([] === $ecus) {
            return null;
        }

        usort($ecus, static function (Ecu $left, Ecu $right): int {
            $leftRank = Ecu::CONTROL_UNIT_KIND_MCU === $left->getControlUnitKind() ? 0 : 1;
            $rightRank = Ecu::CONTROL_UNIT_KIND_MCU === $right->getControlUnitKind() ? 0 : 1;

            return [$leftRank, $left->getId() ?? PHP_INT_MAX, $left->getName()] <=> [$rightRank, $right->getId() ?? PHP_INT_MAX, $right->getName()];
        });

        return $ecus[0] ?? null;
    }
}
