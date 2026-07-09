<?php

namespace App\Service\Ai\Project;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ArrayParameterType;
use Symfony\AI\Platform\Vector\Vector;
use Symfony\AI\Store\Distance\DistanceCalculator;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\Exception\InvalidArgumentException;
use Symfony\AI\Store\Exception\UnsupportedQueryTypeException;
use Symfony\AI\Store\ManagedStoreInterface;
use Symfony\AI\Store\Query\HybridQuery;
use Symfony\AI\Store\Query\QueryInterface;
use Symfony\AI\Store\Query\TextQuery;
use Symfony\AI\Store\Query\VectorQuery;
use Symfony\AI\Store\StoreInterface;

final class AiProjectVectorStore implements StoreInterface, ManagedStoreInterface
{
    private const TABLE = 'ai_project_vector_document';

    public function __construct(
        private readonly Connection $connection,
        private readonly DistanceCalculator $distanceCalculator = new DistanceCalculator(),
    ) {
    }

    public function setup(array $options = []): void
    {
        if ([] !== $options) {
            throw new InvalidArgumentException('No supported options.');
        }
    }

    public function drop(array $options = []): void
    {
        if ([] !== $options) {
            throw new InvalidArgumentException('No supported options.');
        }

        $this->connection->executeStatement('DELETE FROM '.self::TABLE);
    }

    public function add(VectorDocument|array $documents): void
    {
        if ($documents instanceof VectorDocument) {
            $documents = [$documents];
        }

        foreach ($documents as $document) {
            if (!$document instanceof VectorDocument) {
                throw new InvalidArgumentException('Store expects VectorDocument instances.');
            }

            $metadata = $document->getMetadata();
            $projectId = (int) ($metadata['projectId'] ?? 0);
            if ($projectId <= 0) {
                throw new InvalidArgumentException('VectorDocument metadata must contain a valid "projectId".');
            }

            $resourceId = isset($metadata['resourceId']) ? (int) $metadata['resourceId'] : null;
            if (null !== $resourceId && $resourceId <= 0) {
                $resourceId = null;
            }

            $this->connection->insert(self::TABLE, [
                'id' => (string) $document->getId(),
                'project_id' => $projectId,
                'resource_id' => $resourceId,
                'content' => (string) ($metadata->getText() ?? ''),
                'vector_data' => json_encode($document->getVector()->getData(), \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES),
                'metadata' => json_encode($metadata->getArrayCopy(), \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES),
                'created_at' => time(),
            ]);
        }
    }

    public function remove(string|array $ids, array $options = []): void
    {
        if ([] !== $options) {
            throw new InvalidArgumentException('No supported options.');
        }

        $ids = \is_string($ids) ? [$ids] : array_values($ids);
        if ([] === $ids) {
            return;
        }

        $this->connection->executeStatement(
            'DELETE FROM '.self::TABLE.' WHERE id IN (?)',
            [$ids],
            [ArrayParameterType::STRING]
        );
    }

    public function removeByResource(int $projectId, int $resourceId): void
    {
        $this->connection->delete(self::TABLE, [
            'project_id' => $projectId,
            'resource_id' => $resourceId,
        ]);
    }

    public function supports(string $queryClass): bool
    {
        return \in_array($queryClass, [
            VectorQuery::class,
            TextQuery::class,
            HybridQuery::class,
        ], true);
    }

    /**
     * @param array{
     *     maxItems?: positive-int,
     *     filter?: callable(VectorDocument): bool
     * } $options
     *
     * @return iterable<VectorDocument>
     */
    public function query(QueryInterface $query, array $options = []): iterable
    {
        return match (true) {
            $query instanceof VectorQuery => $this->queryVector($query, $options),
            $query instanceof TextQuery => $this->queryText($query, $options),
            $query instanceof HybridQuery => $this->queryHybrid($query, $options),
            default => throw new UnsupportedQueryTypeException($query::class, $this),
        };
    }

