<?php

namespace App\Command;

use App\Entity\DetectedEcu;
use App\Repository\DetectedEcuRepository;
use App\Repository\ToolProviderRepository;
use App\Service\ToolNews\VehicleMatcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tool-news:match-vehicles',
    description: 'Matcht erkannte ECUs (DetectedEcu) gegen die vorhandene Fahrzeugdatenbank und erzeugt VehicleCoverageMatch-Vorschlaege. Aendert die Fahrzeugdatenbank nie, nur lesend.'
)]
final class ToolNewsMatchVehiclesCommand extends Command
{
    /** @var list<string> */
    private const MATCHABLE_STATUSES = [
        DetectedEcu::STATUS_NEW,
        DetectedEcu::STATUS_REVIEW,
    ];

    public function __construct(
        private readonly DetectedEcuRepository $detectedEcuRepository,
        private readonly ToolProviderRepository $toolProviderRepository,
        private readonly VehicleMatcher $vehicleMatcher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('detected-ecu-id', null, InputOption::VALUE_REQUIRED, 'Nur diese eine erkannte ECU matchen.')
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Nur erkannte ECUs dieses Anbieter-Slugs matchen.')
            ->addOption('only-new', null, InputOption::VALUE_NONE, 'Nur DetectedEcu mit Status "new" oder "review" matchen.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Vorhandene VehicleCoverageMatch-Eintraege fuer die jeweilige DetectedEcu loeschen und neu erzeugen.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $detectedEcuIdOption = trim((string) $input->getOption('detected-ecu-id'));
        $providerOption = trim((string) $input->getOption('provider'));
        $onlyNew = (bool) $input->getOption('only-new');
        $force = (bool) $input->getOption('force');

        $detectedEcus = $this->resolveDetectedEcus($detectedEcuIdOption, $providerOption, $onlyNew, $io);
        if (null === $detectedEcus) {
            return Command::INVALID;
        }

        if ([] === $detectedEcus) {
            $io->info('Keine passenden erkannten ECUs gefunden.');

            return Command::SUCCESS;
        }

        $processedCount = 0;
        $matchedCount = 0;
        $noMatchCount = 0;
        $totalMatches = 0;

        foreach ($detectedEcus as $detectedEcu) {
            $results = $this->vehicleMatcher->matchDetectedEcu($detectedEcu, $force);
            ++$processedCount;
            $totalMatches += count($results);

            if ([] !== $results) {
                ++$matchedCount;
            } else {
                ++$noMatchCount;
            }
        }

        $io->success(sprintf(
            '%d erkannte ECU(s) verarbeitet: %d mit mindestens einem Fahrzeug-Match (%d Matches insgesamt), %d ohne Treffer.',
            $processedCount,
            $matchedCount,
            $totalMatches,
            $noMatchCount
        ));

        return Command::SUCCESS;
    }

    /**
     * @return array<int, DetectedEcu>|null
     */
    private function resolveDetectedEcus(string $detectedEcuIdOption, string $providerOption, bool $onlyNew, SymfonyStyle $io): ?array
    {
        if ('' !== $detectedEcuIdOption) {
            if (!ctype_digit($detectedEcuIdOption)) {
                $io->error('Bitte eine gueltige numerische --detected-ecu-id angeben.');

                return null;
            }

            $detectedEcu = $this->detectedEcuRepository->find((int) $detectedEcuIdOption);
            if (!$detectedEcu instanceof DetectedEcu) {
                $io->error(sprintf('Erkannte ECU mit ID %s nicht gefunden.', $detectedEcuIdOption));

                return null;
            }

            return [$detectedEcu];
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
            $criteria['status'] = self::MATCHABLE_STATUSES;
        }

        return $this->detectedEcuRepository->findBy($criteria);
    }
}
