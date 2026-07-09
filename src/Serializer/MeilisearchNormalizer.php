<?php

namespace App\Serializer;

use Meilisearch\Bundle\SearchableObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Delegates meilisearch normalization to getSearchableArray() on any entity that has it.
 */
final class MeilisearchNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var object $object */
        return $object->getSearchableArray();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return SearchableObject::NORMALIZATION_FORMAT === $format
            && is_object($data)
            && method_exists($data, 'getSearchableArray');
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['object' => false];
    }
}
