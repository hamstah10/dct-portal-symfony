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
 * Kennzahlen je Fahrzeugtyp aus der Fahrzeugdatenbank, damit sie mit den Scraper-Daten verglichen werden können.
 * Zählt nur aktive Einträge des gewählten Datenpools.
 */
final class ScraperDbCompareController extends AbstractController
{
    /** Zuordnung Fahrzeugtyp-Name in der Datenbank zu Scraper-Typ. */
    private const TYPE_MAP = [
        'PKW' => 'car',
        'LKW' => 'truck',
        'Motorrad' => 'moto',
        'Agrar' => 'agricultural',
        'Boot' => 'boat',
        'Jetski' => 'jetski',
        'Quad' => 'atv',
    ];

    private const DEFAULT_POOL_KEY = 'tfd_voll';

    private const TABLE_PREFIX = 'tx_chiptuningdatabase_';

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly Connection $connection,
    ) {
    }

    #[Route('/operations-api/scraper-db-compare', name: 'superadmin_portal_scraper_db_compare', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $poolKey = (string) $request->query->get('pool', self::DEFAULT_POOL_KEY);
        if (1 !== preg_match('/^[a-z0-9_]+$/', $poolKey)) {
            throw new BadRequestHttpException('Ungültiger Pool-Schlüssel.');
        }

        $p = self::TABLE_PREFIX;
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
            SELECT t.name AS type_name,
                   COUNT(DISTINCT b.uid) AS brands,
                   COUNT(DISTINCT s.uid) AS series,
                   COUNT(DISTINCT m.uid) AS models,
                   COUNT(DISTINCT e.uid) AS engines,
                   COUNT(DISTINCT st.uid) AS stages
            FROM {$p}vehicletype t
            JOIN {$p}vehiclebrand b
                 ON b.relation_to_vehicletype = t.uid
                AND b.active = 1
                AND b.vehicle_data_pool = (SELECT pool.uid FROM {$p}vehicle_data_pool pool WHERE pool.pool_key = :poolKey)
            LEFT JOIN {$p}vehicleseries s ON s.relation_to_vehiclebrand = b.uid AND s.active = 1
            LEFT JOIN {$p}vehiclemodel m ON m.relation_to_vehicleseries = s.uid AND m.active = 1
            LEFT JOIN {$p}vehicleengine e ON e.relation_to_vehiclemodel = m.uid AND e.active = 1
            LEFT JOIN {$p}vehiclestage st ON st.relation_to_vehicleengine = e.uid AND st.active = 1
            GROUP BY t.uid, t.name
            SQL,
            ['poolKey' => $poolKey],
        );

        $data = [];
        foreach ($rows as $row) {
            $typeName = (string) $row['type_name'];
            if (!isset(self::TYPE_MAP[$typeName])) {
                continue;
            }

            $data[] = [
                'type' => self::TYPE_MAP[$typeName],
                'name' => $typeName,
                'brands' => (int) $row['brands'],
                'series' => (int) $row['series'],
                'models' => (int) $row['models'],
                'engines' => (int) $row['engines'],
                'stages' => (int) $row['stages'],
            ];
        }

        return $this->json(['data' => ['pool' => $poolKey, 'types' => $data]]);
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
