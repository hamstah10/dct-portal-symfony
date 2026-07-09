<?php

namespace App\Repository;

use App\Entity\VehicleUsageEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VehicleUsageEvent>
 */
class VehicleUsageEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VehicleUsageEvent::class);
    }

    public function countSince(\DateTimeImmutable $from): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.createdAt >= :from')
            ->setParameter('from', $from)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByEventTypeSince(string $eventType, \DateTimeImmutable $from): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.createdAt >= :from')
            ->andWhere('e.eventType IN (:eventTypes)')
            ->setParameter('from', $from)
            ->setParameter('eventTypes', $this->eventTypeVariants($eventType))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Website (`vdb.*`) and API proxy (`vdb.api.*`) traffic log the same
     * logical event under different prefixes; aggregate both.
     *
     * @return array<int, string>
     */
    private function eventTypeVariants(string $eventType): array
    {
        if (str_starts_with($eventType, 'vdb.api.')) {
            return [$eventType, 'vdb.' . substr($eventType, strlen('vdb.api.'))];
        }

        if (str_starts_with($eventType, 'vdb.')) {
            return [$eventType, 'vdb.api.' . substr($eventType, strlen('vdb.'))];
        }

        return [$eventType];
    }

    public function countUniqueVisitorsSince(\DateTimeImmutable $from): int
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = <<<'SQL'
SELECT COUNT(*) AS total
FROM (
    SELECT COALESCE(NULLIF(session_id, ''), CONCAT('ip:', COALESCE(ip_address, ''), '|ua:', LEFT(COALESCE(user_agent, ''), 120))) AS visitor_key
    FROM vehicle_usage_event
    WHERE created_at >= :fromDate
    GROUP BY visitor_key
) AS visitors
SQL;

        return (int) $connection->fetchOne($sql, [
            'fromDate' => $from->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<int, array{eventType:string,total:int}>
     */
    public function getEventTypeBreakdownSince(\DateTimeImmutable $from, int $limit = 25): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->executeQuery(
            <<<'SQL'
SELECT event_type, COUNT(*) AS total
FROM vehicle_usage_event
WHERE created_at >= :fromDate
GROUP BY event_type
ORDER BY total DESC, event_type ASC
LIMIT :limit
SQL,
            [
                'fromDate' => $from->format('Y-m-d H:i:s'),
                'limit' => $limit,
            ],
            [
                'limit' => ParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'eventType' => (string) ($row['event_type'] ?? ''),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    /**
     * @return array<int, array{engineId:int,engineName:string,total:int}>
     */
    public function getTopEnginesSince(\DateTimeImmutable $from, int $limit = 15): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->executeQuery(
            <<<'SQL'
SELECT
    e.engine_id,
    MAX(COALESCE(ve.name, CONCAT('Motor #', e.engine_id))) AS engine_name,
    COUNT(*) AS total
FROM vehicle_usage_event e
LEFT JOIN tx_chiptuningdatabase_vehicleengine ve ON ve.uid = e.engine_id
WHERE e.created_at >= :fromDate
  AND e.engine_id IS NOT NULL
  AND e.event_type IN (:eventTypes)
GROUP BY e.engine_id
ORDER BY total DESC, engine_name ASC
LIMIT :limit
SQL,
            [
                'fromDate' => $from->format('Y-m-d H:i:s'),
                'eventTypes' => $this->eventTypeVariants('vdb.engine.details'),
                'limit' => $limit,
            ],
            [
                'eventTypes' => ArrayParameterType::STRING,
                'limit' => ParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'engineId' => (int) ($row['engine_id'] ?? 0),
            'engineName' => (string) ($row['engine_name'] ?? '-'),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    /**
     * @return array<int, array{
     *     brandId:int,
     *     brandName:string,
     *     brandImage:?string,
     *     modelId:int,
     *     modelName:string,
     *     engineId:int,
     *     engineName:string,
     *     total:int
     * }>
     */
    public function getTopBrandModelEnginesSince(\DateTimeImmutable $from, int $limit = 15): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->executeQuery(
            <<<'SQL'
SELECT
    e.engine_id,
    COALESCE(e.brand_id, vb.uid) AS brand_id,
    COALESCE(e.model_id, vm.uid) AS model_id,
    MAX(COALESCE(vb_event.name, vb.name, CONCAT('Marke #', COALESCE(e.brand_id, vb.uid)))) AS brand_name,
    MAX(COALESCE(bma_event.file_path, bm_event.image, vb_event.image, bma.file_path, bm.image, vb.image)) AS brand_image,
    MAX(COALESCE(vm_event.name, vm.name, CONCAT('Modell #', COALESCE(e.model_id, vm.uid)))) AS model_name,
    MAX(COALESCE(ve.name, CONCAT('Motor #', e.engine_id))) AS engine_name,
    COUNT(*) AS total
FROM vehicle_usage_event e
LEFT JOIN tx_chiptuningdatabase_vehicleengine ve ON ve.uid = e.engine_id
LEFT JOIN tx_chiptuningdatabase_vehiclemodel vm ON vm.uid = ve.relation_to_vehiclemodel
LEFT JOIN tx_chiptuningdatabase_vehicleseries vs ON vs.uid = vm.relation_to_vehicleseries
LEFT JOIN tx_chiptuningdatabase_vehiclebrand vb ON vb.uid = vs.relation_to_vehiclebrand
LEFT JOIN tx_chiptuningdatabase_brand_master bm ON bm.uid = vb.master_brand
LEFT JOIN media_asset bma ON bma.id = bm.image_media_asset_id
LEFT JOIN tx_chiptuningdatabase_vehiclebrand vb_event ON vb_event.uid = e.brand_id
LEFT JOIN tx_chiptuningdatabase_brand_master bm_event ON bm_event.uid = vb_event.master_brand
LEFT JOIN media_asset bma_event ON bma_event.id = bm_event.image_media_asset_id
LEFT JOIN tx_chiptuningdatabase_vehiclemodel vm_event ON vm_event.uid = e.model_id
WHERE e.created_at >= :fromDate
  AND e.engine_id IS NOT NULL
  AND e.event_type IN (:eventTypes)
  AND COALESCE(e.model_id, vm.uid) IS NOT NULL
  AND COALESCE(e.brand_id, vb.uid) IS NOT NULL
GROUP BY COALESCE(e.brand_id, vb.uid), COALESCE(e.model_id, vm.uid), e.engine_id
ORDER BY total DESC, brand_name ASC, model_name ASC, engine_name ASC
LIMIT :limit
SQL,
            [
                'fromDate' => $from->format('Y-m-d H:i:s'),
                'eventTypes' => $this->eventTypeVariants('vdb.engine.details'),
                'limit' => $limit,
            ],
            [
                'eventTypes' => ArrayParameterType::STRING,
                'limit' => ParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'brandId' => (int) ($row['brand_id'] ?? 0),
            'brandName' => (string) ($row['brand_name'] ?? '-'),
            'brandImage' => self::normalizeAssetPath($row['brand_image'] ?? null),
            'modelId' => (int) ($row['model_id'] ?? 0),
            'modelName' => (string) ($row['model_name'] ?? '-'),
            'engineId' => (int) ($row['engine_id'] ?? 0),
            'engineName' => (string) ($row['engine_name'] ?? '-'),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    /**
     * @return array<int, array{stageId:int,stageName:string,engineName:string,total:int}>
     */
    public function getTopStagesSince(\DateTimeImmutable $from, int $limit = 15): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->executeQuery(
            <<<'SQL'
SELECT
    e.stage_id,
    MAX(COALESCE(vs.name, CONCAT('Stage #', e.stage_id))) AS stage_name,
    MAX(COALESCE(ve.name, ve2.name, '-')) AS engine_name,
    COUNT(*) AS total
FROM vehicle_usage_event e
LEFT JOIN tx_chiptuningdatabase_vehiclestage vs ON vs.uid = e.stage_id
LEFT JOIN tx_chiptuningdatabase_vehicleengine ve ON ve.uid = e.engine_id
LEFT JOIN tx_chiptuningdatabase_vehicleengine ve2 ON ve2.uid = vs.relation_to_vehicleengine
WHERE e.created_at >= :fromDate
  AND e.stage_id IS NOT NULL
  AND e.event_type IN (:eventTypes)
GROUP BY e.stage_id
ORDER BY total DESC, stage_name ASC
LIMIT :limit
SQL,
            [
                'fromDate' => $from->format('Y-m-d H:i:s'),
                'eventTypes' => $this->eventTypeVariants('vdb.client.select_stage'),
                'limit' => $limit,
            ],
            [
                'eventTypes' => ArrayParameterType::STRING,
                'limit' => ParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'stageId' => (int) ($row['stage_id'] ?? 0),
            'stageName' => (string) ($row['stage_name'] ?? '-'),
            'engineName' => (string) ($row['engine_name'] ?? '-'),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    /**
     * @return array<int, array{typeId:int,typeName:string,total:int}>
     */
    public function getTopVehicleTypesSince(\DateTimeImmutable $from, int $limit = 10): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->executeQuery(
            <<<'SQL'
SELECT
    e.vehicle_type_id,
    MAX(COALESCE(vt.name, CONCAT('Typ #', e.vehicle_type_id))) AS type_name,
    COUNT(*) AS total
FROM vehicle_usage_event e
LEFT JOIN tx_chiptuningdatabase_vehicletype vt ON vt.uid = e.vehicle_type_id
WHERE e.created_at >= :fromDate
  AND e.vehicle_type_id IS NOT NULL
  AND e.event_type IN (:eventTypes)
GROUP BY e.vehicle_type_id
ORDER BY total DESC, type_name ASC
LIMIT :limit
SQL,
            [
                'fromDate' => $from->format('Y-m-d H:i:s'),
                'eventTypes' => [
                    ...$this->eventTypeVariants('vdb.client.select_type'),
                    ...$this->eventTypeVariants('vdb.api.brands.list'),
                    ...$this->eventTypeVariants('vdb.brands.list'),
                ],
                'limit' => $limit,
            ],
            [
                'eventTypes' => ArrayParameterType::STRING,
                'limit' => ParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'typeId' => (int) ($row['vehicle_type_id'] ?? 0),
            'typeName' => (string) ($row['type_name'] ?? '-'),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    /**
     * @return array<int, array{brandId:int,brandName:string,brandImage:?string,total:int}>
     */
    public function getTopBrandsSince(\DateTimeImmutable $from, int $limit = 10, ?string $applicationName = null): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $applicationFilterSql = '';
        $params = [
            'fromDate' => $from->format('Y-m-d H:i:s'),
            'eventTypes' => [
                ...$this->eventTypeVariants('vdb.client.select_brand'),
                ...$this->eventTypeVariants('vdb.api.series.list'),
                ...$this->eventTypeVariants('vdb.series.list'),
            ],
            'limit' => $limit,
        ];
        $types = [
            'eventTypes' => ArrayParameterType::STRING,
            'limit' => ParameterType::INTEGER,
        ];

        if (null !== $applicationName && '' !== $applicationName) {
            $applicationFilterSql = "AND COALESCE(NULLIF(e.application_name, ''), 'Website') = :applicationName";
            $params['applicationName'] = $applicationName;
        }

        $rows = $connection->executeQuery(
            <<<SQL
SELECT
    e.brand_id,
    MAX(COALESCE(vb.name, CONCAT('Marke #', e.brand_id))) AS brand_name,
    MAX(COALESCE(bma.file_path, bm.image, vb.image)) AS brand_image,
    COUNT(*) AS total
FROM vehicle_usage_event e
LEFT JOIN tx_chiptuningdatabase_vehiclebrand vb ON vb.uid = e.brand_id
LEFT JOIN tx_chiptuningdatabase_brand_master bm ON bm.uid = vb.master_brand
LEFT JOIN media_asset bma ON bma.id = bm.image_media_asset_id
WHERE e.created_at >= :fromDate
  AND e.brand_id IS NOT NULL
  AND e.event_type IN (:eventTypes)
  {$applicationFilterSql}
GROUP BY e.brand_id
ORDER BY total DESC, brand_name ASC
LIMIT :limit
SQL,
            $params,
            $types
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'brandId' => (int) ($row['brand_id'] ?? 0),
            'brandName' => (string) ($row['brand_name'] ?? '-'),
            'brandImage' => self::normalizeAssetPath($row['brand_image'] ?? null),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    private static function normalizeAssetPath(mixed $path): ?string
    {
        $trimmed = trim((string) ($path ?? ''));
        if ('' === $trimmed) {
            return null;
        }

        if (preg_match('#^https?://#i', $trimmed) === 1 || str_contains($trimmed, '://')) {
            return $trimmed;
        }

        return '/' . ltrim($trimmed, '/');
    }

    /**
     * @return array<int, array{seriesId:int,seriesName:string,total:int}>
     */
    public function getTopSeriesSince(\DateTimeImmutable $from, int $limit = 10): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->executeQuery(
            <<<'SQL'
SELECT
    e.series_id,
    MAX(COALESCE(vs.name, CONCAT('Baureihe #', e.series_id))) AS series_name,
    COUNT(*) AS total
FROM vehicle_usage_event e
LEFT JOIN tx_chiptuningdatabase_vehicleseries vs ON vs.uid = e.series_id
WHERE e.created_at >= :fromDate
  AND e.series_id IS NOT NULL
  AND e.event_type IN (:eventTypes)
GROUP BY e.series_id
ORDER BY total DESC, series_name ASC
LIMIT :limit
SQL,
            [
                'fromDate' => $from->format('Y-m-d H:i:s'),
                'eventTypes' => [
                    ...$this->eventTypeVariants('vdb.client.select_series'),
                    ...$this->eventTypeVariants('vdb.api.models.list'),
                    ...$this->eventTypeVariants('vdb.models.list'),
                ],
                'limit' => $limit,
            ],
            [
                'eventTypes' => ArrayParameterType::STRING,
                'limit' => ParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'seriesId' => (int) ($row['series_id'] ?? 0),
            'seriesName' => (string) ($row['series_name'] ?? '-'),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    /**
     * @return array<int, array{modelId:int,modelName:string,total:int}>
     */
    public function getTopModelsSince(\DateTimeImmutable $from, int $limit = 10): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->executeQuery(
            <<<'SQL'
SELECT
    e.model_id,
    MAX(COALESCE(vm.name, CONCAT('Modell #', e.model_id))) AS model_name,
    COUNT(*) AS total
FROM vehicle_usage_event e
LEFT JOIN tx_chiptuningdatabase_vehiclemodel vm ON vm.uid = e.model_id
WHERE e.created_at >= :fromDate
  AND e.model_id IS NOT NULL
  AND e.event_type IN (:eventTypes)
GROUP BY e.model_id
ORDER BY total DESC, model_name ASC
LIMIT :limit
SQL,
            [
                'fromDate' => $from->format('Y-m-d H:i:s'),
                'eventTypes' => [
                    ...$this->eventTypeVariants('vdb.client.select_model'),
                    ...$this->eventTypeVariants('vdb.api.engines.list'),
                    ...$this->eventTypeVariants('vdb.engines.list'),
                ],
                'limit' => $limit,
            ],
            [
                'eventTypes' => ArrayParameterType::STRING,
                'limit' => ParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'modelId' => (int) ($row['model_id'] ?? 0),
            'modelName' => (string) ($row['model_name'] ?? '-'),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    /**
     * @return array<int, array{date:string,label:string,total:int}>
     */
    public function getDailyCounts(\DateTimeImmutable $fromDate, int $days): array
    {
        $days = max(1, $days);
        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->executeQuery(
            <<<'SQL'
SELECT DATE(created_at) AS day_key, COUNT(*) AS total
FROM vehicle_usage_event
WHERE created_at >= :fromDate
GROUP BY day_key
ORDER BY day_key ASC
SQL,
            [
                'fromDate' => $fromDate->format('Y-m-d H:i:s'),
            ]
        )->fetchAllAssociative();

        $countsByDay = [];
        foreach ($rows as $row) {
            $dayKey = (string) ($row['day_key'] ?? '');
            if ('' === $dayKey) {
                continue;
            }

            $countsByDay[$dayKey] = (int) ($row['total'] ?? 0);
        }

        $result = [];
        $cursor = $fromDate->setTime(0, 0);
        for ($i = 0; $i < $days; ++$i) {
            $key = $cursor->format('Y-m-d');
            $result[] = [
                'date' => $key,
                'label' => $cursor->format('d.m.'),
                'total' => $countsByDay[$key] ?? 0,
            ];

            $cursor = $cursor->modify('+1 day');
        }

        return $result;
    }

    /**
     * @return array<int, array{applicationName:string,total:int}>
     */
    public function getTopApplicationsSince(\DateTimeImmutable $from, int $limit = 15): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $rows = $connection->executeQuery(
            <<<'SQL'
SELECT
    COALESCE(NULLIF(application_name, ''), 'Website') AS application_name,
    COUNT(*) AS total
FROM vehicle_usage_event
WHERE created_at >= :fromDate
GROUP BY application_name
ORDER BY total DESC, application_name ASC
LIMIT :limit
SQL,
            [
                'fromDate' => $from->format('Y-m-d H:i:s'),
                'limit' => $limit,
            ],
            [
                'limit' => ParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'applicationName' => (string) ($row['application_name'] ?? 'Website'),
            'total' => (int) ($row['total'] ?? 0),
        ], $rows);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLatestWithNames(int $limit = 100): array
    {
        $connection = $this->getEntityManager()->getConnection();

        return $connection->executeQuery(
            <<<'SQL'
SELECT
    e.id,
    e.created_at,
    e.event_type,
    e.event_source,
    e.application_name,
    e.route_name,
    e.request_uri,
    e.http_method,
    e.ip_address,
    e.session_id,
    e.vehicle_type_id,
    e.brand_id,
    e.series_id,
    e.model_id,
    e.engine_id,
    e.stage_id,
    COALESCE(vt.name, '-') AS type_name,
    COALESCE(vb.name, '-') AS brand_name,
    COALESCE(vsr.name, '-') AS series_name,
    COALESCE(vm.name, '-') AS model_name,
    COALESCE(ve.name, '-') AS engine_name,
    COALESCE(vs.name, '-') AS stage_name
FROM vehicle_usage_event e
LEFT JOIN tx_chiptuningdatabase_vehicletype vt ON vt.uid = e.vehicle_type_id
LEFT JOIN tx_chiptuningdatabase_vehiclebrand vb ON vb.uid = e.brand_id
LEFT JOIN tx_chiptuningdatabase_vehicleseries vsr ON vsr.uid = e.series_id
LEFT JOIN tx_chiptuningdatabase_vehiclemodel vm ON vm.uid = e.model_id
LEFT JOIN tx_chiptuningdatabase_vehicleengine ve ON ve.uid = e.engine_id
LEFT JOIN tx_chiptuningdatabase_vehiclestage vs ON vs.uid = e.stage_id
ORDER BY e.created_at DESC
LIMIT :limit
SQL,
            ['limit' => $limit],
            ['limit' => ParameterType::INTEGER]
        )->fetchAllAssociative();
    }
}
