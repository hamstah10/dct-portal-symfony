<?php

namespace App\Service\Api;

use App\Entity\Kunde;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class OpenClawApiWriter
{
    /**
     * @var array<string, true>
     */
    private const BLOCKED_FIELDS = [
        'createdAt' => true,
    ];

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(string $className, array $payload): object
    {
        $entity = new $className();
        $metadata = $this->getMetadata($className);

        $this->applyPayload($entity, $metadata, $payload, false);

        return $entity;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(object $entity, array $payload, bool $partial): object
    {
        $metadata = $this->getMetadata($entity::class);
        $this->applyPayload($entity, $metadata, $payload, $partial);

        return $entity;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function applyPayload(object $entity, ClassMetadata $metadata, array $payload, bool $partial): void
    {
        $this->applySpecialFields($entity, $payload);

        foreach ($payload as $field => $value) {
            if (!is_string($field) || '' === trim($field) || str_starts_with($field, '_')) {
                continue;
            }

            if (isset(self::BLOCKED_FIELDS[$field])) {
                continue;
            }

            if ($metadata->hasField($field)) {
                if (in_array($field, $metadata->getIdentifierFieldNames(), true) && $partial) {
                    continue;
                }

                $normalizedValue = $this->normalizeFieldValue($metadata, $field, $value);
                $this->assignScalarField($entity, $metadata, $field, $normalizedValue);
                continue;
            }

            if (!$metadata->hasAssociation($field)) {
                continue;
            }

            if ($metadata->isCollectionValuedAssociation($field)) {
                $this->applyCollectionAssociation($entity, $metadata, $field, $value);
                continue;
            }

            $resolvedAssociation = $this->resolveAssociationValue($metadata, $field, $value);
            $this->assignAssociationField($entity, $metadata, $field, $resolvedAssociation);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function applySpecialFields(object $entity, array $payload): void
    {
        if ($entity instanceof Kunde) {
            $plainPassword = $payload['plainPasswort'] ?? $payload['plainPassword'] ?? null;
            if (is_string($plainPassword) && '' !== trim($plainPassword)) {
                $entity->setPlainPasswort($plainPassword);
                $entity->setPasswort(password_hash($plainPassword, PASSWORD_BCRYPT));
                $entity->setPasswordChangedAt(new \DateTimeImmutable());
            }
        }

        if ($entity instanceof User) {
            $plainPassword = $payload['plainPassword'] ?? null;
            if (is_string($plainPassword) && '' !== trim($plainPassword)) {
                $entity->setPlainPassword($plainPassword);
                $entity->setPassword($this->passwordHasher->hashPassword($entity, $plainPassword));
            }
        }
    }

    private function assignScalarField(object $entity, ClassMetadata $metadata, string $field, mixed $value): void
    {
        $setter = 'set' . ucfirst($field);
        if (method_exists($entity, $setter)) {
            $entity->{$setter}($value);

            return;
        }

        $metadata->setFieldValue($entity, $field, $value);
    }

    private function assignAssociationField(object $entity, ClassMetadata $metadata, string $field, mixed $value): void
    {
        $setter = 'set' . ucfirst($field);
        if (method_exists($entity, $setter)) {
            $entity->{$setter}($value);

            return;
        }

        $metadata->setFieldValue($entity, $field, $value);
    }

    private function normalizeFieldValue(ClassMetadata $metadata, string $field, mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        return match ($metadata->getTypeOfField($field)) {
            'bigint', 'integer', 'smallint' => (int) $value,
            'float' => (float) $value,
            'decimal' => (string) $value,
            'boolean' => filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE) ?? false,
            'date', 'date_immutable', 'datetime', 'datetime_immutable', 'datetimetz', 'datetimetz_immutable' => $this->normalizeDateTime($value),
            'json', 'array' => is_array($value) ? $value : [$value],
            default => $value,
        };
    }

    private function normalizeDateTime(mixed $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Datumsfeld muss ein gueltiger ISO-8601-String sein.');
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable) {
            throw new \InvalidArgumentException(sprintf('Ungueltiges Datum: %s', $value));
        }
    }

    private function resolveAssociationValue(ClassMetadata $metadata, string $field, mixed $value): ?object
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $targetClass = $metadata->getAssociationTargetClass($field);
        $targetMetadata = $this->getMetadata($targetClass);
        $identifierFields = $targetMetadata->getIdentifierFieldNames();
        $identifierField = $identifierFields[0] ?? null;
        if (null === $identifierField) {
            return null;
        }

        $identifierValue = null;
        if (is_scalar($value)) {
            $identifierValue = $value;
        } elseif (is_array($value)) {
            if (array_key_exists('id', $value)) {
                $identifierValue = $value['id'];
            } elseif (array_key_exists($identifierField, $value)) {
                $identifierValue = $value[$identifierField];
            }
        }

        if (null === $identifierValue || '' === $identifierValue) {
            return null;
        }

        $entity = $this->managerRegistry->getRepository($targetClass)->find($identifierValue);
        if (!is_object($entity)) {
            throw new \InvalidArgumentException(sprintf('Verknuepfte Entitaet fuer %s wurde nicht gefunden.', $field));
        }

        return $entity;
    }

    private function applyCollectionAssociation(object $entity, ClassMetadata $metadata, string $field, mixed $value): void
    {
        if (!is_array($value)) {
            return;
        }

        $collection = $metadata->getFieldValue($entity, $field);
        if (!$collection instanceof Collection) {
            return;
        }

        $targetClass = $metadata->getAssociationTargetClass($field);
        $targetMetadata = $this->getMetadata($targetClass);
        $identifierFields = $targetMetadata->getIdentifierFieldNames();
        $identifierField = $identifierFields[0] ?? 'id';

        $resolvedEntities = [];
        foreach ($value as $entry) {
            $identifierValue = null;
            if (is_scalar($entry)) {
                $identifierValue = $entry;
            } elseif (is_array($entry)) {
                $identifierValue = $entry['id'] ?? $entry[$identifierField] ?? null;
            }

            if (null === $identifierValue || '' === $identifierValue) {
                continue;
            }

            $resolved = $this->managerRegistry->getRepository($targetClass)->find($identifierValue);
            if (is_object($resolved)) {
                $resolvedEntities[] = $resolved;
            }
        }

        foreach ($collection->toArray() as $existing) {
            if (!in_array($existing, $resolvedEntities, true)) {
                $removeMethod = 'remove' . $this->singularize($field);
                if (method_exists($entity, $removeMethod)) {
                    $entity->{$removeMethod}($existing);
                } else {
                    $collection->removeElement($existing);
                }
            }
        }

        foreach ($resolvedEntities as $resolvedEntity) {
            if (!$collection->contains($resolvedEntity)) {
                $addMethod = 'add' . $this->singularize($field);
                if (method_exists($entity, $addMethod)) {
                    $entity->{$addMethod}($resolvedEntity);
                } else {
                    $collection->add($resolvedEntity);
                }
            }
        }
    }

    private function singularize(string $value): string
    {
        $normalized = ucfirst($value);
        if (str_ends_with($normalized, 'ies')) {
            return substr($normalized, 0, -3) . 'y';
        }

        if (str_ends_with($normalized, 'en')) {
            return substr($normalized, 0, -2);
        }

        if (str_ends_with($normalized, 's')) {
            return substr($normalized, 0, -1);
        }

        return $normalized;
    }

    private function getMetadata(string $className): ClassMetadata
    {
        $metadata = $this->managerRegistry->getManagerForClass($className)?->getClassMetadata($className);
        if (!$metadata instanceof ClassMetadata) {
            throw new \RuntimeException(sprintf('Keine Doctrine-Metadaten fuer %s gefunden.', $className));
        }

        return $metadata;
    }
}
