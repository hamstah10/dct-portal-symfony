<?php

namespace App\Service\Ai\Project;

use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\RetrieverInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

final class AiProjectContextBuilder
{
    public function __construct(
        #[Target('project_knowledge')] private readonly RetrieverInterface $retriever,
    ) {
    }

    public function buildContext(int $projectId, string $query, int $maxItems = 5): string
    {
        if ($projectId <= 0 || '' === trim($query)) {
            return '';
        }

        $documents = iterator_to_array($this->retriever->retrieve($query, [
            'maxItems' => $maxItems,
            'semanticRatio' => 0.75,
            'filter' => static function (VectorDocument $document) use ($projectId): bool {
                return (int) ($document->getMetadata()['projectId'] ?? 0) === $projectId;
            },
        ]));

        if ([] === $documents) {
            return '';
        }

        $parts = [];
        foreach ($documents as $index => $document) {
            $metadata = $document->getMetadata();
            $source = trim((string) ($metadata['resourceTitle'] ?? $metadata['resourceId'] ?? 'Quelle'));
            $text = trim((string) ($metadata->getText() ?? ''));
            if ('' === $text) {
                continue;
            }

            if (\strlen($text) > 900) {
                $text = substr($text, 0, 897).'...';
            }

            $parts[] = sprintf(
                "[%d] %s\n%s",
                $index + 1,
                '' !== $source ? $source : 'Quelle',
                $text
            );
        }

        if ([] === $parts) {
            return '';
        }

        return implode("\n\n", $parts);
    }
}
