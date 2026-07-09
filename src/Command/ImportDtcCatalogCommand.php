<?php

namespace App\Command;

use App\Entity\DtcCategory;
use App\Entity\DtcCode;
use App\Repository\DtcCategoryRepository;
use App\Repository\DtcCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:dtc:import',
    description: 'Import DTC categories and codes from JSON.',
)]
class ImportDtcCatalogCommand extends Command
{
    public function __construct(
        private readonly DtcCategoryRepository $categoryRepository,
        private readonly DtcCodeRepository $codeRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'Path to JSON file')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate and parse without persisting');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = (string) ($input->getArgument('file') ?? '');
        if ('' === $file) {
            $file = $this->projectDir . '/data/dtc_catalog.json';
        }

        if (!is_file($file)) {
            $output->writeln(sprintf('<error>Datei nicht gefunden: %s</error>', $file));

            return Command::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($file), true);
        if (!is_array($payload)) {
            $output->writeln('<error>JSON konnte nicht gelesen werden.</error>');

            return Command::FAILURE;
        }

        $categoryRows = $this->loadTableRows($payload, 'tx_tuningdtccodes_domain_model_category');
        $codeRows = $this->loadTableRows($payload, 'tx_tuningdtccodes_domain_model_dtccode');

        if ([] === $categoryRows || [] === $codeRows) {
            $output->writeln('<error>JSON enthaelt keine erwarteten Tabellen.</error>');

            return Command::FAILURE;
        }

        $dryRun = (bool) $input->getOption('dry-run');

        $connection = $this->entityManager->getConnection();
        if (!$dryRun) {
            $connection->beginTransaction();
        }

        try {
            $categoryStats = $this->importCategories($categoryRows, $dryRun);
            $codeStats = $this->importCodes($codeRows, $dryRun);

            if (!$dryRun) {
                $connection->commit();
            }

            $output->writeln(sprintf(
                'Kategorien: %d neu, %d aktualisiert',
                $categoryStats['created'],
                $categoryStats['updated']
            ));
            $output->writeln(sprintf(
                'Codes: %d neu, %d aktualisiert',
                $codeStats['created'],
                $codeStats['updated']
            ));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            if (!$dryRun && $connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @param array<int, array<string, mixed>> $payload
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadTableRows(array $payload, string $tableName): array
    {
        foreach ($payload as $table) {
            if (!is_array($table)) {
                continue;
            }

            if (($table['name'] ?? null) === $tableName && isset($table['data']) && is_array($table['data'])) {
                return $table['data'];
            }
        }

        return [];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array{created: int, updated: int}
     */
    private function importCategories(array $rows, bool $dryRun): array
    {
        $created = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $uid = (int) ($row['uid'] ?? 0);
            if (0 === $uid) {
                continue;
            }

            $category = $this->categoryRepository->find($uid);
            if (!$category instanceof DtcCategory) {
                $category = new DtcCategory();
                $category->setUid($uid);
                $this->entityManager->persist($category);
                ++$created;
            } else {
                ++$updated;
            }

            $category
                ->setPid((int) ($row['pid'] ?? 0))
                ->setTstamp((int) ($row['tstamp'] ?? 0))
                ->setCrdate((int) ($row['crdate'] ?? 0))
                ->setDeleted((bool) ((int) ($row['deleted'] ?? 0)))
                ->setHidden((bool) ((int) ($row['hidden'] ?? 0)))
                ->setName((string) ($row['name'] ?? ''))
                ->setBuchstabe((string) ($row['buchstabe'] ?? ''))
                ->setBeschreibung(array_key_exists('beschreibung', $row) ? (string) $row['beschreibung'] : null);
        }

        if (!$dryRun) {
            $this->entityManager->flush();
        } else {
            $this->entityManager->clear();
        }

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array{created: int, updated: int}
     */
    private function importCodes(array $rows, bool $dryRun): array
    {
        $created = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $uid = (int) ($row['uid'] ?? 0);
            if (0 === $uid) {
                continue;
            }

            $categoryId = (int) ($row['category'] ?? 0);
            $category = $categoryId > 0 ? $this->categoryRepository->find($categoryId) : null;
            if (!$category instanceof DtcCategory) {
                continue;
            }

            $code = $this->codeRepository->find($uid);
            if (!$code instanceof DtcCode) {
                $code = new DtcCode();
                $code->setUid($uid);
                $this->entityManager->persist($code);
                ++$created;
            } else {
                ++$updated;
            }

            $code
                ->setPid((int) ($row['pid'] ?? 0))
                ->setTstamp((int) ($row['tstamp'] ?? 0))
                ->setCrdate((int) ($row['crdate'] ?? 0))
                ->setDeleted((bool) ((int) ($row['deleted'] ?? 0)))
                ->setHidden((bool) ((int) ($row['hidden'] ?? 0)))
                ->setFehlercode((string) ($row['fehlercode'] ?? ''))
                ->setBeschreibung(array_key_exists('beschreibung', $row) ? (string) $row['beschreibung'] : null)
                ->setCategory($category);
        }

        if (!$dryRun) {
            $this->entityManager->flush();
        } else {
            $this->entityManager->clear();
        }

        return ['created' => $created, 'updated' => $updated];
    }
}
