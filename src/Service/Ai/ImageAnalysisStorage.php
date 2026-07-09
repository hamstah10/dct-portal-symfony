<?php

namespace App\Service\Ai;

use Doctrine\DBAL\Connection;

final class ImageAnalysisStorage
{
    private const TABLE = 'ai_image_analysis';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function createRun(
        ?string $imagePath,
        string $model,
        string $prompt,
    ): int {
        $now = time();

        $this->connection->insert(self::TABLE, [
            'image_path' => $imagePath,
            'model' => $model,
            'prompt' => $prompt,
            'response' => null,
            'error' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function updateRun(
        int $id,
        ?string $response,
        ?string $error = null,
    ): void {
        $this->connection->update(self::TABLE, [
            'response' => $response,
            'error' => $error,
            'updated_at' => time(),
        ], ['id' => $id]);
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
     *   imagePath:?string,
     *   model:string,
     *   prompt:string,
     *   response:?string,
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
            $runs[] = [
                'id' => (int) $hydrated['id'],
                'imagePath' => $hydrated['imagePath'] ?? null,
                'model' => (string) ($hydrated['model'] ?? ''),
                'prompt' => (string) ($hydrated['prompt'] ?? ''),
                'response' => $hydrated['response'] ?? null,
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
        $row['imagePath'] = $row['image_path'] ?? null;
        $row['createdAt'] = (int) ($row['created_at'] ?? 0);
        $row['updatedAt'] = (int) ($row['updated_at'] ?? 0);

        return $row;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function buildPreview(array $row): ?string
    {
        $candidate = trim((string) ($row['response'] ?? ''));
        if ('' === $candidate) {
            $candidate = trim((string) ($row['prompt'] ?? ''));
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
