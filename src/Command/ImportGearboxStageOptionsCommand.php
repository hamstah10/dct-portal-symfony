<?php

namespace App\Command;

use App\Entity\GearboxStageOption;
use App\Repository\GearboxStageOptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import:gearbox-stage-options',
    description: 'Importiert die DSG-Stage-Optionsmatrix (dsg_daten.json) in tfd_gearbox_stage_option.',
)]
final class ImportGearboxStageOptionsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GearboxStageOptionRepository $repository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Pfad zur dsg_daten.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string) $input->getArgument('file');

        if (!is_file($file)) {
            $io->error(sprintf('Datei nicht gefunden: %s', $file));

            return Command::FAILURE;
        }

        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data) || !is_array($data['gearboxes'] ?? null)) {
            $io->error('Ungueltiges JSON: Schluessel "gearboxes" fehlt.');

            return Command::FAILURE;
        }

        $stageValue = static function (mixed $value): ?string {
            if (true === $value) {
                return '✓';
            }
            if (false === $value || null === $value || '' === $value) {
                return null;
            }

            return (string) $value;
        };

        $created = 0;
        $updated = 0;
        $sorting = 0;

        foreach ($data['gearboxes'] as $gearbox) {
            $gearboxName = trim((string) ($gearbox['name'] ?? ''));
            if ('' === $gearboxName) {
                continue;
            }

            foreach ((array) ($gearbox['categories'] ?? []) as $category) {
                $categoryName = trim((string) ($category['name'] ?? ''));

                foreach ((array) ($category['options'] ?? []) as $option) {
                    $nameEn = trim((string) ($option['nameEn'] ?? ''));
                    if ('' === $nameEn) {
                        continue;
                    }

                    $sorting += 10;
                    $entity = $this->repository->findOneBy([
                        'gearboxName' => $gearboxName,
                        'category' => $categoryName,
                        'nameEn' => $nameEn,
                    ]);

                    if ($entity instanceof GearboxStageOption) {
                        ++$updated;
                    } else {
                        $entity = new GearboxStageOption();
                        $this->entityManager->persist($entity);
                        ++$created;
                    }

                    $stages = (array) ($option['stages'] ?? []);
                    $entity
                        ->setGearboxName($gearboxName)
                        ->setCategory($categoryName)
                        ->setCategoryDescription(trim((string) ($category['description'] ?? '')) ?: null)
                        ->setName(trim((string) ($option['name'] ?? $nameEn)))
                        ->setNameEn($nameEn)
                        ->setFahrbarkeit($stageValue($stages['fahrbarkeit'] ?? null))
                        ->setStage1($stageValue($stages['stage1'] ?? null))
                        ->setStage2($stageValue($stages['stage2'] ?? null))
                        ->setStage2Plus($stageValue($stages['stage2plus'] ?? null))
                        ->setStage3($stageValue($stages['stage3'] ?? null))
                        ->setStage4($stageValue($stages['stage4'] ?? null))
                        ->setSorting($sorting)
                        ->setActive(true);
                }
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d Optionen angelegt, %d aktualisiert.', $created, $updated));

        return Command::SUCCESS;
    }
}
