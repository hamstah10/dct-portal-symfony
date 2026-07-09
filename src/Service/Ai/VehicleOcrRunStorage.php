<?php

namespace App\Service\Ai;

use Doctrine\DBAL\Connection;

final class VehicleOcrRunStorage
{
    private const TABLE = 'ai_vehicle_ocr_run';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function createRun(
        string $sourceType,
        ?string $originalName,
        ?string $mimeType,
        ?string $documentPath,
        ?string $previewPath,
        array $previewPaths,
        string $model,
    ): int {
        $now = time();

        $this->connection->insert(self::TABLE, [
            'source_type' => $sourceType,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'document_path' => $documentPath,
            'preview_path' => $previewPath,
            'preview_paths' => $this->encodeJson($previewPaths),
            'model' => $model,
            'extracted' => null,
            'raw_text' => null,
            'raw_response' => null,
            'criteria' => null,
            'results' => null,
            'error' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * @param array<string, mixed>|null $extracted
     * @param array<string, mixed>|null $criteria
     * @param array<int, array<string, mixed>> $results
     */
    public function updateRun(
        int $id,
        ?array $extracted,
        ?string $rawText,
        ?string $rawResponse,
        ?array $criteria,
        array $results,
        ?string $error = null,
    ): void {
        $this->connection->update(self::TABLE, [
            'extracted' => $this->encodeJson($extracted),
            'raw_text' => $rawText,
            'raw_response' => $rawResponse,
            'criteria' => $this->encodeJson($criteria),
            'results' => $this->encodeJson($results),
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
     *   sourceType:string,
     *   originalName:?string,
     *   previewPath:?string,
     *   model:string,
     *   error:?string,
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
            $previewPaths = is_array($hydrated['previewPaths'] ?? null) ? $hydrated['previewPaths'] : [];
            $runs[] = [
                'id' => (int) $hydrated['id'],
                'sourceType' => (string) ($hydrated['sourceType'] ?? ''),
                'originalName' => $hydrated['originalName'] ?? null,
                'previewPath' => $hydrated['previewPath'] ?? null,
                'previewCount' => \count($previewPaths),
                'model' => (string) ($hydrated['model'] ?? ''),
                'error' => $hydrated['error'] ?? null,
                'preview' => $this->buildPreview($hydrated),
                'createdAt' => (int) ($hydrated['createdAt'] ?? 0),
                'updatedAt' => (int) ($hydrated['updatedAt'] ?? 0),
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
        $row['sourceType'] = $row['source_type'] ?? null;
        $row['originalName'] = $row['original_name'] ?? null;
        $row['mimeType'] = $row['mime_type'] ?? null;
        $row['documentPath'] = $row['document_path'] ?? null;
        $row['previewPath'] = $row['preview_path'] ?? null;
        $row['previewPaths'] = $this->decodeJson($row['preview_paths'] ?? null) ?? [];
        $row['rawText'] = $row['raw_text'] ?? null;
        $row['rawResponse'] = $row['raw_response'] ?? null;
        $row['createdAt'] = (int) ($row['created_at'] ?? 0);
        $row['updatedAt'] = (int) ($row['updated_at'] ?? 0);
        $row['extracted'] = $this->decodeJson($row['extracted'] ?? null);
        $row['criteria'] = $this->decodeJson($row['criteria'] ?? null);
        $row['results'] = $this->decodeJson($row['results'] ?? null);

        return $row;
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
        $payload = null;
        if (is_array($row['extracted'] ?? null) && [] !== $row['extracted']) {
            $payload = $row['extracted'];
        } elseif (is_array($row['criteria'] ?? null) && [] !== $row['criteria']) {
            $payload = $row['criteria'];
        }

        $parts = [];
        if (is_array($payload)) {
            foreach (['brand', 'series', 'model'] as $key) {
                $value = trim((string) ($payload[$key] ?? ''));
                if ('' !== $value) {
                    $parts[] = $value;
                }
            }

            $engineCode = trim((string) ($payload['engineCode'] ?? ''));
            if ('' !== $engineCode) {
                $parts[] = $engineCode;
            }
        }

        if ([] === $parts) {
            $fallback = trim((string) ($row['originalName'] ?? ''));
            if ('' !== $fallback) {
                $parts[] = $fallback;
            }
        }

        if ([] === $parts) {
            $fallback = trim((string) ($row['sourceType'] ?? ''));
            if ('' !== $fallback) {
                $parts[] = $fallback;
            }
        }

        if ([] === $parts) {
            return null;
        }

        $preview = implode(' / ', $parts);
        if (\strlen($preview) > 120) {
            $preview = substr($preview, 0, 117).'...';
        }

        return $preview;
    }
}
