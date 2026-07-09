<?php

namespace App\Service\Ai;

use Doctrine\DBAL\Connection;
use Symfony\AI\Agent\Toolbox\Source\Source;
use Symfony\AI\Agent\Toolbox\Source\SourceCollection;
use Symfony\AI\Platform\TokenUsage\TokenUsageInterface;

final class FirecrawlRunStorage
{
    private const TABLE = 'ai_firecrawl_run';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const DEFAULT_PROGRESS_MAX = 4;

    public function __construct(private readonly Connection $connection)
    {
    }

    public function createRun(
        string $action,
        string $url,
        string $instruction,
        string $model,
        int $crawlDepth = 0,
        int $crawlLimit = 0,
    ): int {
        $now = time();

        $this->connection->insert(self::TABLE, [
            'action' => $action,
            'url' => $url,
            'instruction' => $instruction,
            'model' => $model,
            'crawl_depth' => $crawlDepth,
            'crawl_limit' => $crawlLimit,
            'response' => null,
            'metadata' => null,
            'map_links' => null,
            'error' => null,
            'status' => self::STATUS_QUEUED,
            'progress_label' => 'Wartet auf Hintergrundprozess',
            'progress_current' => 0,
            'progress_max' => self::DEFAULT_PROGRESS_MAX,
            'started_at' => null,
            'finished_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * @param array<string, mixed>|null $metadata
     * @param list<string> $mapLinks
     */
    public function completeRun(
        int $id,
        ?string $response,
        ?array $metadata,
        array $mapLinks = [],
    ): void {
        $finishedAt = time();
        $payload = [
            'response' => $response,
            'metadata' => $this->encodeJson($this->normalizeMetadata($metadata)),
            'map_links' => $this->encodeJson($mapLinks),
            'error' => null,
            'status' => self::STATUS_SUCCESS,
            'progress_label' => 'Abgeschlossen',
            'progress_current' => $this->resolveProgressMax($id),
            'finished_at' => $finishedAt,
            'updated_at' => $finishedAt,
        ];

        $this->connection->update(self::TABLE, $payload, ['id' => $id]);
    }

    /**
     * @param array<string, mixed>|null $metadata
     * @param list<string> $mapLinks
     */
    public function failRun(
        int $id,
        ?string $error,
        ?string $response = null,
        ?array $metadata = null,
        array $mapLinks = [],
    ): void {
        $finishedAt = time();

        $this->connection->update(self::TABLE, [
            'response' => $response,
            'metadata' => $this->encodeJson($this->normalizeMetadata($metadata)),
            'map_links' => $this->encodeJson($mapLinks),
            'error' => $error,
            'status' => self::STATUS_FAILED,
            'progress_label' => 'Fehlgeschlagen',
            'progress_current' => $this->resolveProgressMax($id),
            'finished_at' => $finishedAt,
            'updated_at' => $finishedAt,
        ], ['id' => $id]);
    }

    public function updateProgress(
        int $id,
        string $status,
        int $current,
        int $max,
        string $label,
        ?int $startedAt = null,
    ): void {
        $payload = [
            'status' => $status,
            'progress_label' => $label,
            'progress_current' => max(0, min($max, $current)),
            'progress_max' => max(1, $max),
            'updated_at' => time(),
        ];

        if (null !== $startedAt) {
            $payload['started_at'] = $startedAt;
        }

        $this->connection->update(self::TABLE, $payload, ['id' => $id]);
    }

    public function exists(int $id): bool
    {
        $value = $this->connection->fetchOne(
            'SELECT id FROM '.self::TABLE.' WHERE id = ?',
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
            'SELECT * FROM '.self::TABLE.' WHERE id = ?',
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
     *   action:string,
     *   url:string,
     *   instruction:string,
     *   model:string,
     *   crawlDepth:int,
     *   crawlLimit:int,
     *   response:?string,
     *   error:?string,
     *   status:string,
     *   progressPercent:int,
     *   progressLabel:?string,
     *   preview:?string,
     *   createdAt:int,
     *   updatedAt:int
     * }>
     */
    public function listRuns(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM '.self::TABLE.' ORDER BY updated_at DESC, id DESC'
        );

        $runs = [];
        foreach ($rows as $row) {
            $hydrated = $this->hydrateRow($row);
            $runs[] = [
                'id' => (int) $hydrated['id'],
                'action' => (string) $hydrated['action'],
                'url' => (string) $hydrated['url'],
                'instruction' => (string) ($hydrated['instruction'] ?? ''),
                'model' => (string) ($hydrated['model'] ?? ''),
                'crawlDepth' => (int) ($hydrated['crawlDepth'] ?? 0),
                'crawlLimit' => (int) ($hydrated['crawlLimit'] ?? 0),
                'response' => $hydrated['response'] ?? null,
                'error' => $hydrated['error'] ?? null,
                'status' => (string) ($hydrated['status'] ?? self::STATUS_SUCCESS),
                'progressPercent' => (int) ($hydrated['progressPercent'] ?? 0),
                'progressLabel' => $hydrated['progressLabel'] ?? null,
                'preview' => $this->buildPreview($hydrated),
                'createdAt' => (int) $hydrated['createdAt'],
                'updatedAt' => (int) $hydrated['updatedAt'],
            ];
        }

        return $runs;
    }

    public function deleteRun(int $id): void
    {
        $this->connection->delete(self::TABLE, ['id' => $id]);
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function hydrateRow(array $row): array
    {
        $row['id'] = (int) $row['id'];
        $row['crawlDepth'] = (int) ($row['crawl_depth'] ?? 0);
        $row['crawlLimit'] = (int) ($row['crawl_limit'] ?? 0);
        $row['status'] = (string) ($row['status'] ?? self::STATUS_SUCCESS);
        $row['progressLabel'] = $row['progress_label'] ?? null;
        $row['progressCurrent'] = (int) ($row['progress_current'] ?? self::DEFAULT_PROGRESS_MAX);
        $row['progressMax'] = max(1, (int) ($row['progress_max'] ?? self::DEFAULT_PROGRESS_MAX));
        $row['createdAt'] = (int) ($row['created_at'] ?? 0);
        $row['updatedAt'] = (int) ($row['updated_at'] ?? 0);
        $row['startedAt'] = isset($row['started_at']) ? (null === $row['started_at'] ? null : (int) $row['started_at']) : null;
        $row['finishedAt'] = isset($row['finished_at']) ? (null === $row['finished_at'] ? null : (int) $row['finished_at']) : null;
        $row['metadata'] = $this->decodeJson($row['metadata'] ?? null);
        $row['mapLinks'] = $this->decodeJson($row['map_links'] ?? null) ?? [];
        $row['progressPercent'] = $this->calculateProgressPercent($row['progressCurrent'], $row['progressMax'], $row['status']);

        return $row;
    }

    private function resolveProgressMax(int $id): int
    {
        $run = $this->getRun($id);
        if (!is_array($run)) {
            return self::DEFAULT_PROGRESS_MAX;
        }

        return max(1, (int) ($run['progressMax'] ?? self::DEFAULT_PROGRESS_MAX));
    }

    private function calculateProgressPercent(int $current, int $max, string $status): int
    {
        if ($max <= 0) {
            return self::STATUS_SUCCESS === $status ? 100 : 0;
        }

        $value = (int) floor(($current / $max) * 100);

        return max(0, min(100, $value));
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
        $candidate = trim((string) ($row['response'] ?? ''));
        if ('' === $candidate) {
            $candidate = trim((string) ($row['instruction'] ?? ''));
        }
        if ('' === $candidate) {
            return null;
        }

        if (\strlen($candidate) > 120) {
            $candidate = substr($candidate, 0, 117).'...';
        }

        return $candidate;
    }
}
