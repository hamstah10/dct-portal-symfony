<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Übersicht, bei welchen Stages der Datenbank die Dyno-Kurve (vehiclestage.dyno_chart) fehlt.
 * Geliefert werden nur Motoren mit mindestens einer Stage ohne Kurve, dazu die Summen je Marke.
 */
final class DynoMissingChartsController extends AbstractController
{
    private const DEFAULT_POOL_KEY = 'tfd_voll';

    private const TABLE_PREFIX = 'tx_chiptuningdatabase_';

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly Connection $connection,
    ) {
    }

    #[Route('/operations-api/dyno-missing', name: 'superadmin_portal_dyno_missing', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $poolKey = (string) $request->query->get('pool', self::DEFAULT_POOL_KEY);
        if (1 !== preg_match('/^[a-z0-9_]+$/', $poolKey)) {
            throw new BadRequestHttpException('Ungültiger Datenpool.');
        }
        $needle = mb_strtolower(trim((string) $request->query->get('brand', '')));

        $p = self::TABLE_PREFIX;
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
            SELECT b.name AS brand_name, sr.name AS series_name, m.name AS model_name, e.uid AS engine_uid, e.name AS engine_name,
                   s.name AS stage_name, (s.dyno_chart IS NULL OR TRIM(s.dyno_chart) = '') AS missing
            FROM {$p}vehiclestage s
            JOIN {$p}vehicleengine e ON e.uid = s.relation_to_vehicleengine AND e.active = 1
            JOIN {$p}vehiclemodel m ON m.uid = e.relation_to_vehiclemodel AND m.active = 1
            JOIN {$p}vehicleseries sr ON sr.uid = m.relation_to_vehicleseries AND sr.active = 1
            JOIN {$p}vehiclebrand b ON b.uid = sr.relation_to_vehiclebrand AND b.active = 1
            WHERE s.active = 1
              AND s.vehicle_data_pool = (SELECT pool.uid FROM {$p}vehicle_data_pool pool WHERE pool.pool_key = :poolKey)
            ORDER BY b.name, sr.name, m.name, e.name, s.name
            SQL,
            ['poolKey' => $poolKey],
        );

        $brands = [];
        foreach ($rows as $row) {
            $brand = (string) $row['brand_name'];
            if ('' !== $needle && !str_contains(mb_strtolower($brand), $needle)) {
                continue;
            }

            $brands[$brand] ??= ['brand' => $brand, 'engine_count' => 0, 'stage_count' => 0, 'missing_stage_count' => 0, 'engines' => []];
            $uid = (int) $row['engine_uid'];
            if (!isset($brands[$brand]['engines'][$uid])) {
                ++$brands[$brand]['engine_count'];
                $brands[$brand]['engines'][$uid] = [
                    'engine_uid' => $uid,
                    'series' => (string) $row['series_name'],
                    'model' => (string) $row['model_name'],
                    'engine' => (string) $row['engine_name'],
                    'stage_count' => 0,
                    'missing_stages' => [],
                ];
            }

            ++$brands[$brand]['stage_count'];
            ++$brands[$brand]['engines'][$uid]['stage_count'];
            if ((bool) $row['missing']) {
                ++$brands[$brand]['missing_stage_count'];
                $brands[$brand]['engines'][$uid]['missing_stages'][] = (string) $row['stage_name'];
            }
        }

        $result = [];
        foreach ($brands as $brand) {
            $engines = array_values(array_filter(
                $brand['engines'],
                static fn (array $e): bool => [] !== $e['missing_stages'],
            ));
            $brand['missing_engine_count'] = \count($engines);
            $brand['engines'] = array_map(static function (array $e): array {
                $e['missing_count'] = \count($e['missing_stages']);
                $e['all_missing'] = $e['missing_count'] === $e['stage_count'];

                return $e;
            }, $engines);
            $result[] = $brand;
        }

        return $this->json(['data' => ['pool' => $poolKey, 'brands' => $result]]);
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
