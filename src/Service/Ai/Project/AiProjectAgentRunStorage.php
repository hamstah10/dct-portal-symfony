<?php

namespace App\Service\Ai\Project;

use Doctrine\DBAL\Connection;
use Symfony\AI\Agent\Toolbox\Source\Source;
use Symfony\AI\Agent\Toolbox\Source\SourceCollection;
use Symfony\AI\Platform\TokenUsage\TokenUsageInterface;

final class AiProjectAgentRunStorage
{
    private const TABLE = 'ai_project_agent_run';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function createRun(int $projectId, string $systemPrompt, string $prompt): int
    {
        $now = time();

        $this->connection->insert(self::TABLE, [
            'project_id' => $projectId,
            'system_prompt' => $systemPrompt,
            'prompt' => $prompt,
            'response' => null,
            'metadata' => null,
            'error' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * @param array<string, mixed>|null $metadata
     */
    public function updateRun(int $id, ?string $response, ?array $metadata, ?string $error = null): void
    {
        $this->connection->update(self::TABLE, [
            'response' => $response,
            'metadata' => $this->encodeJson($this->normalizeMetadata($metadata)),
            'error' => $error,
            'updated_at' => time(),
        ], ['id' => $id]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRun(int $id): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM '.self::TABLE.' WHERE id = ?',
            [$id]
        );

        return \is_array($row) ? $this->hydrateRow($row) : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listRuns(int $projectId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM '.self::TABLE.' WHERE project_id = ? ORDER BY updated_at DESC, id DESC',
            [$projectId]
        );

        return array_values(array_map(fn (array $row): array => $this->hydrateRow($row), $rows));
    }

    public function deleteRun(int $projectId, int $id): void
    {
        $this->connection->delete(self::TABLE, [
            'id' => $id,
            'project_id' => $projectId,
        ]);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function hydrateRow(array $row): array
    {
        $row['id'] = (int) $row['id'];
        $row['projectId'] = (int) ($row['project_id'] ?? 0);
        $row['systemPrompt'] = $row['system_prompt'] ?? '';
        $row['createdAt'] = (int) ($row['created_at'] ?? 0);
        $row['updatedAt'] = (int) ($row['updated_at'] ?? 0);
        $row['metadata'] = $this->decodeJson($row['metadata'] ?? null);

        return $row;
    }

    /**
     * @param array<string, mixed>|null $metadata
     *
     * @return array<string, mixed>|null
     */
    private function normalizeMetadata(?array $metadata): ?array
    {
        if (null === $metadata) {
            return null;
        }

        $normalized = [];
        foreach ($metadata as $key => $value) {
            $normalized[$key] = $this->normalizeMetadataValue($value);
        }

        return $normalized;
    }

    private function normalizeMetadataValue(mixed $value): mixed
    {
        if (null === $value || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof SourceCollection) {
            return array_map(static function (Source $source): array {
                return [
                    'name' => $source->getName(),
                    'reference' => $source->getReference(),
                    'content' => $source->getContent(),
                ];
            }, $value->all());
        }

        if ($value instanceof TokenUsageInterface) {
            return [
                'promptTokens' => $value->getPromptTokens(),
                'completionTokens' => $value->getCompletionTokens(),
                'thinkingTokens' => $value->getThinkingTokens(),
                'toolTokens' => $value->getToolTokens(),
                'cachedTokens' => $value->getCachedTokens(),
                'cacheCreationTokens' => $value->getCacheCreationTokens(),
                'cacheReadTokens' => $value->getCacheReadTokens(),
                'remainingTokens' => $value->getRemainingTokens(),
                'remainingTokensMinute' => $value->getRemainingTokensMinute(),
                'remainingTokensMonth' => $value->getRemainingTokensMonth(),
                'totalTokens' => $value->getTotalTokens(),
            ];
        }

        if (is_array($value)) {
            $items = [];
            foreach ($value as $key => $entry) {
                $items[$key] = $this->normalizeMetadataValue($entry);
            }

            return $items;
        }

        if ($value instanceof \JsonSerializable) {
            return $value->jsonSerialize();
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        return ['__class' => $value::class];
    }

    private function encodeJson(?array $payload): ?string
    {
        if (null === $payload) {
            return null;
        }

        return json_encode($payload, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /**
     * @return array<mixed>|null
     */
    private function decodeJson(?string $payload): ?array
    {
        if (null === $payload || '' === trim($payload)) {
            return null;
        }

        $decoded = json_decode($payload, true);

        return \is_array($decoded) ? $decoded : null;
    }
}
