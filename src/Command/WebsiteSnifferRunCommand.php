<?php

namespace App\Command;

use App\Entity\WebsiteSnifferRun;
use App\Repository\WebsiteSnifferRunRepository;
use App\Service\WebsiteSnifferService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:website-sniffer:run',
    description: 'Fuehrt einen vorbereiteten Website-Sniffer-Lauf im Hintergrund aus.'
)]
final class WebsiteSnifferRunCommand extends Command
{
    public function __construct(
        private readonly WebsiteSnifferRunRepository $websiteSnifferRunRepository,
        private readonly WebsiteSnifferService $websiteSnifferService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('run-id', null, InputOption::VALUE_REQUIRED, 'ID des auszufuehrenden Sniffer-Laufs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $runIdOption = (string) $input->getOption('run-id');
        if (!ctype_digit($runIdOption) || (int) $runIdOption <= 0) {
            $io->error('Bitte eine gueltige --run-id angeben.');

            return Command::INVALID;
        }

        $runId = (int) $runIdOption;
        $run = $this->websiteSnifferRunRepository->find($runId);
        if (!$run instanceof WebsiteSnifferRun) {
            $io->error(sprintf('Sniffer-Lauf mit ID %d nicht gefunden.', $runId));

            return Command::FAILURE;
        }

        if (null !== $run->getFinishedAt() && WebsiteSnifferRun::STATUS_RUNNING !== $run->getStatus()) {
            $io->writeln(sprintf('Sniffer-Lauf %d ist bereits abgeschlossen (%s).', $runId, $run->getStatus()));

            return Command::SUCCESS;
        }

        $run = $this->websiteSnifferService->executeRun($run);

        if (WebsiteSnifferRun::STATUS_SUCCESS === $run->getStatus()) {
            $io->success(sprintf('Sniffer-Lauf %d erfolgreich (%d Seiten).', $runId, $run->getPagesCount()));

            return Command::SUCCESS;
        }

        $io->error(sprintf('Sniffer-Lauf %d fehlgeschlagen: %s', $runId, $run->getErrorMessage() ?? 'Unbekannter Fehler'));

        return Command::FAILURE;
    }
}
