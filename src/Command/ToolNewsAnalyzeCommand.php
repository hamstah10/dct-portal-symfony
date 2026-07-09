<?php

namespace App\Command;

use App\Entity\DetectedEcu;
use App\Entity\ToolNewsItem;
use App\Repository\DetectedEcuRepository;
use App\Repository\ToolNewsItemRepository;
use App\Repository\ToolProviderRepository;
use App\Service\ToolNews\EcuDetector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tool-news:analyze',
    description: 'Analysiert Anbieter-News und erkennt darin ECUs, MCUs, Methoden, Protokolle und Fahrzeug-Hinweise.'
)]
final class ToolNewsAnalyzeCommand extends Command
{
    /** @var list<string> */
    private const ANALYZABLE_STATUSES = [
        ToolNewsItem::STATUS_NEW,
        ToolNewsItem::STATUS_IMPORTED,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ToolNewsItemRepository $toolNewsItemRepository,
        private readonly ToolProviderRepository $toolProviderRepository,
        private readonly DetectedEcuRepository $detectedEcuRepository,
        private readonly EcuDetector $ecuDetector,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('news-id', null, InputOption::VALUE_REQUIRED, 'Nur diese eine News-ID analysieren.')
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Nur News dieses Anbieter-Slugs analysieren.')
            ->addOption('only-new', null, InputOption::VALUE_NONE, 'Nur News mit Status "new" oder "imported" analysieren.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Vorhandene erkannte ECUs loeschen und neu analysieren.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $newsIdOption = trim((string) $input->getOption('news-id'));
        $providerOption = trim((string) $input->getOption('provider'));
        $onlyNew = (bool) $input->getOption('only-new');
        $force = (bool) $input->getOption('force');

        $newsItems = $this->resolveNewsItems($newsIdOption, $providerOption, $onlyNew, $io);
        if (null === $newsItems) {
            return Command::INVALID;
        }

        if ([] === $newsItems) {
            $io->info('Keine passenden Anbieter-News gefunden.');

            return Command::SUCCESS;
        }

        $analyzedCount = 0;
        $foundEcuCount = 0;
        $noEcuCount = 0;
        $skippedCount = 0;

        foreach ($newsItems as $newsItem) {
            $existing = $this->detectedEcuRepository->findByNewsItem($newsItem);
            if ([] !== $existing && !$force) {
                ++$skippedCount;
                continue;
            }

            if ([] !== $existing && $force) {
                $this->detectedEcuRepository->removeAllForNewsItem($newsItem);
            }

            $matches = $this->ecuDetector->analyzeNewsItem($newsItem);
            $created = $this->persistMatches($newsItem, $matches);

            ++$analyzedCount;
            $foundEcuCount += $created;

            if ($created > 0) {
                $newsItem->setStatus(ToolNewsItem::STATUS_ANALYZED);
            } else {
                $newsItem->setStatus(ToolNewsItem::STATUS_NO_ECU_FOUND);
                ++$noEcuCount;
            }
            $newsItem->setUpdatedAt(new \DateTimeImmutable());
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Analyse abgeschlossen: %d News analysiert (%d ECUs erkannt, %d ohne ECU-Treffer), %d uebersprungen (bereits analysiert, --force zum Neu-Analysieren).',
            $analyzedCount,
            $foundEcuCount,
            $noEcuCount,
            $skippedCount
        ));

        return Command::SUCCESS;
    }

    /**
     * @return array<int, ToolNewsItem>|null
     */
    private function resolveNewsItems(string $newsIdOption, string $providerOption, bool $onlyNew, SymfonyStyle $io): ?array
    {
        if ('' !== $newsIdOption) {
            if (!ctype_digit($newsIdOption)) {
                $io->error('Bitte eine gueltige numerische --news-id angeben.');

                return null;
            }

            $newsItem = $this->toolNewsItemRepository->find((int) $newsIdOption);
            if (!$newsItem instanceof ToolNewsItem) {
                $io->error(sprintf('Anbieter-News mit ID %s nicht gefunden.', $newsIdOption));

                return null;
            }

            return [$newsItem];
        }

        $criteria = [];

        if ('' !== $providerOption) {
            $provider = $this->toolProviderRepository->findOneBySlug($providerOption);
            if (null === $provider) {
                $io->error(sprintf('Tool-Anbieter mit Slug "%s" nicht gefunden.', $providerOption));

                return null;
            }
            $criteria['provider'] = $provider;
        }

        if ($onlyNew) {
            $criteria['status'] = self::ANALYZABLE_STATUSES;
        }

        return $this->toolNewsItemRepository->findBy($criteria);
    }

    /**
     * @param list<array{
     *     manufacturer: ?string,
     *     ecuFamily: ?string,
     *     ecuName: ?string,
     *     mcu: ?string,
     *     readMethod: ?string,
     *     protocol: ?string,
     *     vehicleHint: ?string,
     *     confidence: float,
     *     rawText: string,
     *     matchData: array<string, mixed>
     * }> $matches
     */
    private function persistMatches(ToolNewsItem $newsItem, array $matches): int
    {
        $seen = [];
        $created = 0;

        foreach ($matches as $match) {
            $dedupKey = $this->buildDedupKey($match);
            if (isset($seen[$dedupKey])) {
                continue;
            }
            $seen[$dedupKey] = true;

            $detectedEcu = new DetectedEcu();
            $detectedEcu->setNewsItem($newsItem);
            $detectedEcu->setProvider($newsItem->getProvider());
            $detectedEcu->setManufacturer($match['manufacturer']);
            $detectedEcu->setEcuFamily($match['ecuFamily']);
            $detectedEcu->setEcuName($match['ecuName']);
            $detectedEcu->setMcu($match['mcu']);
            $detectedEcu->setReadMethod($match['readMethod']);
            $detectedEcu->setProtocol($match['protocol']);
            $detectedEcu->setVehicleHint($match['vehicleHint']);
            $detectedEcu->setConfidence($match['confidence']);
            $detectedEcu->setRawText($match['rawText']);
            $detectedEcu->setMatchData([] !== $match['matchData'] ? $match['matchData'] : null);
            $detectedEcu->setStatus(DetectedEcu::STATUS_NEW);

            $this->entityManager->persist($detectedEcu);
            ++$created;
        }

        return $created;
    }

    /**
     * @param array{ecuName: ?string, ecuFamily: ?string, manufacturer: ?string, mcu: ?string, readMethod: ?string, protocol: ?string} $match
     */
    private function buildDedupKey(array $match): string
    {
        if (null !== $match['ecuName'] && '' !== $match['ecuName']) {
            return implode('|', [
                $match['ecuName'],
                $match['mcu'] ?? '',
                $match['readMethod'] ?? '',
                $match['protocol'] ?? '',
            ]);
        }

        return implode('|', [
            'family',
            $match['ecuFamily'] ?? '',
            $match['manufacturer'] ?? '',
            $match['mcu'] ?? '',
        ]);
    }
}
