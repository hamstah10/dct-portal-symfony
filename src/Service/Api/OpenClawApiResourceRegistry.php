<?php

namespace App\Service\Api;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;

final class OpenClawApiResourceRegistry
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * @return array<string, class-string>
     */
    public function getResources(): array
    {
        $resources = [];

        foreach ($this->managerRegistry->getManager()->getMetadataFactory()->getAllMetadata() as $metadata) {
            if (!$metadata instanceof ClassMetadata) {
                continue;
            }

            $className = $metadata->getName();
            if (!str_starts_with($className, 'App\\Entity\\')) {
                continue;
            }

            $resource = $this->toResourceName($className);
            $resources[$resource] = $className;
        }

        ksort($resources);

        return $resources;
    }

    /**
     * @return class-string|null
     */
    public function resolveClass(string $resource): ?string
    {
        $resources = $this->getResources();

        return $resources[strtolower(trim($resource))] ?? null;
    }

    public function getResourceForClass(string $className): ?string
    {
        $normalized = ltrim($className, '\\');

        foreach ($this->getResources() as $resource => $candidate) {
            if ($candidate === $normalized) {
                return $resource;
            }
        }

        return null;
    }

    private function toResourceName(string $className): string
    {
        $shortName = preg_replace('/^.*\\\\/', '', $className) ?? $className;
        $resource = preg_replace('/(?<!^)[A-Z]/', '-$0', $shortName) ?? $shortName;

        return strtolower($resource);
    }
}
