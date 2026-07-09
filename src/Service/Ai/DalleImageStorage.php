<?php

namespace App\Service\Ai;

use Doctrine\DBAL\Connection;
use Symfony\AI\Agent\Toolbox\Source\Source;
use Symfony\AI\Agent\Toolbox\Source\SourceCollection;
use Symfony\AI\Platform\TokenUsage\TokenUsageInterface;

final class DalleImageStorage
{
    private const TABLE = 'ai_dalle_image';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function createRun(
        string $prompt,
        string $model,
        string $size,
        string $quality,
        string $style,
        string $format,
        int $count = 1,
        string $aspectRatio = 'square',
    ): int {
        $now = time();

        $this->connection->insert(self::TABLE, [
            'project_id' => null,
            'prompt' => $prompt,
            'revised_prompt' => null,
            'model' => $model,
            'size' => $size,
            'quality' => $this->isModel2($model) ? null : $quality,
            'style' => $this->isModel2($model) ? null : $style,
            'response_format' => $format,
            'image_count' => $count,
            'aspect_ratio' => $aspectRatio,
            'image_path' => null,
            'image_url' => null,
            'image_paths' => null,
            'image_urls' => null,
            'metadata' => null,
            'error' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function isModel2(string $model): bool
    {
        return 'dall-e-2' === $model;
    }

    /**
     * @param array<string, mixed>|null $metadata
     */
    /**
     * @param array<string, mixed>|null $metadata
     * @param list<string> $imagePaths
     * @param list<string> $imageUrls
     */
    public function updateRun(
        int $id,
        array $imagePaths,
        array $imageUrls,
        ?string $revisedPrompt,
        ?array $metadata,
        ?string $error = null,
    ): void {
        $imagePath = $imagePaths[0] ?? null;
        $imageUrl = $imageUrls[0] ?? null;

        $this->connection->update(self::TABLE, [
            'image_path' => $imagePath,
            'image_url' => $imageUrl,
            'image_paths' => $this->encodeJson($imagePaths),
            'image_urls' => $this->encodeJson($imageUrls),
            'revised_prompt' => $revisedPrompt,
            'metadata' => $this->encodeJson($this->normalizeMetadata($metadata)),
            'error' => $error,
            'updated_at' => time(),
        ], ['id' => $id]);
    }

    public function exists(int $id): bool
    {
        $value = $this->connection->fetchOne(
            'SELECT id FROM '.self::TABLE.' WHERE id = ? AND project_id IS NULL',
            [$id],
        );

        return false !== $value;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRun(int $id): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM '.self::TABLE.' WHERE id = ? AND project_id IS NULL',
            [$id],
        );

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrateRow($row);
    }

    /**
     * @return list<array{
     *   id:int,
     *   prompt:string,
     *   revisedPrompt:?string,
     *   model:string,
     *   size:string,
     *   quality:string,
     *   style:string,
     *   format:string,
     *   imagePath:?string,
     *   error:?string,
     *   preview:?string,
     *   createdAt:int,
     *   updatedAt:int
     * }>
     */
    public function listRuns(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM '.self::TABLE.' WHERE project_id IS NULL ORDER BY updated_at DESC, id DESC'
        );

        $runs = [];
        foreach ($rows as $row) {
            $hydrated = $this->hydrateRow($row);
            $runs[] = [
                'id' => (int) $hydrated['id'],
                'prompt' => (string) $hydrated['prompt'],
                'revisedPrompt' => $hydrated['revisedPrompt'] ?? null,
                'model' => (string) ($hydrated['model'] ?? ''),
                'size' => (string) ($hydrated['size'] ?? ''),
                'quality' => (string) ($hydrated['quality'] ?? ''),
                'style' => (string) ($hydrated['style'] ?? ''),
                'format' => (string) ($hydrated['format'] ?? ''),
                'count' => (int) ($hydrated['imageCount'] ?? 1),
                'aspect' => (string) ($hydrated['aspectRatio'] ?? 'square'),
                'imagePath' => $hydrated['imagePath'] ?? null,
                'imagePaths' => $hydrated['imagePaths'] ?? [],
                'error' => $hydrated['error'] ?? null,
                'preview' => $this->buildPreview($hydrated),
                'createdAt' => (int) $hydrated['createdAt'],
                'updatedAt' => (int) $hydrated['updatedAt'],
            ];
        }

        return $runs;
    }

    public function deleteRun(int $id): void
    {
        $this->connection->executeStatement(
            'DELETE FROM '.self::TABLE.' WHERE id = ? AND project_id IS NULL',
            [$id],
        );
    }

    public function createProjectRun(
        int $projectId,
        string $prompt,
        string $model,
        string $size,
        string $quality,
        string $style,
        string $format,
        int $count = 1,
        string $aspectRatio = 'square',
    ): int {
        $now = time();

        $this->connection->insert(self::TABLE, [
            'project_id' => $projectId,
            'prompt' => $prompt,
            'revised_prompt' => null,
            'model' => $model,
            'size' => $size,
            'quality' => $this->isModel2($model) ? null : $quality,
            'style' => $this->isModel2($model) ? null : $style,
            'response_format' => $format,
            'image_count' => $count,
            'aspect_ratio' => $aspectRatio,
            'image_path' => null,
            'image_url' => null,
            'image_paths' => null,
            'image_urls' => null,
            'metadata' => null,
            'error' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getProjectRun(int $projectId, int $id): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM '.self::TABLE.' WHERE id = ? AND project_id = ?',
            [$id, $projectId],
        );

        if (!is_array($row)) {
            return null;
        }

        return $this->hydrateRow($row);
    }

    /**
     * @return list<array{
     *   id:int,
     *   prompt:string,
     *   revisedPrompt:?string,
     *   model:string,
     *   size:string,
     *   quality:string,
     *   style:string,
     *   format:string,
     *   imagePath:?string,
     *   error:?string,
     *   preview:?string,
     *   createdAt:int,
     *   updatedAt:int
     * }>
     */
    public function listProjectRuns(int $projectId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM '.self::TABLE.' WHERE project_id = ? ORDER BY updated_at DESC, id DESC',
            [$projectId],
        );

        $runs = [];
        foreach ($rows as $row) {
            $hydrated = $this->hydrateRow($row);
            $runs[] = [
                'id' => (int) $hydrated['id'],
                'prompt' => (string) $hydrated['prompt'],
                'revisedPrompt' => $hydrated['revisedPrompt'] ?? null,
                'model' => (string) ($hydrated['model'] ?? ''),
                'size' => (string) ($hydrated['size'] ?? ''),
                'quality' => (string) ($hydrated['quality'] ?? ''),
                'style' => (string) ($hydrated['style'] ?? ''),
                'format' => (string) ($hydrated['format'] ?? ''),
                'count' => (int) ($hydrated['imageCount'] ?? 1),
                'aspect' => (string) ($hydrated['aspectRatio'] ?? 'square'),
                'imagePath' => $hydrated['imagePath'] ?? null,
                'imagePaths' => $hydrated['imagePaths'] ?? [],
                'metadata' => $hydrated['metadata'] ?? null,
                'error' => $hydrated['error'] ?? null,
                'preview' => $this->buildPreview($hydrated),
                'createdAt' => (int) $hydrated['createdAt'],
                'updatedAt' => (int) $hydrated['updatedAt'],
            ];
        }

        return $runs;
    }

    public function deleteProjectRun(int $projectId, int $id): void
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
        $row['projectId'] = isset($row['project_id']) ? (null === $row['project_id'] ? null : (int) $row['project_id']) : null;
        $row['createdAt'] = (int) ($row['created_at'] ?? 0);
        $row['updatedAt'] = (int) ($row['updated_at'] ?? 0);
        $row['imagePath'] = $row['image_path'] ?? null;
        $row['imageUrl'] = $row['image_url'] ?? null;
        $row['imagePaths'] = $this->decodeJson($row['image_paths'] ?? null) ?? [];
        $row['imageUrls'] = $this->decodeJson($row['image_urls'] ?? null) ?? [];
        $row['revisedPrompt'] = $row['revised_prompt'] ?? null;
        $row['format'] = $row['response_format'] ?? null;
        $row['imageCount'] = (int) ($row['image_count'] ?? 1);
        $row['aspectRatio'] = $row['aspect_ratio'] ?? 'square';
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

        return [
            '__class' => $value::class,
        ];
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

        $data = json_decode($payload, true);

        return is_array($data) ? $data : null;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function buildPreview(array $row): ?string
    {
        $candidate = trim((string) ($row['prompt'] ?? ''));
        if ('' === $candidate) {
            return null;
        }

        if (\strlen($candidate) > 120) {
            $candidate = substr($candidate, 0, 117).'...';
        }

        return $candidate;
    }
}
