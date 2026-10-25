<?php

declare(strict_types=1);

namespace App\Service\Ai;

use App\Service\Scraper\BrandContentService;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Eigene Fahrzeugdaten zu einer Marke: zugeordneter Fahrzeugtyp (Dyno-Zuordnung), Modellanzahl und
 * Modellnamen je Quelle (Dyno, Olsx, Datenbank).
 */
#[AsTool('brand_data', description: 'Look up a vehicle brand in our internal data: assigned vehicle type, and model counts and model names per source (Dyno, Olsx, database). Use it for questions about our own data.', method: 'lookup')]
final class BrandDataTool
{
    private const MAX_MODELS = 60;

    public function __construct(
        private readonly BrandContentService $content,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    /** @return array<string, mixed> */
    public function lookup(string $brand): array
    {
        $needle = mb_strtolower(trim($brand));
        $entry = null;
        foreach ($this->mapEntries() as $candidate) {
            if (mb_strtolower((string) $candidate['brand_name']) === $needle || mb_strtolower((string) $candidate['brand_slug']) === $needle) {
                $entry = $candidate;
                break;
            }
        }

        if (null === $entry) {
            return ['found' => false, 'message' => sprintf('Marke "%s" ist in der Dyno-Zuordnung nicht enthalten.', $brand)];
        }

        $data = $this->content->content((string) $entry['brand_slug']);

        return [
            'found' => true,
            'brand' => $data['brand'],
            'assigned_type' => $entry['type'] ?? 'offen',
            'assignment_source' => $entry['source'],
            'dyno' => $this->summary($data['dyno']['models']),
            'olsx' => array_map(fn (array $g): array => ['type' => $g['type']] + $this->summary($g['models']), $data['olsx']),
            'database' => array_map(fn (array $g): array => ['type' => $g['type']] + $this->summary($g['models']), $data['db']),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function mapEntries(): array
    {
        $path = $this->projectDir.'/scrapy-remote-scraper/data/dyno_type_map.json';
        if (!is_file($path)) {
            return [];
        }

        return json_decode((string) file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR)['brands'] ?? [];
    }

    /**
     * @param list<array{name: string}> $models
     *
     * @return array{model_count: int, models: list<string>, truncated: bool}
     */
    private function summary(array $models): array
    {
        $names = array_values(array_unique(array_map(static fn (array $m): string => (string) $m['name'], $models)));

        return [
            'model_count' => \count($models),
            'models' => \array_slice($names, 0, self::MAX_MODELS),
            'truncated' => \count($names) > self::MAX_MODELS,
        ];
    }
}
