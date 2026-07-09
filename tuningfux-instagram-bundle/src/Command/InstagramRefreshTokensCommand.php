<?php

declare(strict_types=1);

namespace Tuningfux\InstagramBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tuningfux\InstagramBundle\Service\InstagramAuthService;
use Tuningfux\InstagramBundle\Store\InstagramAccountStoreInterface;

#[AsCommand(name: 'tuningfux:instagram:refresh-tokens', description: 'Refreshes stored Instagram long-lived tokens')]
final class InstagramRefreshTokensCommand extends Command
{
    public function __construct(
        private readonly InstagramAccountStoreInterface $store,
        private readonly InstagramAuthService $authService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->store->findAll() as $account) {
            try {
                $this->authService->refreshToken($account);
                $output->writeln(sprintf('<info>OK</info> %s', $account->getUsername()));
            } catch (\Throwable $e) {
                $output->writeln(sprintf('<error>FEHLER</error> %s: %s', $account->getUsername(), $e->getMessage()));
            }
        }

        return Command::SUCCESS;
    }
}
