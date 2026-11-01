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
 * Stage-Prüfung: Stage-Werte gegen das Original, Kennzeichnung auffälliger Stages und Dyno-Kurven.
 * Fehlt die Kurve einer Stage, wird sie aus der Kurve der niedrigsten Stage mit Kurve abgeleitet
 * (gleiche Kurvenform, um den Unterschied der Spitzenwerte verschoben). Abgeleitete Kurven sind gekennzeichnet.
 */
final class ScraperStageCheckController extends AbstractController
{
    private const DEFAULT_POOL_KEY = 'tfd_voll';

    private const DEFAULT_THRESHOLD = 40.0;

    private const TABLE_PREFIX = 'tx_chiptuningdatabase_';

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly Connection $connection,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[Route('/operations-api/stage-check/pools', name: 'superadmin_portal_stage_check_pools', methods: ['GET'])]
    public function pools(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $p = self::TABLE_PREFIX;
        $rows = $this->connection->fetchAllAssociative("SELECT pool_key, name FROM {$p}vehicle_data_pool ORDER BY active DESC, name");

        return $this->json(['data' => [
            'default' => self::DEFAULT_POOL_KEY,
            'pools' => array_map(static fn (array $r): array => ['key' => (string) $r['pool_key'], 'name' => (string) $r['name']], $rows),
        ]]);
    }

    #[Route('/operations-api/stage-check', name: 'superadmin_portal_stage_check', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        // Jede der vier Schwellen (PS %, Nm %, PS absolut, Nm absolut) ist optional; leer = Kriterium nicht verwenden.
        [$thresholdPs, $thresholdNm] = $this->percentThresholds($request);
        $absPs = $this->optionalThreshold($request, 'thresholdPsAbs');
        $absNm = $this->optionalThreshold($request, 'thresholdNmAbs');
        $poolKey = (string) $request->query->get('pool', self::DEFAULT_POOL_KEY);
        if (1 !== preg_match('/^[a-z0-9_]+$/', $poolKey)) {
            throw new BadRequestHttpException('Ungültiger Datenpool.');
        }
        $onlyFlagged = $request->query->getBoolean('onlyFlagged');
        $needle = mb_strtolower(trim((string) $request->query->get('brand', '')));

