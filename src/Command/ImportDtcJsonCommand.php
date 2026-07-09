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
    name: 'app:dtc:import-json',
    description: 'Import DTC codes from a simple JSON list.',
)]
class ImportDtcJsonCommand extends Command
{
    /**
     * @var array<string, DtcCategory>
     */
    private array $categoryCache = [];

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
            $file = $this->projectDir . '/var/import/dtc.json';
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

        $dryRun = (bool) $input->getOption('dry-run');
        $now = time();
        $created = 0;
        $updated = 0;
        $skipped = 0;

        $nextCategoryUid = $this->categoryRepository->getNextUid();
        $nextCodeUid = $this->codeRepository->getNextUid();

        $connection = $this->entityManager->getConnection();
        if (!$dryRun) {
            $connection->beginTransaction();
        }

        try {
            foreach ($payload as $row) {
                if (!is_array($row)) {
                    $skipped++;
                    continue;
                }

                $codeValue = trim((string) ($row['fehlercode'] ?? ''));
                if ('' === $codeValue) {
                    $skipped++;
                    continue;
                }

                $descriptionValue = array_key_exists('beschreibung', $row)
                    ? trim((string) $row['beschreibung'])
                    : '';
                $description = '' !== $descriptionValue ? $descriptionValue : null;

                $categoryLabel = trim((string) ($row['category'] ?? ''));
                $category = null;
                if ('' !== $categoryLabel) {
                    $category = $this->resolveCategory($categoryLabel, $nextCategoryUid, $now, $dryRun);
                    if ($category instanceof DtcCategory && $category->getUid() >= $nextCategoryUid) {
                        $nextCategoryUid = $category->getUid() + 1;
                    }
                }

                $dtc = $this->codeRepository->findOneBy(['fehlercode' => $codeValue]);
                if (!$dtc instanceof DtcCode) {
                    $dtc = new DtcCode();
                    $dtc->setUid($nextCodeUid++);
                    $this->entityManager->persist($dtc);
                    $created++;
                } else {
                    $updated++;
                }

                $dtc
                    ->setFehlercode($codeValue)
                    ->setBeschreibung($description)
                    ->setTstamp($now);

                if (0 === $dtc->getCrdate()) {
                    $dtc->setCrdate($now);
                }

                if ($category instanceof DtcCategory) {
                    $dtc->setCategory($category);
                }
            }

            if (!$dryRun) {
                $this->entityManager->flush();
                $connection->commit();
            } else {
                $this->entityManager->clear();
            }
        } catch (\Throwable $e) {
            if (!$dryRun && $connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $e;
        }

        $output->writeln(sprintf('Codes: %d neu, %d aktualisiert, %d uebersprungen', $created, $updated, $skipped));

        return Command::SUCCESS;
    }

    private function resolveCategory(string $label, int $nextUid, int $timestamp, bool $dryRun): ?DtcCategory
    {
        if (isset($this->categoryCache[$label])) {
            return $this->categoryCache[$label];
        }

        $buchstabe = '';
        $name = $label;
        $parts = preg_split('/\\s*[,\\-–:]\\s*/', $label, 2);
        if (is_array($parts) && count($parts) === 2 && '' !== trim((string) $parts[0])) {
            $buchstabe = trim((string) $parts[0]);
            $name = trim((string) $parts[1]);
        }

        $category = null;
        if ('' !== $buchstabe && '' !== $name) {
            $category = $this->categoryRepository->findOneBy([
                'buchstabe' => $buchstabe,
                'name' => $name,
            ]);
        }

        if (!$category instanceof DtcCategory && '' !== $name) {
            $category = $this->categoryRepository->findOneBy(['name' => $name]);
        }

        if (!$category instanceof DtcCategory && '' !== $buchstabe) {
            $category = $this->categoryRepository->findOneBy(['buchstabe' => $buchstabe]);
        }

        if (!$category instanceof DtcCategory) {
            $category = new DtcCategory();
            $category->setUid($nextUid);
            $category->setName($name);
            $category->setBuchstabe($buchstabe);
            $category->setCrdate($timestamp);
            $category->setTstamp($timestamp);
            $this->entityManager->persist($category);
        }

        $this->categoryCache[$label] = $category;

        return $category;
    }
}
