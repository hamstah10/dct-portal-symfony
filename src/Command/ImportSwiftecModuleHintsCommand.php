<?php

namespace App\Command;

use App\Entity\EcuOptionHint;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vehicle-data:import-swiftec-module-hints',
    description: 'Importiert ECU-Optionen Hinweise aus einer Swiftec-Module-JSON-Datei.',
)]
final class ImportSwiftecModuleHintsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Pfad zur Swiftec-JSON-Datei');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = trim((string) $input->getArgument('file'));
        if ('' === $file || !is_file($file)) {
            $output->writeln(sprintf('<error>Datei nicht gefunden: %s</error>', $file));

            return Command::FAILURE;
        }

        $decoded = json_decode((string) file_get_contents($file), true);
        if (!is_array($decoded)) {
            $output->writeln('<error>JSON konnte nicht gelesen werden.</error>');

            return Command::FAILURE;
        }

        $repository = $this->entityManager->getRepository(EcuOptionHint::class);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $processed = 0;
        $hintCache = [];

        foreach ($decoded as $moduleRow) {
            if (!is_array($moduleRow)) {
                continue;
            }

            $moduleName = $this->stringValue($moduleRow['module_name'] ?? null);
            $moduleSlug = $this->nullableString($moduleRow['module_slug'] ?? null);
            $moduleUrl = $this->nullableString($moduleRow['module_url'] ?? null);
            $scrapedAt = $this->parseDateTime($moduleRow['scraped_at'] ?? null);

            foreach ($this->toList($moduleRow['brands'] ?? null) as $brandRow) {
                if (!is_array($brandRow)) {
                    continue;
                }

                $brandName = $this->stringValue($brandRow['brand_name'] ?? null);
                $brandSlug = $this->nullableString($brandRow['brand_slug'] ?? null);

                foreach ($this->toList($brandRow['controllers'] ?? null) as $controllerRow) {
                    if (!is_array($controllerRow)) {
                        continue;
                    }

                    $notes = $this->nullableString($controllerRow['notes'] ?? null);
                    $notesDe = $this->nullableString($controllerRow['notes_de'] ?? null);
                    if (null === $notes && null === $notesDe) {
                        ++$skipped;
                        continue;
                    }

                    $manufacturer = $this->stringValue($controllerRow['manufacturer'] ?? null);
                    $model = $this->stringValue($controllerRow['model'] ?? null);
                    $raw = $this->nullableString($controllerRow['raw'] ?? null);
                    $signatureHash = hash('sha256', implode('|', [
                        mb_strtolower($moduleSlug ?? $moduleName),
                        mb_strtolower($brandSlug ?? $brandName),
                        mb_strtolower($manufacturer),
                        mb_strtolower($model),
                        $notes ?? '',
                        $notesDe ?? '',
                    ]));

                    $hint = $hintCache[$signatureHash] ?? null;
                    if (!$hint instanceof EcuOptionHint) {
                        $hint = $repository->findOneBy(['signatureHash' => $signatureHash]);
                    }
                    $isNew = false;
                    if (!$hint instanceof EcuOptionHint) {
                        $hint = new EcuOptionHint();
                        $this->entityManager->persist($hint);
                        $isNew = true;
                    }

                    $hint
                        ->setModuleName($moduleName)
                        ->setModuleSlug($moduleSlug)
                        ->setModuleUrl($moduleUrl)
                        ->setBrandName($brandName)
                        ->setBrandSlug($brandSlug)
                        ->setManufacturer($manufacturer)
                        ->setModel($model)
                        ->setNotes($notes)
                        ->setNotesDe($notesDe)
                        ->setRaw($raw)
                        ->setSourceFile($file)
                        ->setScrapedAt($scrapedAt)
                        ->setSignatureHash($signatureHash)
                        ->setActive(true);

                    $hintCache[$signatureHash] = $hint;
                    ++$processed;
                    if ($isNew) {
                        ++$created;
                    } else {
                        ++$updated;
                    }
                }
            }
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('Verarbeitet: %d', $processed));
        $output->writeln(sprintf('Neu: %d', $created));
        $output->writeln(sprintf('Aktualisiert: %d', $updated));
        $output->writeln(sprintf('Ohne Hinweis uebersprungen: %d', $skipped));

        return Command::SUCCESS;
    }

    /**
     * @return array<int, mixed>
     */
    private function toList(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }

    private function stringValue(mixed $value): string
    {
        $resolved = trim((string) $value);

        return '' !== $resolved ? $resolved : '-';
    }

    private function nullableString(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $resolved = trim((string) $value);

        return '' !== $resolved ? $resolved : null;
    }

    private function parseDateTime(mixed $value): ?\DateTimeImmutable
    {
        $resolved = $this->nullableString($value);
        if (null === $resolved) {
            return null;
        }

        try {
            return new \DateTimeImmutable($resolved);
        } catch (\Throwable) {
            return null;
        }
    }
}
