<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Abgleich Scraper-Ausgabe gegen Fahrzeugdatenbank über alle Ebenen: Marke, Baureihe, Modell und Motor.
 * Der Import legt aus dem Scraper-"model" eine Baureihe und aus der "version" ein Modell an; die Ebenen
 * werden hier entsprechend zugeordnet.
 */
final class ScraperDbDiffController extends AbstractController
{
    /** Scraper-Typ zu Fahrzeugtyp-Name in der Datenbank. */
    private const TYPE_NAMES = [
        'car' => 'PKW',
        'truck' => 'LKW',
        'moto' => 'Motorrad',
        'agricultural' => 'Agrar',
        'boat' => 'Boot',
        'jetski' => 'Jetski',
        'atv' => 'Quad',
    ];

    private const DEFAULT_POOL_KEY = 'tfd_voll';

    private const TABLE_PREFIX = 'tx_chiptuningdatabase_';

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly Connection $connection,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[Route('/operations-api/scraper-db-diff', name: 'superadmin_portal_scraper_db_diff', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        [$type, $poolKey] = $this->readScope($request);

        $scraperBrands = $this->loadScraperBrands($type);
        $dbBrands = $this->loadDbBrands($type, $poolKey);

        $brands = [];
        foreach (array_unique([...array_keys($scraperBrands), ...array_keys($dbBrands)]) as $key) {
            $brands[] = $this->compareBrand($scraperBrands[$key] ?? null, $dbBrands[$key] ?? null);
        }
        usort($brands, static fn (array $a, array $b): int => strcasecmp($a['brand'], $b['brand']));

        return $this->json(['data' => [
            'type' => $type,
            'pool' => $poolKey,
            'brands' => $brands,
        ]]);
    }

    /** Detailansicht einer Marke: alle Baureihen, Modelle und Motoren beider Quellen nebeneinander. */
    #[Route('/operations-api/scraper-db-diff/brand', name: 'superadmin_portal_scraper_db_diff_brand', methods: ['GET'])]
    public function brandDetail(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        [$type, $poolKey] = $this->readScope($request);

        $brandKey = $this->normalize((string) $request->query->get('brand', ''));
        if ('' === $brandKey) {
            throw new BadRequestHttpException('Marke fehlt.');
        }

        $scraper = $this->loadScraperBrands($type)[$brandKey] ?? null;
        $db = $this->loadDbBrands($type, $poolKey)[$brandKey] ?? null;
        if (null === $scraper && null === $db) {
            throw new NotFoundHttpException('Marke nicht gefunden.');
        }

        $scraperSeries = $scraper['series'] ?? [];
        $dbSeries = $db['series'] ?? [];

        $rows = [];
        foreach (array_unique([...array_keys($scraperSeries), ...array_keys($dbSeries)]) as $seriesKey) {
            $leftSeries = $scraperSeries[$seriesKey] ?? null;
            $rightSeries = $dbSeries[$seriesKey] ?? null;
            $seriesName = (string) ($leftSeries['name'] ?? $rightSeries['name'] ?? '');
            $leftModels = $leftSeries['models'] ?? [];
            $rightModels = $rightSeries['models'] ?? [];

            foreach (array_unique([...array_keys($leftModels), ...array_keys($rightModels)]) as $modelKey) {
                $left = $leftModels[$modelKey] ?? null;
                $right = $rightModels[$modelKey] ?? null;

                $scraperEngines = $left['engines'] ?? [];
                $dbEngines = $right['engines'] ?? [];

                if (null === $leftSeries) {
                    $status = 'series_only_db';
                } elseif (null === $rightSeries) {
                    $status = 'series_only_scraper';
                } elseif (null === $left) {
                    $status = 'only_db';
                } elseif (null === $right) {
                    $status = 'only_scraper';
                } else {
                    $status = count($scraperEngines) === count($dbEngines) && [] === array_diff_key($scraperEngines, $dbEngines) && [] === array_diff_key($dbEngines, $scraperEngines)
                        ? 'unchanged'
                        : 'changed';
                }

                $rows[] = [
                    'key' => $seriesKey . '|' . $modelKey,
                    'series' => $seriesName,
                    'model' => (string) ($left['name'] ?? $right['name'] ?? ''),
                    'scraper_engines' => null === $left ? null : count($scraperEngines),
                    'db_engines' => null === $right ? null : count($dbEngines),
                    'status' => $status,
                    'engines_only_scraper' => array_values(array_diff_key($scraperEngines, $dbEngines)),
                    'engines_only_db' => array_values(array_diff_key($dbEngines, $scraperEngines)),
                    'engines_both' => array_values(array_intersect_key($scraperEngines, $dbEngines)),
                ];
            }
        }

        usort($rows, static fn (array $a, array $b): int => strnatcasecmp($a['series'], $b['series']) ?: strnatcasecmp($a['model'], $b['model']));

        return $this->json(['data' => [
            'brand' => $scraper['name'] ?? $db['name'] ?? '',
            'type' => $type,
            'pool' => $poolKey,
            'status' => null !== $scraper && null !== $db ? 'both' : (null !== $scraper ? 'only_scraper' : 'only_db'),
            'models' => $rows,
        ]]);
    }

    /** @return array{0: string, 1: string} */
    private function readScope(Request $request): array
    {
        $type = (string) $request->query->get('type', 'car');
        if (!isset(self::TYPE_NAMES[$type])) {
            throw new BadRequestHttpException('Unbekannter Fahrzeugtyp.');
        }

        $poolKey = (string) $request->query->get('pool', self::DEFAULT_POOL_KEY);
        if (1 !== preg_match('/^[a-z0-9_]+$/', $poolKey)) {
            throw new BadRequestHttpException('Ungültiger Pool-Schlüssel.');
        }

        return [$type, $poolKey];
    }

    /**
     * Scraper-Marken als Baum: Marke -> Baureihe (Scraper "model") -> Modell (Scraper "version") -> Motoren.
     *
     * @return array<string, array{name: string, series: array<string, array{name: string, models: array<string, array{name: string, engines: array<string, string>}>}>}>
     */
    private function loadScraperBrands(string $type): array
    {
        // Aufbereitete Marken-Dateien der Scraper-Ansicht. Die Gesamtausgabe ist zu groß für den Speicher.
        $files = glob($this->projectDir . '/scrapy-remote-scraper/data/viewer/' . $type . '/brands/*.json') ?: [];
        if ([] === $files) {
            throw new NotFoundHttpException('Scraper-Daten für diesen Typ sind noch nicht aufbereitet.');
        }

        $brands = [];
        foreach ($files as $file) {
            $brand = json_decode((string) file_get_contents($file), true, 512, \JSON_THROW_ON_ERROR);
            $name = trim((string) ($brand['brand_name'] ?? '')) !== '' ? (string) $brand['brand_name'] : (string) ($brand['brand_slug'] ?? '');
            $key = $this->normalize($name);
            if ('' === $key) {
                continue;
            }

            $brands[$key] ??= ['name' => $name, 'series' => []];
            foreach ($brand['models'] ?? [] as $series) {
                $seriesName = $this->displayName($series);
                $seriesKey = $this->normalize($seriesName);
                if ('' === $seriesKey) {
                    continue;
                }

                $brands[$key]['series'][$seriesKey] ??= ['name' => $seriesName, 'models' => []];
                foreach ($series['versions'] ?? [] as $model) {
                    $modelName = $this->displayName($model);
                    $modelKey = $this->normalize($modelName);
                    if ('' === $modelKey) {
                        continue;
                    }

                    $brands[$key]['series'][$seriesKey]['models'][$modelKey] ??= ['name' => $modelName, 'engines' => []];
                    foreach ($model['engines'] ?? [] as $engine) {
                        $engineName = trim((string) ($engine['name'] ?? $engine['display_name'] ?? ''));
                        if ('' !== $engineName) {
                            $brands[$key]['series'][$seriesKey]['models'][$modelKey]['engines'][$this->normalize($engineName)] = $engineName;
                        }
                    }
                }
            }
        }

        return $brands;
    }

    /**
     * @return array<string, array{name: string, series: array<string, array{name: string, models: array<string, array{name: string, engines: array<string, string>}>}>}>
     */
    private function loadDbBrands(string $type, string $poolKey): array
    {
        $p = self::TABLE_PREFIX;
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
            SELECT b.name AS brand_name, s.name AS series_name, m.name AS model_name, e.name AS engine_name
            FROM {$p}vehicletype t
            JOIN {$p}vehiclebrand b
                 ON b.relation_to_vehicletype = t.uid
                AND b.active = 1
                AND b.vehicle_data_pool = (SELECT pool.uid FROM {$p}vehicle_data_pool pool WHERE pool.pool_key = :poolKey)
            JOIN {$p}vehicleseries s ON s.relation_to_vehiclebrand = b.uid AND s.active = 1
            JOIN {$p}vehiclemodel m ON m.relation_to_vehicleseries = s.uid AND m.active = 1
            LEFT JOIN {$p}vehicleengine e ON e.relation_to_vehiclemodel = m.uid AND e.active = 1
            WHERE t.name = :typeName
            SQL,
            ['poolKey' => $poolKey, 'typeName' => self::TYPE_NAMES[$type]],
        );

        $brands = [];
        foreach ($rows as $row) {
            $name = (string) $row['brand_name'];
            $key = $this->normalize($name);
            $seriesName = trim((string) $row['series_name']);
            $seriesKey = $this->normalize($seriesName);
            $modelName = trim((string) $row['model_name']);
            $modelKey = $this->normalize($modelName);
            if ('' === $key || '' === $seriesKey || '' === $modelKey) {
                continue;
            }

            $brands[$key] ??= ['name' => $name, 'series' => []];
            $brands[$key]['series'][$seriesKey] ??= ['name' => $seriesName, 'models' => []];
            $brands[$key]['series'][$seriesKey]['models'][$modelKey] ??= ['name' => $modelName, 'engines' => []];

            $engineName = trim((string) ($row['engine_name'] ?? ''));
            if ('' !== $engineName) {
                $brands[$key]['series'][$seriesKey]['models'][$modelKey]['engines'][$this->normalize($engineName)] = $engineName;
            }
        }

        return $brands;
    }

