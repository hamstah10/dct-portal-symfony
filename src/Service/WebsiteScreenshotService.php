<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class WebsiteScreenshotService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function capture(string $url, string $relativeDirectory): ?string
    {
        [$relativePath, $absolutePath] = $this->prepareOutputPath($relativeDirectory);

        if ($this->captureWithChromium($url, $absolutePath) || $this->captureWithMShots($url, $absolutePath)) {
            return $relativePath;
        }

        return null;
    }

    public function captureLocalFile(string $absoluteFilePath, string $relativeDirectory): ?string
    {
        $resolvedFilePath = realpath($absoluteFilePath);
        if (false === $resolvedFilePath || !is_file($resolvedFilePath) || !is_readable($resolvedFilePath)) {
            return null;
        }

        [$relativePath, $absolutePath] = $this->prepareOutputPath($relativeDirectory);
        $fileUrl = $this->toFileUrl($resolvedFilePath);

        if ($this->captureWithChromium($fileUrl, $absolutePath)) {
            return $relativePath;
        }

        return null;
    }

    /**
     * @return array{0:string,1:string}
     */
    private function prepareOutputPath(string $relativeDirectory): array
    {
        $relativeDirectory = trim($relativeDirectory, '/');
        $absoluteDirectory = $this->projectDir . '/public/' . $relativeDirectory;
        if (!is_dir($absoluteDirectory)) {
            mkdir($absoluteDirectory, 0775, true);
        }

        $fileName = sprintf('auto-shot-%s-%s.jpg', date('YmdHis'), bin2hex(random_bytes(4)));
        $relativePath = $relativeDirectory . '/' . $fileName;
        $absolutePath = $absoluteDirectory . '/' . $fileName;

        return [$relativePath, $absolutePath];
    }

    private function captureWithChromium(string $url, string $outputPath): bool
    {
        foreach (['chromium-browser', 'chromium', 'google-chrome', 'google-chrome-stable'] as $binary) {
            $binaryPath = $this->findBinary($binary);
            if (null === $binaryPath) {
                continue;
            }

            $command = sprintf(
                '%s --headless --no-sandbox --disable-dev-shm-usage --disable-gpu --hide-scrollbars --allow-file-access-from-files --virtual-time-budget=12000 --screenshot=%s --window-size=1366,768 %s 2>/dev/null',
                escapeshellarg($binaryPath),
                escapeshellarg($outputPath),
                escapeshellarg($url)
            );

            exec($command, $output, $exitCode);
            if (is_file($outputPath) && filesize($outputPath) > 0) {
                return true;
            }
        }

        return false;
    }

    private function toFileUrl(string $absoluteFilePath): string
    {
        $normalized = str_replace('\\', '/', $absoluteFilePath);

        if (preg_match('/^[A-Za-z]:\\//', $normalized)) {
            $drive = strtoupper(substr($normalized, 0, 1));
            $rest = ltrim(substr($normalized, 2), '/');
            $segments = '' === $rest ? [] : array_map('rawurlencode', explode('/', $rest));

            return sprintf(
                'file:///%s:%s',
                $drive,
                [] === $segments ? '' : '/' . implode('/', $segments)
            );
        }

        $trimmed = ltrim($normalized, '/');
        $segments = '' === $trimmed ? [] : array_map('rawurlencode', explode('/', $trimmed));

        return 'file:///' . implode('/', $segments);
    }

    private function captureWithMShots(string $url, string $outputPath): bool
    {
        $screenshotUrl = 'https://s.wordpress.com/mshots/v1/' . rawurlencode($url) . '?w=1366';
        $content = @file_get_contents($screenshotUrl);
        if (false === $content || '' === $content) {
            return false;
        }

        return false !== @file_put_contents($outputPath, $content);
    }

    private function findBinary(string $binary): ?string
    {
        exec(sprintf('command -v %s 2>/dev/null', escapeshellarg($binary)), $output, $exitCode);
        if (0 !== $exitCode || [] === $output) {
            return null;
        }

        $path = trim((string) $output[0]);

        return '' === $path ? null : $path;
    }
}