        $p = self::TABLE_PREFIX;
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
            SELECT e.uid AS engine_uid, e.name AS engine_name, m.name AS model_name, sr.name AS series_name, b.name AS brand_name, t.name AS type_name,
                   s.uid AS stage_uid, s.name AS stage_name, s.hp, s.nm, s.original_ps, s.original_nm,
                   COALESCE(e.horsepower, s.original_ps) AS engine_original_ps,
                   COALESCE(e.torque, s.original_nm) AS engine_original_nm,
                   (s.dyno_chart IS NOT NULL AND s.dyno_chart <> '') AS has_chart
            FROM {$p}vehiclestage s
            JOIN {$p}vehicleengine e ON e.uid = s.relation_to_vehicleengine AND e.active = 1
            JOIN {$p}vehiclemodel m ON m.uid = e.relation_to_vehiclemodel AND m.active = 1
            JOIN {$p}vehicleseries sr ON sr.uid = m.relation_to_vehicleseries AND sr.active = 1
            JOIN {$p}vehiclebrand b ON b.uid = sr.relation_to_vehiclebrand AND b.active = 1
            JOIN {$p}vehicletype t ON t.uid = b.relation_to_vehicletype
            WHERE s.active = 1
              AND s.vehicle_data_pool = (SELECT pool.uid FROM {$p}vehicle_data_pool pool WHERE pool.pool_key = :poolKey)
            ORDER BY b.name, sr.name, m.name, e.name, s.name
            SQL,
            ['poolKey' => $poolKey],
        );

        $result = array_values(array_filter(
            $this->groupEngines($rows, $thresholdPs, $thresholdNm, $needle, $absPs, $absNm),
            static fn (array $engine): bool => !$onlyFlagged || $engine['flagged'],
        ));

        return $this->json(['data' => ['threshold_ps' => $thresholdPs, 'threshold_nm' => $thresholdNm, 'engines' => $result]]);
    }

    #[Route('/operations-api/stage-check/engine/{engineUid}', name: 'superadmin_portal_stage_check_engine', requirements: ['engineUid' => '\d+'], methods: ['GET'])]
    public function engine(int $engineUid, Request $request): JsonResponse
    {
        $this->assertAccess($request);

        [$thresholdPs, $thresholdNm] = $this->percentThresholds($request);
        $absPs = $this->optionalThreshold($request, 'thresholdPsAbs');
        $absNm = $this->optionalThreshold($request, 'thresholdNmAbs');

        $p = self::TABLE_PREFIX;
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
            SELECT e.uid AS engine_uid, e.name AS engine_name, e.horsepower AS engine_ps, e.torque AS engine_nm,
                   e.fuel, e.displacement, e.compression, e.bore_stroke, e.engine_code, e.vmax, e.acceleration_0_100, e.acceleration_0_200,
                   e.acceleration_100_200, e.drivetrain, e.turbocharger, e.weight, e.hsn, e.ecus, e.read_methods, e.price, e.description,
                   b.uid AS brand_uid, m.uid AS model_uid, sr.uid AS series_uid,
                   m.name AS model_name, sr.name AS series_name, b.name AS brand_name, t.name AS type_name, pool.name AS pool_name,
                   s.uid AS stage_uid, s.name AS stage_name, s.hp, s.nm, s.original_ps, s.original_nm,
                   COALESCE(e.horsepower, s.original_ps) AS engine_original_ps,
                   COALESCE(e.torque, s.original_nm) AS engine_original_nm,
                   (s.dyno_chart IS NOT NULL AND s.dyno_chart <> '') AS has_chart
            FROM {$p}vehiclestage s
            JOIN {$p}vehicleengine e ON e.uid = s.relation_to_vehicleengine
            JOIN {$p}vehiclemodel m ON m.uid = e.relation_to_vehiclemodel
            JOIN {$p}vehicleseries sr ON sr.uid = m.relation_to_vehicleseries
            JOIN {$p}vehiclebrand b ON b.uid = sr.relation_to_vehiclebrand
            JOIN {$p}vehicletype t ON t.uid = b.relation_to_vehicletype
            LEFT JOIN {$p}vehicle_data_pool pool ON pool.uid = s.vehicle_data_pool
            WHERE s.active = 1 AND e.uid = :engine
            ORDER BY s.name
            SQL,
            ['engine' => $engineUid],
        );
        if ([] === $rows) {
            throw new NotFoundHttpException('Keine Stages für diesen Motor.');
        }

        $engine = $this->groupEngines($rows, $thresholdPs, $thresholdNm, '', $absPs, $absNm)[0];
        $engine['original_ps'] = null === $rows[0]['engine_ps'] ? null : (int) $rows[0]['engine_ps'];
        $engine['original_nm'] = null === $rows[0]['engine_nm'] ? null : (int) $rows[0]['engine_nm'];
        $engine['pool'] = (string) ($rows[0]['pool_name'] ?? '');
        $engine['info'] = $this->engineInfo($rows[0]);
        $engine['same_brand'] = $this->sameBrandEngines((int) $rows[0]['brand_uid'], $engineUid, (string) $rows[0]['engine_name']);
        $engine['scraper'] = $this->scraperMatches(
            (string) $rows[0]['type_name'],
            (string) $rows[0]['brand_name'],
            (string) $rows[0]['series_name'],
            (string) $rows[0]['model_name'],
            (string) $rows[0]['engine_name'],
            null === $rows[0]['engine_ps'] ? null : (int) $rows[0]['engine_ps'],
            null === $rows[0]['engine_nm'] ? null : (int) $rows[0]['engine_nm'],
        );

        return $this->json(['data' => ['threshold_ps' => $thresholdPs, 'threshold_nm' => $thresholdNm, 'engine' => $engine]]);
    }

    #[Route('/operations-api/stage-chart/{engineUid}', name: 'superadmin_portal_stage_chart', requirements: ['engineUid' => '\d+'], methods: ['GET'])]
    public function chart(int $engineUid, Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $p = self::TABLE_PREFIX;
        $stages = $this->connection->fetchAllAssociative(
            <<<SQL
            SELECT s.uid, s.name, s.hp, s.nm, s.dyno_chart
            FROM {$p}vehiclestage s
            WHERE s.relation_to_vehicleengine = :engine AND s.active = 1
            ORDER BY s.name
            SQL,
            ['engine' => $engineUid],
        );
        if ([] === $stages) {
            throw new NotFoundHttpException('Keine Stages für diesen Motor.');
        }

        $curves = [];
        $original = null;
        $base = null;
        foreach ($stages as $stage) {
            $chart = $this->decodeChart($stage['dyno_chart']);
            if (null !== $chart && null === $original && isset($chart['original'])) {
                $original = $chart['original'];
            }
            if (null !== $chart && isset($chart['optimized']) && null === $base) {
                $base = ['stage' => $stage, 'curve' => $chart['optimized']];
            }
        }

        foreach ($stages as $stage) {
            $chart = $this->decodeChart($stage['dyno_chart']);
            if (null !== $chart && isset($chart['optimized'])) {
                $curves[] = ['name' => (string) $stage['name'], 'source' => 'chart', 'power' => $chart['optimized']['power'] ?? [], 'torque' => $chart['optimized']['torque'] ?? []];
                continue;
            }

            if (null === $base || null === $stage['hp'] || null === $stage['nm']) {
                $curves[] = ['name' => (string) $stage['name'], 'source' => 'none', 'power' => [], 'torque' => []];
                continue;
            }

            // Verschiebung um den Unterschied der Spitzenwerte zur Basis-Stage.
            $basePower = $base['curve']['power'] ?? [];
            $baseTorque = $base['curve']['torque'] ?? [];
            $offsetPower = (float) $stage['hp'] - $this->peak($basePower);
            $offsetTorque = (float) $stage['nm'] - $this->peak($baseTorque);
            $curves[] = [
                'name' => (string) $stage['name'],
                'source' => 'derived',
                'base' => (string) $base['stage']['name'],
                'power' => $this->shift($basePower, $offsetPower),
                'torque' => $this->shift($baseTorque, $offsetTorque),
            ];
        }

        return $this->json(['data' => [
            'engine_uid' => $engineUid,
            'original' => $original ?? ['power' => [], 'torque' => []],
            'stages' => $curves,
        ]]);
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return list<array<string, mixed>>
     */
    private function groupEngines(array $rows, ?float $thresholdPs, ?float $thresholdNm, string $needle, ?float $absPs = null, ?float $absNm = null): array
    {
        $engines = [];
        foreach ($rows as $row) {
            if ('' !== $needle && !str_contains(mb_strtolower((string) $row['brand_name']), $needle)) {
                continue;
            }

            $uid = (int) $row['engine_uid'];
            $engines[$uid] ??= [
                'engine_uid' => $uid,
                'brand' => (string) $row['brand_name'],
                'series' => (string) $row['series_name'],
                'model' => (string) $row['model_name'],
                'engine' => (string) $row['engine_name'],
                'type' => (string) $row['type_name'],
                'stages' => [],
            ];

            $orig = $row['original_ps'] ?? $row['engine_original_ps'];
            $origNm = $row['original_nm'] ?? $row['engine_original_nm'];
            $deltaPs = $this->deltaPct($row['hp'], $orig);
            $deltaNm = $this->deltaPct($row['nm'], $origNm);
            $absDeltaPs = null === $row['hp'] || null === $orig ? null : (int) $row['hp'] - (int) $orig;
            $absDeltaNm = null === $row['nm'] || null === $origNm ? null : (int) $row['nm'] - (int) $origNm;
            $flaggedPower = (null !== $thresholdPs && null !== $deltaPs && $deltaPs > $thresholdPs) || (null !== $absPs && null !== $absDeltaPs && $absDeltaPs > $absPs);
            $flaggedTorque = (null !== $thresholdNm && null !== $deltaNm && $deltaNm > $thresholdNm) || (null !== $absNm && null !== $absDeltaNm && $absDeltaNm > $absNm);
            $flagged = $flaggedPower || $flaggedTorque;

            $engines[$uid]['stages'][] = [
                'stage_uid' => (int) $row['stage_uid'],
                'name' => (string) $row['stage_name'],
                'power_ps' => null === $row['hp'] ? null : (int) $row['hp'],
                'torque_nm' => null === $row['nm'] ? null : (int) $row['nm'],
                'original_ps' => null === $orig ? null : (int) $orig,
                'original_nm' => null === $origNm ? null : (int) $origNm,
                'delta_power_pct' => $deltaPs,
                'delta_torque_pct' => $deltaNm,
                'delta_power_abs' => $absDeltaPs,
                'delta_torque_abs' => $absDeltaNm,
                'flagged' => $flagged,
                'flagged_power' => $flaggedPower,
                'flagged_torque' => $flaggedTorque,
                'has_chart' => (bool) $row['has_chart'],
            ];
        }

        $result = [];
        foreach ($engines as $engine) {
            $engine['flagged'] = (bool) array_filter($engine['stages'], static fn (array $s): bool => $s['flagged']);
            $result[] = $engine;
        }

        return $result;
    }

    /**
     * Weitere Motordaten für die Detailansicht; leere Felder entfallen.
     *
     * @param array<string, mixed> $row
     *
     * @return list<array{label: string, value: string}>
     */
    private function engineInfo(array $row): array
    {
        $fields = [
            'engine_code' => 'Motorcode', 'fuel' => 'Kraftstoff', 'displacement' => 'Hubraum', 'compression' => 'Verdichtung',
            'bore_stroke' => 'Bohrung x Hub', 'turbocharger' => 'Turbolader', 'drivetrain' => 'Antrieb', 'weight' => 'Gewicht',
            'vmax' => 'Höchstgeschwindigkeit', 'acceleration_0_100' => '0–100 km/h', 'acceleration_0_200' => '0–200 km/h',
            'acceleration_100_200' => '100–200 km/h', 'hsn' => 'HSN', 'ecus' => 'Steuergeräte', 'read_methods' => 'Auslesemethoden', 'price' => 'Preis',
        ];
        $info = [];
        foreach ($fields as $key => $label) {
            $value = trim((string) ($row[$key] ?? ''));
            if ('' !== $value) {
                $info[] = ['label' => $label, 'value' => $value];
            }
        }

        return $info;
    }

    /**
     * Gleich benannte Motoren in anderen Fahrzeugen derselben Marke (gleicher Datenpool, da die Marke je Pool eigene Zeilen hat).
     *
     * @return list<array<string, mixed>>
     */
    private function sameBrandEngines(int $brandUid, int $engineUid, string $name): array
    {
        $p = self::TABLE_PREFIX;
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
            SELECT e.uid, e.name, e.horsepower, e.torque, m.name AS model_name, sr.name AS series_name,
                   (SELECT COUNT(*) FROM {$p}vehiclestage s WHERE s.relation_to_vehicleengine = e.uid AND s.active = 1) AS stage_count
            FROM {$p}vehicleengine e
            JOIN {$p}vehiclemodel m ON m.uid = e.relation_to_vehiclemodel AND m.active = 1
            JOIN {$p}vehicleseries sr ON sr.uid = m.relation_to_vehicleseries AND sr.active = 1
            WHERE sr.relation_to_vehiclebrand = :brand AND e.active = 1 AND e.uid <> :engine
              AND LOWER(TRIM(e.name)) = LOWER(TRIM(:name))
            ORDER BY sr.name, m.name
            SQL,
            ['brand' => $brandUid, 'engine' => $engineUid, 'name' => $name],
        );
        $own = $this->connection->fetchAssociative("SELECT horsepower, torque FROM {$p}vehicleengine WHERE uid = :engine", ['engine' => $engineUid]) ?: [];

        return array_map(static fn (array $r): array => [
            'engine_uid' => (int) $r['uid'],
            'series' => (string) $r['series_name'],
            'model' => (string) $r['model_name'],
            'engine' => (string) $r['name'],
            'power_ps' => null === $r['horsepower'] ? null : (int) $r['horsepower'],
            'torque_nm' => null === $r['torque'] ? null : (int) $r['torque'],
            'stage_count' => (int) $r['stage_count'],
            'same_values' => ($r['horsepower'] ?? null) == ($own['horsepower'] ?? null) && ($r['torque'] ?? null) == ($own['torque'] ?? null),
        ], $rows);
    }

    private const SCRAPER_TYPES = [
        'PKW' => 'car', 'LKW' => 'truck', 'Motorrad' => 'moto', 'Agrar' => 'agricultural', 'Quad' => 'atv', 'Boot' => 'boat', 'Jetski' => 'jetski',
    ];

    /**
     * Passende Motoren aus den Scraper-Daten (Olsx, Trucks UK, Dyno, Tuning-Datenbank) mit deren Stages, bestes Ergebnis zuerst.
     * Treffer ist der gleich benannte Motor in der Marke; Baureihe und Modell erhöhen die Güte.
     *
     * @return list<array<string, mixed>>
     */
    private function scraperMatches(string $typeName, string $brand, string $series, string $model, string $engine, ?int $ps = null, ?int $nm = null): array
    {
        $type = self::SCRAPER_TYPES[$typeName] ?? null;
        if (null === $type) {
            return [];
        }

        $base = $this->projectDir.'/scrapy-remote-scraper/data/viewer';
        $sources = [
            'olsx' => $base.'/'.$type.'/brands',
            'truck_chiptuningfiles' => $base.'/truck_chiptuningfiles/'.$type.'/brands',
            'dyno' => $base.'/dyno/brands',
        ];
        if ('car' === $type) {
            // Die Tuning-Datenbank gibt es nur für Pkw; ihre Marken-Dateien sind sehr groß (über 40 MB).
            $sources['td'] = $base.'/td/brands';
        }
        $nEngine = $this->norm($engine);
        $nSeries = $this->norm($series);
        $nModel = $this->norm($model);
        $matches = [];

        foreach ($sources as $provider => $dir) {
            $file = $this->brandFile($dir, $brand);
            if (null === $file) {
                continue;
            }
            if ('td' === $provider) {
                ini_set('memory_limit', '3G');
            }
            $data = json_decode((string) file_get_contents($file), true);
            foreach (\is_array($data) ? ($data['models'] ?? []) : [] as $scraperSeries) {
                $seriesName = $this->displayName($scraperSeries);
                foreach ($scraperSeries['versions'] ?? [] as $scraperModel) {
                    $modelName = $this->displayName($scraperModel);
                    foreach ($scraperModel['engines'] ?? [] as $candidate) {
                        $candidateNames = [$this->norm((string) ($candidate['name'] ?? '')), $this->norm((string) ($candidate['display_name'] ?? ''))];
                        $score = ($this->norm($seriesName) === $nSeries ? 2 : 0) + ($this->norm($modelName) === $nModel ? 2 : 0);
                        // Treffer über den Motornamen, ersatzweise bei gleicher Baureihe und gleichem Modell über identische Originalwerte.
                        $sameName = '' !== $nEngine && \in_array($nEngine, $candidateNames, true);
                        $sameOriginal = null !== $ps && null !== $nm
                            && (int) ($candidate['original']['power_ps'] ?? -1) === $ps && (int) ($candidate['original']['torque_nm'] ?? -1) === $nm;
                        if (!$sameName && !(4 === $score && $sameOriginal)) {
                            continue;
                        }
                        $matches[] = [
                            'score' => $score,
                            'provider' => $provider,
                            'series' => $seriesName,
                            'model' => $modelName,
                            'engine' => $this->displayName($candidate),
                            'url' => (string) ($candidate['url'] ?? ''),
                            'original_ps' => isset($candidate['original']['power_ps']) ? (int) $candidate['original']['power_ps'] : null,
                            'original_nm' => isset($candidate['original']['torque_nm']) ? (int) $candidate['original']['torque_nm'] : null,
                            'exact' => 4 === $score,
                            'stages' => array_map(static fn (array $st): array => [
                                'name' => (string) ($st['name'] ?? ''),
                                'power_ps' => isset($st['power_ps']) ? (int) $st['power_ps'] : null,
                                'torque_nm' => isset($st['torque_nm']) ? (int) $st['torque_nm'] : null,
                            ], array_values(array_filter((array) ($candidate['stages'] ?? []), 'is_array'))),
                        ];
                    }
                }
            }
            unset($data);
        }

        usort($matches, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_map(static function (array $m): array {
            unset($m['score']);

            return $m;
        }, \array_slice($matches, 0, 8));
    }

    private function brandFile(string $dir, string $brand): ?string
    {
        if (!is_dir($dir)) {
            return null;
        }
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($brand)), '-');
        if (is_file($dir.'/'.$slug.'.json')) {
            return $dir.'/'.$slug.'.json';
        }
        $flat = preg_replace('/[^a-z0-9]/', '', mb_strtolower($brand));
        foreach (glob($dir.'/*.json') ?: [] as $file) {
            if (preg_replace('/[^a-z0-9]/', '', basename($file, '.json')) === $flat) {
                return $file;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $row */
    private function displayName(array $row): string
    {
        $display = trim((string) ($row['display_name'] ?? ''));

        return '' !== $display ? $display : trim((string) ($row['name'] ?? ''));
    }

    private function norm(string $value): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $value)));
    }

    /**
     * Prozent-Schwellen für PS und Nm. Fehlt der Parameter ganz, gilt der Standard; ein leerer Wert schaltet das Kriterium ab.
     *
     * @return array{0: float|null, 1: float|null}
     */
    private function percentThresholds(Request $request): array
    {
        $fallback = (float) $request->query->get('threshold', (string) self::DEFAULT_THRESHOLD);

        return [
            $request->query->has('thresholdPs') ? $this->optionalThreshold($request, 'thresholdPs') : $fallback,
            $request->query->has('thresholdNm') ? $this->optionalThreshold($request, 'thresholdNm') : $fallback,
        ];
    }

    /** Optionale Schwelle; leer oder nicht größer als 0 heißt: nicht verwenden. */
    private function optionalThreshold(Request $request, string $name): ?float
    {
        $raw = trim((string) $request->query->get($name, ''));
        if ('' === $raw) {
            return null;
        }
        $value = (float) str_replace(',', '.', $raw);

        return $value > 0 ? $value : null;
    }

    private function deltaPct(mixed $value, mixed $reference): ?float
    {
        if (null === $value || null === $reference || (float) $reference <= 0) {
            return null;
        }

        return round(((float) $value - (float) $reference) / (float) $reference * 100, 1);
    }

    /** @return array<string, mixed>|null */
    private function decodeChart(mixed $json): ?array
    {
        if (!\is_string($json) || '' === trim($json)) {
            return null;
        }

        $data = json_decode($json, true);

        return \is_array($data) ? $data : null;
    }

    /** @param list<array{0: float|int, 1: float|int}> $points */
    private function peak(array $points): float
    {
        $values = array_map(static fn (array $p): float => (float) $p[1], $points);

        return [] === $values ? 0.0 : max($values);
    }

    /**
     * @param list<array{0: float|int, 1: float|int}> $points
     *
     * @return list<array{0: float|int, 1: float}>
     */
    private function shift(array $points, float $offset): array
    {
        return array_map(static fn (array $p): array => [$p[0], round((float) $p[1] + $offset, 1)], $points);
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
