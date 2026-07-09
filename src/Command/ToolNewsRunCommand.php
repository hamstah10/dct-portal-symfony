<?php

namespace App\Command;

use App\Repository\ToolNewsSourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:tool-news:run',
    description: 'Fuehrt den Tool-News-Scraper fuer einen Anbieter aus (autotuner, magicmotorsport oder alientech).'
)]
final class ToolNewsRunCommand extends Command
{
    /** @var array<string, string> */
    private const SPIDER_MAP = [
        'autotuner' => 'autotuner_news',
        'magicmotorsport' => 'magicmotorsport_news',
        'alientech' => 'alientech_news',
    ];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        private readonly EntityManagerInterface $entityManager,
        private readonly ToolNewsSourceRepository $toolNewsSourceRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('provider', InputArgument::REQUIRED, 'Anbieter-Slug: autotuner, magicmotorsport oder alientech.')
            ->addOption('year', null, InputOption::VALUE_REQUIRED, 'Nur News aus diesem Jahr scrapen (z.B. 2026).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $provider = strtolower(trim((string) $input->getArgument('provider')));
        $spider = self::SPIDER_MAP[$provider] ?? null;
        if (null === $spider) {
            $io->error(sprintf(
                'Unbekannter Anbieter "%s". Erlaubt: %s',
                $provider,
                implode(', ', array_keys(self::SPIDER_MAP))
            ));

            return Command::INVALID;
        }

        $scraperDir = $this->projectDir . '/scrapy-remote-scraper';
        if (!is_dir($scraperDir)) {
            $io->error(sprintf('Scraper-Verzeichnis nicht gefunden: %s', $scraperDir));

            return Command::FAILURE;
        }

        $outputDir = $scraperDir . '/output/tool_news';
        if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
            $io->error(sprintf('Output-Verzeichnis konnte nicht angelegt werden: %s', $outputDir));

            return Command::FAILURE;
        }

        $outputFile = sprintf('output/tool_news/%s_latest.json', $spider);

        $command = ['.venv/bin/scrapy', 'crawl', $spider, '-O', $outputFile];
        $year = trim((string) $input->getOption('year'));
        if ('' !== $year) {
            $command[] = '-a';
            $command[] = sprintf('year=%s', $year);
        }
        $io->writeln(sprintf('Starte: %s (in %s)', implode(' ', $command), $scraperDir));

        $process = new Process($command, $scraperDir);
        $process->setTimeout(600);
        $process->setIdleTimeout(300);

        $source = $this->toolNewsSourceRepository->findOneBySpiderName($spider);
        $source?->setLastRunAt(new \DateTimeImmutable());

        try {
            $process->mustRun();
        } catch (ProcessFailedException) {
            $errorOutput = $process->getErrorOutput();
            $source?->setLastError('' !== $errorOutput ? mb_substr($errorOutput, -2000) : sprintf('Exit-Code %d', (int) $process->getExitCode()));
            if (null !== $source) {
                $this->entityManager->flush();
            }

            $io->error(sprintf(
                'Scraper-Lauf fuer "%s" fehlgeschlagen (Exit-Code %d).%s',
                $provider,
                (int) $process->getExitCode(),
                '' !== $errorOutput ? "\n" . $errorOutput : ''
            ));

            return Command::FAILURE;
        }

        if (null !== $source) {
            $source->setLastSuccessAt(new \DateTimeImmutable());
            $source->setLastError(null);
            $this->entityManager->flush();
        }

        $io->success(sprintf(
            'Tool-News-Scraper "%s" erfolgreich ausgefuehrt. Output: %s/%s',
            $spider,
            $scraperDir,
            $outputFile
        ));

        return Command::SUCCESS;
    }
}
