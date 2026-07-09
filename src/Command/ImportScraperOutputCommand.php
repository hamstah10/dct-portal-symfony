<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ScraperImportLog;
use App\Entity\VehicleDataPool;
use App\Entity\VehicleType;
use App\Repository\VehicleDataPoolRepository;
use App\Service\OlsxVehicleImporter;
use App\Service\ScraperImportAnalyzer;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:scraper:import-output',
    description: 'Importiert JSON-Dateien aus scrapy-remote-scraper/data/output/<spider>/<type> oder Legacy data/output/<type> in einen aktiven Datenpool.',
)]
final class ImportScraperOutputCommand extends Command
{
    private const OUTPUT_BASE_DIR = '/scrapy-remote-scraper/data/output';

    public function __construct(
        private readonly ScraperImportAnalyzer $analyzer,
        private readonly VehicleDataPoolRepository $poolRepository,
        private readonly OlsxVehicleImporter $importer,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('pool', InputArgument::REQUIRED, 'Pool-Name oder Pool-Key (z. B. "Voll").')
            ->addArgument('type', InputArgument::REQUIRED, 'Output-Typ (z. B. car, truck, moto, boat).')
            ->addOption('spider', null, InputOption::VALUE_REQUIRED, 'Optionaler Spider-Ordner unter data/output/<spider>/<type>.')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Optionaler Dateiname zum Fortsetzen (inklusive), z. B. dodge.json.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $poolInput = trim((string) $input->getArgument('pool'));
        $typeInput = strtolower(trim((string) $input->getArgument('type')));
        $spiderInput = trim((string) ($input->getOption('spider') ?? ''));
        $fromFile = trim((string) ($input->getOption('from') ?? ''));
        if ('' === $poolInput || '' === $typeInput) {
            $io->error('Pool und Typ sind Pflichtparameter.');

            return Command::INVALID;
        }

        $pool = $this->resolvePool($poolInput);
        if (!$pool instanceof VehicleDataPool) {
            $io->error(sprintf('Aktiver Datenpool "%s" wurde nicht gefunden.', $poolInput));

            return Command::FAILURE;
        }

        $typeDirs = [];
        if ('' !== $spiderInput) {
            $typeDirs[] = sprintf('%s%s/%s/%s', $this->projectDir, self::OUTPUT_BASE_DIR, $spiderInput, $typeInput);
        } else {
            foreach (glob(sprintf('%s%s/*/%s', $this->projectDir, self::OUTPUT_BASE_DIR, $typeInput), GLOB_ONLYDIR) ?: [] as $spiderTypeDir) {
                $typeDirs[] = $spiderTypeDir;
            }
            $typeDirs[] = sprintf('%s%s/%s', $this->projectDir, self::OUTPUT_BASE_DIR, $typeInput);
        }

        $jsonFiles = [];
        foreach ($typeDirs as $typeDir) {
            $jsonFiles = array_merge($jsonFiles, $this->analyzer->listJsonFilesRecursivelyFromDirectory($typeDir));
        }
        usort($jsonFiles, static fn (array $left, array $right): int => strcmp((string) $left['name'], (string) $right['name']));
        if ('' !== $fromFile) {
            $startIndex = null;
            foreach ($jsonFiles as $index => $file) {
                if (($file['name'] ?? '') === $fromFile) {
                    $startIndex = $index;
                    break;
                }
            }

            if (null === $startIndex) {
                $io->error(sprintf('Start-Datei "%s" wurde nicht gefunden.', $fromFile));

                return Command::FAILURE;
            }

            $jsonFiles = array_slice($jsonFiles, $startIndex);
        }

        if ([] === $jsonFiles) {
            $io->warning(sprintf('Keine JSON-Dateien gefunden unter %s', implode(', ', $typeDirs)));

            return Command::SUCCESS;
        }

        $io->section(sprintf(
            'Starte Import: %d Datei(en) aus "%s" in Pool "%s" (#%d)',
            count($jsonFiles),
            $typeInput,
            $pool->getName(),
            $pool->getId() ?? 0
        ));

        $totals = [
            'files' => 0,
            'failed' => 0,
            'brands' => 0,
            'models' => 0,
            'engines' => 0,
            'created' => 0,
            'updated' => 0,
            'unchanged' => 0,
        ];

        foreach ($jsonFiles as $file) {
            $filePath = (string) $file['path'];
            $fileName = (string) $file['name'];
            $io->text(sprintf('→ %s', $fileName));

            try {
                $poolRef = $this->resolvePool($poolInput);
                if (!$poolRef instanceof VehicleDataPool) {
                    throw new \RuntimeException('Aktiver Datenpool nicht mehr verfügbar.');
                }

                $analysis = $this->analyzer->analyze($filePath);
                $payload = $this->decodePayload($filePath);
                $vehicleType = $this->importer->resolveVehicleTypeFromFile($filePath);
                if (!$vehicleType instanceof VehicleType) {
                    throw new \RuntimeException('Fahrzeugtyp konnte nicht bestimmt werden.');
                }

                $stats = $this->importer->import(
                    $payload,
                    $poolRef,
                    $vehicleType,
                    $this->importer->detectScraperSourceFromFile($filePath)
                );
                $this->entityManager->flush();

                $summaryJson = null;
                try {
                    $summaryJson = json_encode($analysis['brands'], \JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                }

                $importLog = (new ScraperImportLog())
                    ->setVehicleDataPool($poolRef)
                    ->setFileName($fileName)
                    ->setFilePath($filePath)
                    ->setSource(ScraperImportLog::SOURCE_OUTPUT)
                    ->setType($analysis['type'] ?? $typeInput)
                    ->setBrandCount((int) ($analysis['brandCount'] ?? 0))
                    ->setBrandSummary($summaryJson)
                    ->setStatus('imported')
                    ->setMessage(sprintf(
                        'Batch-Import (%s Models, %s Engines, neu: %s, aktualisiert: %s, unverändert: %s).',
                        $stats['Models'] ?? 0,
                        $stats['Engines'] ?? 0,
                        $stats['Created'] ?? 0,
                        $stats['Updated'] ?? 0,
                        $stats['Unchanged'] ?? 0
                    ));

                $this->entityManager->persist($importLog);
                $this->entityManager->flush();
                $this->entityManager->clear();

                ++$totals['files'];
                $totals['brands'] += (int) ($stats['Brands'] ?? 0);
                $totals['models'] += (int) ($stats['Models'] ?? 0);
                $totals['engines'] += (int) ($stats['Engines'] ?? 0);
                $totals['created'] += (int) ($stats['Created'] ?? 0);
                $totals['updated'] += (int) ($stats['Updated'] ?? 0);
                $totals['unchanged'] += (int) ($stats['Unchanged'] ?? 0);
            } catch (\Throwable $exception) {
                $this->entityManager->clear();
                ++$totals['failed'];
                $io->warning(sprintf('Fehler bei %s: %s', $fileName, $exception->getMessage()));
            }
        }

        $io->newLine();
        $io->table(
            ['Dateien', 'Fehler', 'Brands', 'Models', 'Engines', 'Created', 'Updated', 'Unchanged'],
            [[
                $totals['files'],
                $totals['failed'],
                $totals['brands'],
                $totals['models'],
                $totals['engines'],
                $totals['created'],
                $totals['updated'],
                $totals['unchanged'],
            ]]
        );

        if ($totals['failed'] > 0) {
            $io->warning('Import abgeschlossen mit Fehlern.');

            return Command::FAILURE;
        }

        $io->success('Import vollständig erfolgreich abgeschlossen.');

        return Command::SUCCESS;
    }

    private function resolvePool(string $poolInput): ?VehicleDataPool
    {
        $normalized = mb_strtolower(trim($poolInput));
        if ('' === $normalized) {
            return null;
        }

        $pools = $this->poolRepository->findBy(['active' => true]);
        foreach ($pools as $pool) {
            if (!$pool instanceof VehicleDataPool) {
                continue;
            }

            if (mb_strtolower($pool->getName()) === $normalized || mb_strtolower($pool->getPoolKey()) === $normalized) {
                return $pool;
            }
        }

        return null;
    }

    /**
     * @return array<int, mixed>
     *
     * @throws JsonException
     */
    private function decodePayload(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if (false === $content || '' === $content) {
            throw new \RuntimeException('Datei konnte nicht gelesen werden.');
        }

        $payload = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        if (!is_array($payload) || [] === $payload) {
            throw new \RuntimeException('Ungültiger oder leerer JSON-Inhalt.');
        }

        if ($this->looksLikeSingleBrandPayload($payload)) {
            return [$payload];
        }

        return array_values($payload);
    }

    /**
     * @param array<mixed> $payload
     */
    private function looksLikeSingleBrandPayload(array $payload): bool
    {
        return array_key_exists('brand_slug', $payload)
            || array_key_exists('brand_name', $payload)
            || array_key_exists('models', $payload);
    }
}
