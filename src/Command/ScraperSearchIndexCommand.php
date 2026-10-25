<?php

declare(strict_types=1);

namespace App\Command;

use Meilisearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Baut den Meilisearch-Index „scraper_engines“ aus den JSON-Dateien des Scrapers.
 * Ein Dokument je Motor (mit Marke, Modell, Version, Stage-Namen und Stage-Werten).
 */
#[AsCommand(name: 'app:scraper:search-index', description: 'Build the Meilisearch index scraper_engines from scraper JSON files')]
final class ScraperSearchIndexCommand extends Command
{
    private const INDEX = 'scraper_engines';
    private const BATCH_SIZE = 1000;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        #[Autowire('%kernel.environment%')] private readonly string $environment,
        #[Autowire(env: 'MEILISEARCH_URL')] private readonly string $meiliUrl,
        #[Autowire(env: 'MEILISEARCH_KEY')] private readonly string $meiliKey,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only count documents, do not write to Meilisearch');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Einzelne Olsx-Dateien sind bis ca. 70 MB groß. Jede Datei wird einzeln geladen und sofort abgeschickt.
        ini_set('memory_limit', '2G');

        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $base = $this->projectDir . '/scrapy-remote-scraper/data/output';
        $files = $this->sourceFiles($base);

        if ($dryRun) {
            $total = 0;
            foreach ($files as [$provider, $type, $file]) {
                $total += count($this->documentsFor($provider, $type, $file));
            }
            $io->writeln(sprintf('%d Dokumente aus %d Dateien.', $total, count($files)));

            return Command::SUCCESS;
        }

        $index = $this->indexName();
        $client = new Client($this->meiliUrl, $this->meiliKey);
        $client->createIndex($index, ['primaryKey' => 'id']);
        $client->index($index)->updateSettings([
            'searchableAttributes' => ['brand', 'model', 'version', 'engine', 'fuel_type', 'stage_names', 'search_aliases'],
            'filterableAttributes' => ['provider', 'type', 'brand_slug'],
            'sortableAttributes' => ['brand', 'stage_count'],
        ]);
        // Neu aufbauen: Motoren aus gelöschten Dateien sollen nicht übrig bleiben.
        $client->index($index)->deleteAllDocuments();

        $total = 0;
        foreach ($files as [$provider, $type, $file]) {
            $documents = $this->documentsFor($provider, $type, $file);
            foreach (array_chunk($documents, self::BATCH_SIZE) as $batch) {
                $client->index($index)->addDocuments($batch, 'id');
            }
            $total += count($documents);
            $io->writeln(sprintf('%s/%s: %d Dokumente', $provider, $type, count($documents)));
        }

        $io->success(sprintf('Index %s mit %d Dokumenten geschrieben.', $index, $total));

        return Command::SUCCESS;
    }

    private function indexName(): string
    {
        return ('prod' === $this->environment ? 'prod_' : 'dev_') . self::INDEX;
    }

    /**
     * Alle Quelldateien als [Anbieter, Typ, Datei]. Olsx: je Typ nur die neueste Datei (wie im Scraper-Viewer), Teildateien ausgenommen.
     *
     * @return list<array{0: string, 1: string, 2: string}>
     */
    private function sourceFiles(string $base): array
    {
        $result = [];
        foreach (glob($base . '/olsx_brands_spider/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $type = basename($dir);
            $files = array_values(array_filter(glob($dir . '/olsx_brands_' . $type . '_*.json') ?: [], static fn (string $f): bool => !str_contains(basename($f), '_partial')));
            if ([] === $files) {
                continue;
            }
            usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
            $result[] = ['olsx', $type, $files[0]];
        }

        foreach (glob($base . '/dyno/dyno_chiptuningfiles/*.json') ?: [] as $file) {
            $result[] = ['dyno', 'car', $file];
        }
        foreach (glob($base . '/truck_chiptuningfiles/*', GLOB_ONLYDIR) ?: [] as $dir) {
            foreach (glob($dir . '/*.json') ?: [] as $file) {
                $result[] = ['truck_chiptuningfiles', basename($dir), $file];
            }
        }

        return $result;
    }

    /**
     * Schreibweisen für Hubraum-Angaben: „5.0D“ wird zusätzlich als „50d“ und „5.0 d“ gesucht,
     * weil man „50d“ eintippt und nicht „5.0D“.
     *
     * @param list<string> $texts
     */
    private function aliases(array $texts): string
    {
        $aliases = [];
        foreach ($texts as $text) {
            if (preg_match_all('/(\d)[.,](\d)\s*([a-zA-Z]{1,3})\b/', $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as [, $first, $second, $letters]) {
                    $letters = strtolower($letters);
                    $aliases[] = $first . $second . $letters;
                    $aliases[] = $first . ' ' . $letters;
                    $aliases[] = $first . $second . ' ' . $letters;
                }
            }
        }

        return implode(' ', array_unique($aliases));
    }

    /** @return list<array<string, mixed>> */
    private function documentsFor(string $provider, string $type, string $file): array
    {
        $documents = [];
        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data)) {
            return $documents;
        }

        // Olsx-Dateien sind eine Liste von Marken, Dyno- und Truck-Dateien je eine Marke.
        $brands = array_is_list($data) ? $data : [$data];
        foreach ($brands as $brandFile) {
            if (!is_array($brandFile)) {
                continue;
            }
            $this->appendBrand($documents, $provider, $type, $brandFile, $file);
        }

        return $documents;
    }

    /** @param list<array<string, mixed>> $documents */
    private function appendBrand(array &$documents, string $provider, string $type, array $brandFile, string $file): void
    {
        $brandName = (string) ($brandFile['brand_name'] ?? $brandFile['brand'] ?? $brandFile['brand_slug'] ?? basename($file, '.json'));
        $brandSlug = (string) ($brandFile['brand_slug'] ?? basename($file, '.json'));

        foreach ($brandFile['models'] ?? [] as $model) {
            foreach ($model['versions'] ?? [] as $version) {
                foreach ($version['engines'] ?? [] as $engine) {
                    $stages = [];
                    foreach ($engine['stages'] ?? [] as $stage) {
                        $stages[] = [
                            'name' => (string) ($stage['name'] ?? ''),
                            'power_ps' => $stage['power_ps'] ?? null,
                            'torque_nm' => $stage['torque_nm'] ?? null,
                        ];
                    }

                    $key = implode('|', [$provider, $type, $brandSlug, $model['slug'] ?? '', $version['slug'] ?? '', $engine['slug'] ?? '']);
                    $documents[] = [
                        'id' => md5($key),
                        'provider' => $provider,
                        'type' => $type,
                        'brand' => $brandName,
                        'brand_slug' => $brandSlug,
                        'model' => (string) ($model['display_name'] ?? $model['name'] ?? ''),
                        'version' => (string) ($version['display_name'] ?? $version['name'] ?? ''),
                        'engine' => (string) ($engine['display_name'] ?? $engine['name'] ?? ''),
                        'fuel_type' => (string) ($engine['fuel_type'] ?? ''),
                        'stage_names' => array_values(array_filter(array_column($stages, 'name'), static fn (string $n): bool => '' !== $n)),
                        'stage_count' => count($stages),
                        'search_aliases' => $this->aliases([$engine['display_name'] ?? $engine['name'] ?? '', $version['name'] ?? '', $model['name'] ?? '']),
                        'stages' => $stages,
                        'url' => (string) ($engine['url'] ?? $version['url'] ?? $model['url'] ?? ''),
                    ];
                }
            }
        }
    }
}
