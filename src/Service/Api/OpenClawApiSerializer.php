<?php

namespace App\Service\Api;

use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;

final class OpenClawApiSerializer
{
    /**
     * @var array<string, true>
     */
    private const HEAVY_FIELDS = [
        'fileData' => true,
        'html' => true,
        'htmlSource' => true,
        'itemsJson' => true,
        'lastError' => true,
        'parserData' => true,
        'rawResponse' => true,
        'vehicleData' => true,
    ];

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly OpenClawApiResourceRegistry $resourceRegistry,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(object $entity, bool $detail = false): array
    {
        $className = $entity::class;
        $metadata = $this->managerRegistry->getManagerForClass($className)?->getClassMetadata($className);
        if (!$metadata instanceof ClassMetadata) {
            return [
                '_meta' => [
                    'class' => $className,
                    'resource' => $this->resourceRegistry->getResourceForClass($className),
                    'label' => (string) $entity,
                ],
            ];
        }

        $payload = [
            '_meta' => [
                'class' => $className,
                'resource' => $this->resourceRegistry->getResourceForClass($className),
                'id' => $this->normalizeIdentifier($metadata, $entity),
                'identifierFields' => $metadata->getIdentifierFieldNames(),
                'label' => (string) $entity,
            ],
        ];

        foreach ($metadata->getFieldNames() as $fieldName) {
            if (!$detail && isset(self::HEAVY_FIELDS[$fieldName])) {
                continue;
            }

            $payload[$fieldName] = $this->normalizeValue($metadata->getFieldValue($entity, $fieldName));
        }

        foreach ($metadata->getAssociationNames() as $associationName) {
            $value = $metadata->getFieldValue($entity, $associationName);

            if ($metadata->isCollectionValuedAssociation($associationName)) {
                $payload[$associationName] = $this->normalizeCollection($value, $detail);
                continue;
            }

            $payload[$associationName] = $this->normalizeAssociation($value);
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function describeResource(string $className): array
    {
        $metadata = $this->managerRegistry->getManagerForClass($className)?->getClassMetadata($className);
        if (!$metadata instanceof ClassMetadata) {
            return [
                'class' => $className,
                'resource' => $this->resourceRegistry->getResourceForClass($className),
            ];
        }

        $fields = [];
        foreach ($metadata->getFieldNames() as $fieldName) {
            $fields[$fieldName] = [
                'type' => $metadata->getTypeOfField($fieldName),
                'heavy' => isset(self::HEAVY_FIELDS[$fieldName]),
                'writable' => !in_array($fieldName, ['createdAt'], true),
                'nullable' => !in_array($fieldName, $metadata->getIdentifierFieldNames(), true),
            ];
        }

        $associations = [];
        foreach ($metadata->getAssociationNames() as $associationName) {
            $associations[$associationName] = [
                'targetEntity' => $metadata->getAssociationTargetClass($associationName),
                'collection' => $metadata->isCollectionValuedAssociation($associationName),
                'writable' => true,
            ];
        }

        return [
            'resource' => $this->resourceRegistry->getResourceForClass($className),
            'class' => $className,
            'identifierFields' => $metadata->getIdentifierFieldNames(),
            'fields' => $fields,
            'associations' => $associations,
        ];
    }

    /**
     * @return array<string, mixed>|int|string|null
     */
    private function normalizeIdentifier(ClassMetadata $metadata, object $entity): array|int|string|null
    {
        $values = $metadata->getIdentifierValues($entity);
        if ([] === $values) {
            return null;
        }

        if (1 === count($values)) {
            return $this->normalizeValue(reset($values));
        }

        $normalized = [];
        foreach ($values as $key => $value) {
            $normalized[$key] = $this->normalizeValue($value);
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeAssociation(mixed $value): ?array
    {
        if (!is_object($value)) {
            return null;
        }

        $className = $value::class;
        $metadata = $this->managerRegistry->getManagerForClass($className)?->getClassMetadata($className);
        if (!$metadata instanceof ClassMetadata) {
            return [
                'class' => $className,
                'label' => (string) $value,
            ];
        }

        return [
            'resource' => $this->resourceRegistry->getResourceForClass($className),
            'class' => $className,
            'id' => $this->normalizeIdentifier($metadata, $value),
            'label' => (string) $value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeCollection(mixed $value, bool $detail): array
    {
        if (!$value instanceof Collection) {
            return [
                'count' => 0,
                'items' => [],
            ];
        }

        $items = [];
        if ($detail) {
            foreach (array_slice($value->toArray(), 0, 25) as $item) {
                $items[] = $this->normalizeAssociation($item);
            }
        }

        return [
            'count' => $value->count(),
            'items' => $items,
        ];
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }

            return $normalized;
        }

        if (is_scalar($value) || null === $value) {
            return $value;
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        return null;
    }
}
