<?php

namespace App\Service;

use JsonException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class ScraperImportAnalyzer
{
    private const DEFAULT_OUTPUT_PATTERN = 'olsx_brands*.json';
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array<int,array{path:string,name:string,relative:string,publicPath:string,mtime:int,size:int}>
     */
    public function listJsonFiles(): array
    {
        return $this->listJsonFilesFromDirectory($this->projectDir . '/scrapy-remote-scraper');
    }

    /**
     * @return array<int,array{path:string,name:string,relative:string,publicPath:string,mtime:int,size:int}>
     */
    public function listJsonFilesFromDirectory(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = glob(rtrim($directory, '/') . '/' . self::DEFAULT_OUTPUT_PATTERN) ?: [];
        usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return array_map([$this, 'createMetadata'], $files);
    }

    /**
     * @return array<int,array{path:string,name:string,relative:string,publicPath:string,mtime:int,size:int}>
     */
    public function listJsonFilesRecursivelyFromDirectory(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                continue;
            }

            if (strtolower($file->getExtension()) !== 'json') {
                continue;
            }

            $files[] = $file->getPathname();
        }

        usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return array_map([$this, 'createMetadata'], $files);
    }

    /**
     * @throws JsonException
     */
    public function analyze(string $path): array
    {
        $content = file_get_contents($path);
        /** @var array<int,array|mixed>|array<string,mixed>|null $data */
        $data = null;
        if ($content) {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        }

        $brands = [];
        if (is_array($data)) {
            if ($this->looksLikeSingleBrandPayload($data)) {
                $data = [$data];
            }

            foreach ($data as $brand) {
                if (!is_array($brand)) {
                    continue;
                }
                $brands[] = [
                    'slug' => $brand['brand_slug'] ?? null,
                    'name' => $brand['brand_name'] ?? null,
                    'model_count' => $brand['model_count'] ?? 0,
                ];
            }
        }

        return [
            'type' => $this->typeFromPath($path),
            'brandCount' => count($brands),
            'brands' => $brands,
        ];
    }

    private function createMetadata(string $path): array
    {
        $relative = ltrim((string) str_replace(realpath($this->projectDir) ?: $this->projectDir, '', $path), '/');
        return [
            'path' => $path,
            'name' => basename($path),
            'relative' => $relative,
            'publicPath' => '/' . $relative,
            'mtime' => (int) filemtime($path),
            'size' => is_file($path) ? (int) filesize($path) : 0,
        ];
    }

    private function typeFromPath(string $path): ?string
    {
        if (preg_match('/olsx_brands_([a-z]+)_/', $path, $matches)) {
            return $matches[1];
        }

        $normalizedPath = str_replace('\\', '/', $path);
        if (preg_match('#/data/output/(?:[^/]+/)?([^/]+)/#', $normalizedPath, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param array<mixed> $data
     */
    private function looksLikeSingleBrandPayload(array $data): bool
    {
        return array_key_exists('brand_slug', $data)
            || array_key_exists('brand_name', $data)
            || array_key_exists('models', $data);
    }
}
