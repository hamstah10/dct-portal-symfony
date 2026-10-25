<?php

declare(strict_types=1);

namespace App\Service\Ai;

use App\Repository\AiProjectRepository;
use App\Service\Ai\Project\AiProjectContextBuilder;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

/**
 * Sucht im Wissensprojekt „Marken-Wissen“ (interne Dokumente). Der Agent ruft das Werkzeug gezielt auf
 * und nennt die Quelle im Ergebnis.
 */
#[AsTool('knowledge_search', description: 'Search the internal knowledge base (internal documents and notes). Use it before the internet. Returns text passages with their source title.', method: 'search')]
final class KnowledgeSearchTool
{
    private const PROJECT_NAME = 'Marken-Wissen';

    public function __construct(
        private readonly AiProjectRepository $projects,
        private readonly AiProjectContextBuilder $contextBuilder,
    ) {
    }

    /** @return array{found: bool, passages: string} */
    public function search(string $query): array
    {
        $project = $this->projects->findOneBy(['name' => self::PROJECT_NAME]);
        if (null === $project) {
            return ['found' => false, 'passages' => 'Wissensprojekt „Marken-Wissen“ ist nicht angelegt.'];
        }

        $context = $this->contextBuilder->buildContext((int) $project->getId(), $query, 5);

        return ['found' => '' !== $context, 'passages' => $context];
    }
}
