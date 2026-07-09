<?php

namespace App\Command;

use App\Entity\ToolNewsItem;
use App\Entity\ToolProvider;
use App\Repository\ToolNewsItemRepository;
use App\Repository\ToolProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tool-news:import',
    description: 'Importiert eine vom Tool-News-Scraper erzeugte JSON-Datei nach ToolNewsItem. Ueberspringt bereits bekannte Eintraege anhand von URL/Content-Hash.'
)]
final class ToolNewsImportCommand extends Command
{
    /** @var array<string, ToolProvider> */
    private array $providerCache = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ToolProviderRepository $toolProviderRepository,
        private readonly ToolNewsItemRepository $toolNewsItemRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'Pfad zur JSON-Datei mit den gescrapten News-Items.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $file = (string) $input->getArgument('file');
        if (!is_file($file)) {
            $io->error(sprintf('Datei nicht gefunden: %s', $file));

            return Command::INVALID;
        }

        $raw = file_get_contents($file);
        if (false === $raw) {
            $io->error(sprintf('Datei konnte nicht gelesen werden: %s', $file));

            return Command::FAILURE;
        }

        try {
            $rows = json_decode($raw, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $io->error(sprintf('JSON konnte nicht geparst werden: %s', $exception->getMessage()));

            return Command::FAILURE;
        }

        if (!is_array($rows)) {
            $io->error('JSON-Datei enthaelt keine Liste von News-Items.');

            return Command::FAILURE;
        }

        $created = 0;
        $skippedDuplicate = 0;
        $skippedInvalid = 0;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                ++$skippedInvalid;
                continue;
            }

            $url = trim((string) ($row['url'] ?? ''));
            $title = trim((string) ($row['title'] ?? ''));
            $providerSlug = trim((string) ($row['provider'] ?? ''));

            if ('' === $url || '' === $title || '' === $providerSlug) {
                ++$skippedInvalid;
                continue;
            }

            $contentHash = $this->buildContentHash($row);

            if (null !== $this->toolNewsItemRepository->findOneByUrl($url)) {
                ++$skippedDuplicate;
                continue;
            }

            if (null !== $contentHash && null !== $this->toolNewsItemRepository->findOneByContentHash($contentHash)) {
                ++$skippedDuplicate;
                continue;
            }

            $provider = $this->resolveProvider($providerSlug);

            $item = new ToolNewsItem();
            $item->setProvider($provider);
            $item->setTitle($title);
            $item->setUrl($url);
            $item->setExcerpt($this->nullableString($row['excerpt'] ?? null));
            $item->setContent($this->nullableString($row['content'] ?? null));
            $item->setImageUrl($this->nullableString($row['image_url'] ?? null));
            $item->setPublishedAt($this->parseDate($row['published_at'] ?? null));
            $item->setContentHash($contentHash);
            $item->setStatus(ToolNewsItem::STATUS_NEW);
            $item->setRawData($row);

            $this->entityManager->persist($item);
            ++$created;
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Import abgeschlossen: %d neu, %d Duplikate uebersprungen, %d ungueltig uebersprungen.',
            $created,
            $skippedDuplicate,
            $skippedInvalid
        ));

        return Command::SUCCESS;
    }

    private function resolveProvider(string $slug): ToolProvider
    {
        if (isset($this->providerCache[$slug])) {
            return $this->providerCache[$slug];
        }

        $provider = $this->toolProviderRepository->findOneBySlug($slug);
        if (null === $provider) {
            $provider = new ToolProvider();
            $provider->setSlug($slug);
            $provider->setName(ucfirst($slug));
            $this->entityManager->persist($provider);
        }

        $this->providerCache[$slug] = $provider;

        return $provider;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function buildContentHash(array $row): ?string
    {
        $content = trim((string) ($row['content'] ?? ''));
        if ('' === $content) {
            return null;
        }

        return hash('sha256', $content);
    }

    private function nullableString(mixed $value): ?string
    {
        $stringValue = trim((string) $value);

        return '' !== $stringValue ? $stringValue : null;
    }

    private function parseDate(mixed $value): ?\DateTimeImmutable
    {
        $stringValue = trim((string) $value);
        if ('' === $stringValue) {
            return null;
        }

        try {
            return new \DateTimeImmutable($stringValue);
        } catch (\Exception) {
            return null;
        }
    }
}
