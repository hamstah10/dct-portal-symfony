<?php

namespace App\Command;

use App\Service\Ai\FirecrawlRunExecutor;
use App\Service\Ai\FirecrawlRunStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ai-firecrawl:run',
    description: 'Fuehrt einen vorbereiteten Firecrawl-Lauf im Hintergrund aus.'
)]
final class FirecrawlRunCommand extends Command
{
    public function __construct(
        private readonly FirecrawlRunStorage $storage,
        private readonly FirecrawlRunExecutor $executor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('run-id', null, InputOption::VALUE_REQUIRED, 'ID des auszufuehrenden Firecrawl-Runs.');
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
        $run = $this->storage->getRun($runId);
        if (!is_array($run)) {
            $io->error(sprintf('Firecrawl-Run mit ID %d nicht gefunden.', $runId));

            return Command::FAILURE;
        }

        $status = (string) ($run['status'] ?? FirecrawlRunStorage::STATUS_QUEUED);
        if (\in_array($status, [FirecrawlRunStorage::STATUS_SUCCESS, FirecrawlRunStorage::STATUS_FAILED], true)) {
            $io->writeln(sprintf('Firecrawl-Run %d ist bereits abgeschlossen (%s).', $runId, $status));

            return FirecrawlRunStorage::STATUS_SUCCESS === $status ? Command::SUCCESS : Command::FAILURE;
        }

        $progressBar = $this->createProgressBar($output, (int) ($run['progressMax'] ?? FirecrawlRunStorage::DEFAULT_PROGRESS_MAX));
        $progressBar->setMessage((string) ($run['progressLabel'] ?? 'Wartet auf Start'));
        $progressBar->start();

        try {
            $this->executor->execute($runId, function (int $current, int $max, string $label) use ($progressBar): void {
                $progressBar->setMaxSteps(max(1, $max));
                $progressBar->setMessage($label);
                $progressBar->setProgress(max(0, min($max, $current)));
            });
        } catch (\Throwable $exception) {
            $progressBar->setMessage('Fehlgeschlagen');
            $progressBar->setProgress((int) ($progressBar->getMaxSteps() ?? 0));
            $progressBar->finish();
            $output->writeln('');
            $io->error(sprintf('Firecrawl-Run %d fehlgeschlagen: %s', $runId, $exception->getMessage()));

            return Command::FAILURE;
        }

        $progressBar->finish();
        $output->writeln('');

        $run = $this->storage->getRun($runId);
        if (is_array($run) && FirecrawlRunStorage::STATUS_SUCCESS === ($run['status'] ?? null)) {
            $io->success(sprintf('Firecrawl-Run %d erfolgreich abgeschlossen.', $runId));

            return Command::SUCCESS;
        }

        $io->error(sprintf(
            'Firecrawl-Run %d fehlgeschlagen: %s',
            $runId,
            is_array($run) ? ($run['error'] ?? 'Unbekannter Fehler') : 'Run nicht mehr verfuegbar'
        ));

        return Command::FAILURE;
    }

    private function createProgressBar(OutputInterface $output, int $maxSteps): ProgressBar
    {
        $progressBar = new ProgressBar($output, max(1, $maxSteps));
        $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%% %message%');
        $progressBar->setBarCharacter('=');
        $progressBar->setEmptyBarCharacter(' ');
        $progressBar->setProgressCharacter('>');

        return $progressBar;
    }
}
