<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class BrandMasterImageResolver
{
    private readonly AsciiSlugger $slugger;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        $this->slugger = new AsciiSlugger();
    }

    public function resolve(?string $slug, string $name, ?string ...$fallbacks): ?string
    {
        foreach ($this->buildCandidates($slug, $name) as $candidate) {
            $absolutePath = sprintf('%s/%s', $this->projectDir, $candidate);
            if (is_file($absolutePath)) {
                return $candidate;
            }
        }

        foreach ($fallbacks as $fallback) {
            $normalized = $this->nullableString($fallback);
            if (null !== $normalized) {
                return $normalized;
            }
        }

        return null;
    }

    public function resolveForType(?string $vehicleTypeSlug, ?string $slug, string $name, ?string ...$fallbacks): ?string
    {
        foreach ($this->buildCandidates($slug, $name, $vehicleTypeSlug) as $candidate) {
            $absolutePath = sprintf('%s/%s', $this->projectDir, $candidate);
            if (is_file($absolutePath)) {
                return $candidate;
            }
        }

        foreach ($fallbacks as $fallback) {
            $normalized = $this->nullableString($fallback);
            if (null !== $normalized) {
                return $normalized;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function buildCandidates(?string $slug, string $name, ?string $vehicleTypeSlug = null): array
    {
        $keys = [];

        $normalizedSlug = $this->normalizeSlug($slug);
        if (null !== $normalizedSlug) {
            $keys[] = $normalizedSlug;
            foreach ($this->stripTypeSuffixes($normalizedSlug) as $strippedSlug) {
                $keys[] = $strippedSlug;
            }
        }

        $normalizedName = $this->normalizeSlug((string) $this->slugger->slug($name)->toString());
        if (null !== $normalizedName) {
            $keys[] = $normalizedName;
        }

        $aliases = [
            'lannen' => 'laennen',
            'lynk-co' => 'lynk-and-co',
            'mercedes' => 'mercedes-benz',
        ];

        $paths = [];
        $tuningType = $this->mapVehicleTypeToTuningLogoFolder($vehicleTypeSlug);
        $candidateKeys = array_unique(array_filter($keys));
        foreach ($candidateKeys as $key) {
            if (isset($aliases[$key])) {
                $candidateKeys[] = $aliases[$key];
            }
        }

        foreach (array_unique($candidateKeys) as $key) {
            if (null !== $tuningType) {
                foreach (['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif', 'avif'] as $extension) {
                    $paths[] = sprintf('assets/icons/brands-tuning/%s/%s.%s', $tuningType, $key, $extension);
                }
            }
            $paths[] = 'assets/icons/brands-cars/Icons/' . $key . '.svg';
            if (isset($aliases[$key])) {
                $paths[] = 'assets/icons/brands-cars/Icons/' . $aliases[$key] . '.svg';
            }
        }

        return $paths;
    }

    private function normalizeSlug(?string $value): ?string
    {
        $normalized = strtolower(trim((string) ($value ?? '')));

        return '' !== $normalized ? $normalized : null;
    }

    /**
     * @return array<int, string>
     */
    private function stripTypeSuffixes(string $value): array
    {
        $stripped = [];
        foreach (['-agri', '-truck', '-trucks', '-moto', '-motorcycle', '-atv', '-boat'] as $suffix) {
            if (str_ends_with($value, $suffix)) {
                $stripped[] = substr($value, 0, -strlen($suffix));
            }
        }

        return array_values(array_unique(array_filter($stripped)));
    }

    private function mapVehicleTypeToTuningLogoFolder(?string $vehicleTypeSlug): ?string
    {
        return match ($this->normalizeSlug($vehicleTypeSlug)) {
            'pkw' => 'car',
            'lkw' => 'truck',
            'motorrad' => 'moto',
            'agrar' => 'agricultural',
            'atv' => 'atv',
            'boat' => 'boat',
            default => null,
        };
    }

    private function nullableString(?string $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return '' !== $normalized ? $normalized : null;
    }
}