    /**
     * @param array{maxItems?: positive-int, filter?: callable(VectorDocument): bool} $options
     *
     * @return iterable<VectorDocument>
     */
    private function queryVector(VectorQuery $query, array $options): iterable
    {
        $documents = $this->loadAllDocuments($options);

        yield from $this->distanceCalculator->calculate(
            $documents,
            $query->getVector(),
            $options['maxItems'] ?? null
        );
    }

    /**
     * @param array{maxItems?: positive-int, filter?: callable(VectorDocument): bool} $options
     *
     * @return iterable<VectorDocument>
     */
    private function queryText(TextQuery $query, array $options): iterable
    {
        $documents = array_filter($this->loadAllDocuments($options), static function (VectorDocument $document) use ($query): bool {
            $text = strtolower((string) ($document->getMetadata()->getText() ?? ''));
            foreach ($query->getTexts() as $searchText) {
                if ('' !== trim($searchText) && str_contains($text, strtolower($searchText))) {
                    return true;
                }
            }

            return false;
        });

        $documents = array_values($documents);
        $maxItems = $options['maxItems'] ?? null;

        yield from null !== $maxItems ? \array_slice($documents, 0, $maxItems) : $documents;
    }

    /**
     * @param array{maxItems?: positive-int, filter?: callable(VectorDocument): bool} $options
     *
     * @return iterable<VectorDocument>
     */
    private function queryHybrid(HybridQuery $query, array $options): iterable
    {
        $vectorResults = iterator_to_array($this->queryVector(new VectorQuery($query->getVector()), $options));
        $textResults = iterator_to_array($this->queryText(new TextQuery($query->getTexts()), $options));

        $mergedResults = [];
        $seenIds = [];

        foreach ($vectorResults as $document) {
            $id = (string) $document->getId();
            if (!isset($seenIds[$id])) {
                $mergedResults[] = new VectorDocument(
                    id: $document->getId(),
                    vector: $document->getVector(),
                    metadata: $document->getMetadata(),
                    score: null !== $document->getScore() ? $document->getScore() * $query->getSemanticRatio() : null,
                );
                $seenIds[$id] = true;
            }
        }

        foreach ($textResults as $document) {
            $id = (string) $document->getId();
            if (!isset($seenIds[$id])) {
                $mergedResults[] = $document;
                $seenIds[$id] = true;
            }
        }

        yield from $mergedResults;
    }

    /**
     * @param array{filter?: callable(VectorDocument): bool} $options
     *
     * @return list<VectorDocument>
     */
    private function loadAllDocuments(array $options): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, project_id, resource_id, content, vector_data, metadata FROM '.self::TABLE
        );

        $documents = [];
        foreach ($rows as $row) {
            $document = $this->hydrateDocument($row);
            if (!$document instanceof VectorDocument) {
                continue;
            }

            if (isset($options['filter']) && \is_callable($options['filter']) && !$options['filter']($document)) {
                continue;
            }

            $documents[] = $document;
        }

        return $documents;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateDocument(array $row): ?VectorDocument
    {
        $vectorData = json_decode((string) ($row['vector_data'] ?? ''), true);
        if (!\is_array($vectorData) || [] === $vectorData) {
            return null;
        }

        $vector = new Vector(array_map('floatval', $vectorData));

        $metadataPayload = json_decode((string) ($row['metadata'] ?? ''), true);
        $metadata = new Metadata(\is_array($metadataPayload) ? $metadataPayload : []);
        if (!$metadata->hasText() && isset($row['content']) && \is_string($row['content'])) {
            $metadata->setText($row['content']);
        }
        if (!$metadata->offsetExists('projectId')) {
            $metadata['projectId'] = (int) ($row['project_id'] ?? 0);
        }
        if (!$metadata->offsetExists('resourceId') && isset($row['resource_id'])) {
            $metadata['resourceId'] = null === $row['resource_id'] ? null : (int) $row['resource_id'];
        }

        return new VectorDocument(
            id: (string) ($row['id'] ?? ''),
            vector: $vector,
            metadata: $metadata,
        );
    }
}
