<?php

namespace App\Command;

use App\Entity\EmailKonto;
use App\Repository\EmailKontoRepository;
use App\Service\SupportEmailFetchService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:support-email:fetch',
    description: 'Ruft E-Mails aus allen aktiven Konten ab und speichert sie im Support-Postfach.'
)]
final class SupportEmailFetchCommand extends Command
{
    public function __construct(
        private readonly EmailKontoRepository $emailKontoRepository,
        private readonly SupportEmailFetchService $supportEmailFetchService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'account',
                'a',
                InputOption::VALUE_REQUIRED,
                'Nur ein bestimmtes Konto abrufen (ID).'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_REQUIRED,
                'Maximale Anzahl Nachrichten pro Konto.',
                '100'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $accountOption = (string) $input->getOption('account');
        $accountId = is_numeric($accountOption) ? (int) $accountOption : 0;

        $limitOption = (string) $input->getOption('limit');
        $limit = is_numeric($limitOption) ? (int) $limitOption : 100;
        $limit = max(1, min(500, $limit));

        $accounts = [];
        if ($accountId > 0) {
            $account = $this->emailKontoRepository->find($accountId);
            if (!$account instanceof EmailKonto) {
                $io->error(sprintf('Konto mit ID %d nicht gefunden.', $accountId));

                return Command::FAILURE;
            }

            $accounts[] = $account;
        } else {
            $accounts = $this->emailKontoRepository->findBy(
                ['active' => true, 'fetchEnabled' => true],
                ['sorting' => 'ASC', 'name' => 'ASC']
            );
        }

        if ([] === $accounts) {
            $io->warning('Keine aktiven Konten mit aktiviertem Abruf gefunden.');

            return Command::SUCCESS;
        }

        $io->writeln(sprintf('Abruf startet fuer %d Konto/Konten (Limit: %d pro Konto).', count($accounts), $limit));

        $rows = [];
        $hasErrors = false;
        $sumFetched = 0;
        $sumCreated = 0;
        $sumUpdated = 0;
        $sumFailed = 0;

        foreach ($accounts as $account) {
            if (!$account instanceof EmailKonto) {
                continue;
            }

            $result = $this->supportEmailFetchService->fetchAccount($account, $limit);

            $rows[] = [
                (string) ($result['accountId'] ?? 0),
                (string) ($result['accountLabel'] ?? '-'),
                (string) ($result['fetched'] ?? 0),
                (string) ($result['created'] ?? 0),
                (string) ($result['updated'] ?? 0),
                (string) ($result['failed'] ?? 0),
                (string) ($result['error'] ?? ''),
            ];

            $sumFetched += (int) ($result['fetched'] ?? 0);
            $sumCreated += (int) ($result['created'] ?? 0);
            $sumUpdated += (int) ($result['updated'] ?? 0);
            $sumFailed += (int) ($result['failed'] ?? 0);

            if (!empty($result['error']) || ((int) ($result['failed'] ?? 0) > 0)) {
                $hasErrors = true;
            }
        }

        $io->table(
            ['ID', 'Konto', 'Gelesen', 'Neu', 'Aktualisiert', 'Fehlgeschlagen', 'Fehler'],
            $rows
        );

        $io->writeln(sprintf(
            'Summe: gelesen=%d, neu=%d, aktualisiert=%d, fehlgeschlagen=%d',
            $sumFetched,
            $sumCreated,
            $sumUpdated,
            $sumFailed
        ));

        if ($hasErrors) {
            $io->warning('Abruf mit Fehlern abgeschlossen.');

            return Command::FAILURE;
        }

        $io->success('Abruf erfolgreich abgeschlossen.');

        return Command::SUCCESS;
    }
}