    /**
     * Vergleicht eine Marke über alle Ebenen.
     *
     * @param array{name: string, series: array<string, mixed>}|null $scraper
     * @param array{name: string, series: array<string, mixed>}|null $db
     *
     * @return array<string, mixed>
     */
    private function compareBrand(?array $scraper, ?array $db): array
    {
        $scraperSeries = $scraper['series'] ?? [];
        $dbSeries = $db['series'] ?? [];

        $seriesNew = [];
        $seriesMissing = [];
        $seriesUnchanged = 0;
        $new = [];
        $missing = [];
        $changed = [];
        $unchanged = 0;
        $enginesNew = 0;
        $enginesMissing = 0;

        foreach ($scraperSeries as $seriesKey => $series) {
            if (!isset($dbSeries[$seriesKey])) {
                $seriesNew[] = $series['name'];
            }
        }
        foreach ($dbSeries as $seriesKey => $series) {
            if (!isset($scraperSeries[$seriesKey])) {
                $seriesMissing[] = $series['name'];
            } else {
                ++$seriesUnchanged;
            }
        }

        foreach (array_unique([...array_keys($scraperSeries), ...array_keys($dbSeries)]) as $seriesKey) {
            $leftModels = $scraperSeries[$seriesKey]['models'] ?? [];
            $rightModels = $dbSeries[$seriesKey]['models'] ?? [];
            $seriesName = (string) ($scraperSeries[$seriesKey]['name'] ?? $dbSeries[$seriesKey]['name'] ?? '');

            foreach (array_unique([...array_keys($leftModels), ...array_keys($rightModels)]) as $modelKey) {
                $left = $leftModels[$modelKey] ?? null;
                $right = $rightModels[$modelKey] ?? null;
                $label = $this->modelLabel($seriesName, (string) ($left['name'] ?? $right['name'] ?? ''));
                $leftEngines = $left['engines'] ?? [];
                $rightEngines = $right['engines'] ?? [];

                if (null === $right) {
                    $new[] = $label;
                    $enginesNew += count($leftEngines);
                    continue;
                }
                if (null === $left) {
                    $missing[] = $label;
                    $enginesMissing += count($rightEngines);
                    continue;
                }

                $onlyScraper = count(array_diff_key($leftEngines, $rightEngines));
                $onlyDb = count(array_diff_key($rightEngines, $leftEngines));
                $enginesNew += $onlyScraper;
                $enginesMissing += $onlyDb;

                if (0 === $onlyScraper && 0 === $onlyDb) {
                    ++$unchanged;
                    continue;
                }

                $changed[] = ['name' => $label, 'scraper' => count($leftEngines), 'db' => count($rightEngines)];
            }
        }

        sort($seriesNew, \SORT_NATURAL | \SORT_FLAG_CASE);
        sort($seriesMissing, \SORT_NATURAL | \SORT_FLAG_CASE);
        sort($new, \SORT_NATURAL | \SORT_FLAG_CASE);
        sort($missing, \SORT_NATURAL | \SORT_FLAG_CASE);
        usort($changed, static fn (array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));

        return [
            'brand' => $scraper['name'] ?? $db['name'] ?? '',
            'status' => null !== $scraper && null !== $db ? 'both' : (null !== $scraper ? 'only_scraper' : 'only_db'),
            'series_new' => $seriesNew,
            'series_missing' => $seriesMissing,
            'series_unchanged' => $seriesUnchanged,
            'new' => $new,
            'missing' => $missing,
            'changed' => $changed,
            'unchanged' => $unchanged,
            'engines_new' => $enginesNew,
            'engines_missing' => $enginesMissing,
        ];
    }

    private function displayName(array $row): string
    {
        $display = trim((string) ($row['display_name'] ?? ''));

        return '' !== $display ? $display : trim((string) ($row['name'] ?? ''));
    }

    private function modelLabel(string $series, string $model): string
    {
        return trim($series) . ' › ' . trim($model);
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $value)));
    }

    private function assertAccess(Request $request): void
    {
        $systemType = (string) $request->attributes->get('_brand_system_type', '');
        if (SystemType::SUPERADMIN === $systemType) {
            return;
        }

        $portal = $this->portalRuntimeResolver->requirePortalSystem($request);
        if ($portal->getPortalType() !== Portal::TYPE_SUPERADMIN_PORTAL) {
            throw new NotFoundHttpException('Superadmin operations API not available for this portal.');
        }
    }
}
