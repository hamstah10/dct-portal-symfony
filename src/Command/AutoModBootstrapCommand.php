<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Auftrag;
use App\Repository\AuftragRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:automod:make-eligible',
    description: 'Markiert einen bestehenden Auftrag als AutoMod-faehig (autoModEnabled=true, autoModStatus=DECRYPTED).'
)]
final class AutoModBootstrapCommand extends Command
{
    public function __construct(
        private readonly AuftragRepository $auftragRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('auftragId', InputArgument::REQUIRED, 'ID des Auftrags');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $auftragId = (int) $input->getArgument('auftragId');
        $order = $this->auftragRepository->find($auftragId);
        if (!$order instanceof Auftrag) {
            $io->error(sprintf('Auftrag #%d nicht gefunden.', $auftragId));

            return Command::FAILURE;
        }

        $this->auftragRepository->markAutoModReady($order);

        $io->success(sprintf(
            'Auftrag %s ist jetzt AutoMod-faehig. ulid=%s, autoModStatus=%d',
            $order->getOrderNumber(),
            (string) $order->getUlid(),
            (int) $order->getAutoModStatus()
        ));

        return Command::SUCCESS;
    }
}
