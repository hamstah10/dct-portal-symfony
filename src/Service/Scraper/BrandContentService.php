<?php

declare(strict_types=1);

namespace App\Service\Scraper;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Inhalte einer Marke aus drei Quellen: Dyno (Datei), Olsx (aufbereitete Marken-Dateien) und
 * Fahrzeugdatenbank (Standard-Datenpool). Gemeinsam genutzt von der Dyno-Zuordnung und dem Marken-Assistenten.
 */
final class BrandContentService
{
    private const DEFAULT_POOL_KEY = 'tfd_voll';

    private const TABLE_PREFIX = 'tx_chiptuningdatabase_';

    public function __construct(
        private readonly Connection $connection,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    /**
     * @return array{brand: string, dyno: array{models: list<array<string, mixed>>}, olsx: list<array{type: string, models: list<array{name: string, engines: int}>}>, db: list<array{type: string, models: list<array{name: string, engines: int}>}>}
     */
    public function content(string $slug): array
    {
        $path = $this->projectDir.'/scrapy-remote-scraper/data/output/dyno/dyno_chiptuningfiles/'.$slug.'.json';
        if (!is_file($path)) {
            throw new NotFoundHttpException('Keine Dyno-Daten für diese Marke.');
        }

        $dyno = json_decode((string) file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR);
        $brandName = (string) ($dyno['brand_name'] ?? $slug);

        $dynoModels = [];
        foreach ($dyno['models'] ?? [] as $model) {
            $engines = [];
            foreach ($model['versions'] ?? [] as $version) {
                foreach ($version['engines'] ?? [] as $engine) {
                    $engines[] = [
                        'name' => (string) ($engine['display_name'] ?? $engine['name'] ?? ''),
                        'fuel' => $engine['fuel_type'] ?? null,
                    ];
                }
            }

            $dynoModels[] = [
                'name' => (string) ($model['display_name'] ?? $model['name'] ?? ''),
                'engines' => $engines,
            ];
        }

        return [
            'brand' => $brandName,
            'dyno' => ['models' => $dynoModels],
            'olsx' => $this->olsxBrandModels($brandName),
            'db' => $this->dbBrandModels($brandName),
        ];
    }

    /**
     * Olsx-Modelle je Typ, für Marken mit gleichem Namen. Liest die aufbereiteten Marken-Dateien der Scraper-Ansicht.
     *
     * @return list<array{type: string, models: list<array{name: string, engines: int}>}>
     */
    public function olsxBrandModels(string $brandName): array
    {
        $key = $this->normalize($brandName);
        $result = [];
        foreach (glob($this->projectDir.'/scrapy-remote-scraper/data/viewer/*/index.json') ?: [] as $indexPath) {
            $viewerDir = dirname($indexPath);
            $index = json_decode((string) file_get_contents($indexPath), true, 512, \JSON_THROW_ON_ERROR);
            foreach ($index['brands'] ?? [] as $entry) {
                $entryName = (string) ($entry['name'] ?? $entry['slug'] ?? '');
                $brandFile = $viewerDir.'/brands/'.($entry['slug'] ?? '').'.json';
                if ($this->normalize($entryName) !== $key || !is_file($brandFile)) {
                    continue;
                }

                $brand = json_decode((string) file_get_contents($brandFile), true, 512, \JSON_THROW_ON_ERROR);
                $result[] = ['type' => basename($viewerDir), 'models' => $this->summarizeModels($brand['models'] ?? [])];
            }
        }

        return $result;
    }

    /**
     * Datenbank-Modelle je Fahrzeugtyp für Marken mit gleichem Namen im Standard-Datenpool.
     *
     * @return list<array{type: string, models: list<array{name: string, engines: int}>}>
     */
    public function dbBrandModels(string $brandName): array
    {
        $p = self::TABLE_PREFIX;
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
            SELECT t.name AS type_name, m.name AS model_name, COUNT(DISTINCT e.uid) AS engines
            FROM {$p}vehiclebrand b
            JOIN {$p}vehicletype t ON t.uid = b.relation_to_vehicletype
            JOIN {$p}vehicleseries s ON s.relation_to_vehiclebrand = b.uid AND s.active = 1
            JOIN {$p}vehiclemodel m ON m.relation_to_vehicleseries = s.uid AND m.active = 1
            LEFT JOIN {$p}vehicleengine e ON e.relation_to_vehiclemodel = m.uid AND e.active = 1
            WHERE b.active = 1
              AND LOWER(TRIM(b.name)) = :name
              AND b.vehicle_data_pool = (SELECT pool.uid FROM {$p}vehicle_data_pool pool WHERE pool.pool_key = :poolKey)
            GROUP BY t.name, m.uid, m.name
            ORDER BY t.name, m.name
            SQL,
            ['name' => $this->normalize($brandName), 'poolKey' => self::DEFAULT_POOL_KEY],
        );

        $byType = [];
        foreach ($rows as $row) {
            $byType[(string) $row['type_name']][] = ['name' => (string) $row['model_name'], 'engines' => (int) $row['engines']];
        }

        $result = [];
        foreach ($byType as $type => $models) {
            $result[] = ['type' => $type, 'models' => $models];
        }

        return $result;
    }

    /**
     * @param list<array<string, mixed>> $models
     *
     * @return list<array{name: string, engines: int}>
     */
    private function summarizeModels(array $models): array
    {
        $result = [];
        foreach ($models as $model) {
            $engines = 0;
            foreach ($model['versions'] ?? [] as $version) {
                $engines += (int) ($version['engine_count'] ?? count($version['engines'] ?? []));
            }
            $result[] = ['name' => (string) ($model['display_name'] ?? $model['name'] ?? ''), 'engines' => $engines];
        }

        return $result;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $value)));
    }
}
