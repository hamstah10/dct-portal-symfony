<?php

namespace App\Command;

use App\Repository\AiProjectResourceRepository;
use App\Service\Ai\Project\AiProjectKnowledgeManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:ai-project:reindex', description: 'Indexiert die Ressourcen der KI-Wissensprojekte neu (Vektoren)')]
final class AiProjectReindexCommand extends Command
{
    public function __construct(
        private readonly AiProjectResourceRepository $resources,
        private readonly AiProjectKnowledgeManager $knowledgeManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('project', null, InputOption::VALUE_REQUIRED, 'Nur Ressourcen dieses Projekts (ID)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectId = $input->getOption('project');
        $failed = 0;

        foreach ($this->resources->findAll() as $resource) {
            if (null !== $projectId && (int) $projectId !== $resource->getProject()?->getId()) {
                continue;
            }

            $this->knowledgeManager->reindexResource($resource);
            $ok = 'indexed' === $resource->getIndexingStatus();
            $failed += $ok ? 0 : 1;
            $io->writeln(sprintf('%d %s: %s %s', $resource->getId(), $resource->getTitle(), $resource->getIndexingStatus(), $resource->getErrorMessage() ?? ''));
        }

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
