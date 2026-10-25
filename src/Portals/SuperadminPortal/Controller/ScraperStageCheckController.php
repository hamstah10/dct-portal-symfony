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

    #[Route('/operations-api/stage-check', name: 'superadmin_portal_stage_check', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        // Schwellen für Leistung (PS) und Drehmoment (Nm) getrennt; "threshold" gilt als gemeinsamer Fallback.
        $fallback = (float) $request->query->get('threshold', (string) self::DEFAULT_THRESHOLD);
        $thresholdPs = (float) $request->query->get('thresholdPs', (string) $fallback);
        $thresholdNm = (float) $request->query->get('thresholdNm', (string) $fallback);
        if ($thresholdPs <= 0 || $thresholdNm <= 0) {
            throw new BadRequestHttpException('Schwellen müssen größer als 0 sein.');
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
            ['poolKey' => self::DEFAULT_POOL_KEY],
        );

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
            $flaggedPower = null !== $deltaPs && $deltaPs > $thresholdPs;
            $flaggedTorque = null !== $deltaNm && $deltaNm > $thresholdNm;
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
                'flagged' => $flagged,
                'flagged_power' => $flaggedPower,
                'flagged_torque' => $flaggedTorque,
                'has_chart' => (bool) $row['has_chart'],
            ];
        }

        $result = [];
        foreach ($engines as $engine) {
            $engine['flagged'] = (bool) array_filter($engine['stages'], static fn (array $s): bool => $s['flagged']);
            if ($onlyFlagged && !$engine['flagged']) {
                continue;
            }
            $result[] = $engine;
        }

        return $this->json(['data' => ['threshold_ps' => $thresholdPs, 'threshold_nm' => $thresholdNm, 'engines' => $result]]);
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
