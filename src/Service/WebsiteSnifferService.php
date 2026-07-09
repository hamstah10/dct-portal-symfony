<?php

namespace App\Service;

use App\Entity\WebsiteSniffer;
use App\Entity\WebsiteSnifferPage;
use App\Entity\WebsiteSnifferRun;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class WebsiteSnifferService
{
    private const DEFAULT_MAX_ITEMS = 300;
    private const MAX_REMOTE_PAGES = 500;
    private const HTTP_TIMEOUT_SECONDS = 15;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WebsiteScreenshotService $websiteScreenshotService,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function run(WebsiteSniffer $sniffer): WebsiteSnifferRun
    {
        $run = $this->startRun($sniffer);

        return $this->executeRun($run);
    }

    public function startRun(WebsiteSniffer $sniffer): WebsiteSnifferRun
    {
        $startedAt = new \DateTimeImmutable();
        $run = (new WebsiteSnifferRun())
            ->setSniffer($sniffer)
            ->setRunAt($startedAt)
            ->setStartedAt($startedAt)
            ->setStatus(WebsiteSnifferRun::STATUS_RUNNING)
            ->setSourceDirectory($sniffer->getDirectoryPath())
            ->setPagesCount(0)
            ->setProcessedPages(0)
            ->setTotalPages(0)
            ->setErrorMessage(null);

        $sniffer
            ->setLastRunAt($startedAt)
            ->setLastRunStatus(WebsiteSnifferRun::STATUS_RUNNING)
            ->setLastRunPagesCount(0);

        $this->entityManager->persist($run);
        $this->entityManager->persist($sniffer);
        $this->entityManager->flush();

        return $run;
    }

    public function executeRun(WebsiteSnifferRun $run): WebsiteSnifferRun
    {
        $sniffer = $run->getSniffer();
        if (!$sniffer instanceof WebsiteSniffer) {
            throw new \RuntimeException('Sniffer-Konfiguration nicht gefunden.');
        }

        $startedAt = new \DateTimeImmutable();
        $run
            ->setStartedAt($startedAt)
            ->setFinishedAt(null)
            ->setStatus(WebsiteSnifferRun::STATUS_RUNNING)
            ->setPagesCount(0)
            ->setProcessedPages(0)
            ->setTotalPages(0)
            ->setErrorMessage(null);

        $this->entityManager->persist($run);
        $this->entityManager->persist($sniffer);
        $this->entityManager->flush();

        try {
            $source = trim($sniffer->getDirectoryPath());
            if ('' === $source) {
                throw new \RuntimeException('Kein Verzeichnis oder keine URL konfiguriert.');
            }

            $runId = $run->getId() ?? 0;
            $screenshotDirectory = sprintf('uploads/website-sniffer/%d', $runId);

            if ($this->isHttpUrl($source)) {
                $baseUrl = $this->normalizeHttpUrl($source);
                if (null === $baseUrl) {
                    throw new \RuntimeException(sprintf('URL ungueltig: %s', $source));
                }

                $run->setSourceDirectory($baseUrl);
                $localDirectoryFromUrl = $this->resolveLocalDirectoryFromUrl($baseUrl);
                if (null !== $localDirectoryFromUrl) {
                    $pagesCount = $this->crawlLocalPages(
                        $run,
                        $localDirectoryFromUrl,
                        $screenshotDirectory,
                        $sniffer->isIncludeSubdirectories(),
                        $baseUrl
                    );
                } else {
                    $pagesCount = $this->crawlRemotePages(
                        $run,
                        $baseUrl,
                        $screenshotDirectory,
                        $sniffer->isIncludeSubdirectories()
                    );
                }
            } else {
                $resolvedDirectory = $this->resolveDirectory($source);
                $run->setSourceDirectory($resolvedDirectory);
                $pagesCount = $this->crawlLocalPages(
                    $run,
                    $resolvedDirectory,
                    $screenshotDirectory,
                    $sniffer->isIncludeSubdirectories(),
                    null
                );
            }

            $finishedAt = new \DateTimeImmutable();
            $run
                ->setPagesCount($pagesCount)
                ->setProcessedPages(max($run->getProcessedPages(), $run->getTotalPages(), $pagesCount))
                ->setTotalPages(max($run->getTotalPages(), $run->getProcessedPages(), $pagesCount))
                ->setFinishedAt($finishedAt)
                ->setStatus(WebsiteSnifferRun::STATUS_SUCCESS)
                ->setErrorMessage(null);

            $sniffer
                ->setLastRunAt($finishedAt)
                ->setLastRunStatus(WebsiteSnifferRun::STATUS_SUCCESS)
                ->setLastRunPagesCount($pagesCount);
        } catch (\Throwable $throwable) {
            $finishedAt = new \DateTimeImmutable();
            $run
                ->setFinishedAt($finishedAt)
                ->setStatus(WebsiteSnifferRun::STATUS_FAILED)
                ->setErrorMessage($this->limitLength(trim($throwable->getMessage()), 65535));

            $sniffer
                ->setLastRunAt($finishedAt)
                ->setLastRunStatus(WebsiteSnifferRun::STATUS_FAILED)
                ->setLastRunPagesCount(0);
        }

        $this->entityManager->persist($sniffer);
        $this->entityManager->persist($run);
        $this->entityManager->flush();

        return $run;
    }

    private function crawlLocalPages(
        WebsiteSnifferRun $run,
        string $resolvedDirectory,
        string $screenshotDirectory,
        bool $includeSubdirectories,
        ?string $baseUrlForScreenshots = null
    ): int {
        $finder = new Finder();
        $finder
            ->files()
            ->in($resolvedDirectory)
            ->ignoreUnreadableDirs()
            ->name('/\.(html?|xhtml)$/i');

        if (!$includeSubdirectories) {
            $finder->depth('== 0');
        }

        /** @var array<int, SplFileInfo> $files */
        $files = iterator_to_array($finder->getIterator(), false);
        $totalFiles = count($files);
        $processedFiles = 0;

        $run
            ->setTotalPages($totalFiles)
            ->setProcessedPages(0)
            ->setPagesCount(0);
        $this->saveRunProgress($run);

        $pagesCount = 0;
        $seenPageKeys = [];
        foreach ($files as $file) {
            ++$processedFiles;

            $page = $this->createPageFromFile(
                $run,
                $file,
                $resolvedDirectory,
                $screenshotDirectory,
                $baseUrlForScreenshots
            );

            $pageKey = $this->buildPageDedupeKey($page->getFilePath(), $page->getRelativePath());
            if (!isset($seenPageKeys[$pageKey])) {
                $seenPageKeys[$pageKey] = true;
                $run->addPage($page);
                ++$pagesCount;
            }
            $run
                ->setPagesCount($pagesCount)
                ->setProcessedPages($processedFiles);
            $this->saveRunProgress($run);
        }

        return $pagesCount;
    }

    private function crawlRemotePages(
        WebsiteSnifferRun $run,
        string $baseUrl,
        string $screenshotDirectory,
        bool $includeSubdirectories
    ): int {
        $pagesCount = 0;
        $processedCandidates = 0;
        $queue = [$baseUrl];
        $queuedSet = [$baseUrl => true];
        $visited = [];
        $seenPageKeys = [];

        $run
            ->setProcessedPages(0)
            ->setTotalPages(1)
            ->setPagesCount(0);
        $this->saveRunProgress($run);

        while ([] !== $queue && $pagesCount < self::MAX_REMOTE_PAGES) {
            $currentUrl = array_shift($queue);
            if (!is_string($currentUrl) || '' === $currentUrl) {
                continue;
            }

            unset($queuedSet[$currentUrl]);

            if (isset($visited[$currentUrl])) {
                continue;
            }
            $visited[$currentUrl] = true;
            ++$processedCandidates;

            $download = $this->downloadRemoteHtml($currentUrl);
            if (null === $download) {
                $run
                    ->setProcessedPages($processedCandidates)
                    ->setTotalPages($this->resolveRemoteTotalPages($processedCandidates, count($queue)))
                    ->setPagesCount($pagesCount);
                $this->saveRunProgress($run);

                continue;
            }

            $parsed = $this->parseHtml($download['html']);
            $screenshotPath = $this->websiteScreenshotService->capture($currentUrl, $screenshotDirectory);

            $page = (new WebsiteSnifferPage())
                ->setRun($run)
                ->setRelativePath($this->buildRelativeUrlPath($currentUrl, $baseUrl))
                ->setFilePath($currentUrl)
                ->setTitle($parsed['title'])
                ->setH1($parsed['h1'])
                ->setMetaDescription($parsed['metaDescription'])
                ->setHtmlContent($download['html'])
                ->setParsedData($parsed['parsedData'])
                ->setBodyTextLength($parsed['bodyTextLength'])
                ->setFileSize($download['size'])
                ->setScreenshotPath($screenshotPath);

            $pageKey = $this->buildPageDedupeKey($page->getFilePath(), $page->getRelativePath());
            if (isset($seenPageKeys[$pageKey])) {
                $run
                    ->setProcessedPages($processedCandidates)
                    ->setTotalPages($this->resolveRemoteTotalPages($processedCandidates, count($queue)))
                    ->setPagesCount($pagesCount);
                $this->saveRunProgress($run);

                continue;
            }
            $seenPageKeys[$pageKey] = true;

            $run->addPage($page);
            ++$pagesCount;

            foreach ($this->extractCrawlCandidates($parsed['parsedData']) as $candidateHref) {
                $candidateUrl = $this->normalizeDiscoveredUrl($candidateHref, $currentUrl);
                if (null === $candidateUrl) {
                    continue;
                }

                if (!$this->isUrlInsideBase($candidateUrl, $baseUrl, $includeSubdirectories)) {
                    continue;
                }

                if (isset($visited[$candidateUrl]) || isset($queuedSet[$candidateUrl])) {
                    continue;
                }

                $queue[] = $candidateUrl;
                $queuedSet[$candidateUrl] = true;
            }

            $run
                ->setProcessedPages($processedCandidates)
                ->setTotalPages($this->resolveRemoteTotalPages($processedCandidates, count($queue)))
                ->setPagesCount($pagesCount);
            $this->saveRunProgress($run);
        }

        $run
            ->setProcessedPages($this->resolveRemoteTotalPages($processedCandidates, 0))
            ->setTotalPages($this->resolveRemoteTotalPages($processedCandidates, 0))
            ->setPagesCount($pagesCount);
        $this->saveRunProgress($run);

        return $pagesCount;
    }

    private function saveRunProgress(WebsiteSnifferRun $run): void
    {
        $this->entityManager->persist($run);
        $this->entityManager->flush();
    }

    private function resolveRemoteTotalPages(int $processedCandidates, int $queuedCount): int
    {
        return min(self::MAX_REMOTE_PAGES, max(0, $processedCandidates + max(0, $queuedCount)));
    }

    private function createPageFromFile(
        WebsiteSnifferRun $run,
        SplFileInfo $file,
        string $baseDirectory,
        string $screenshotDirectory,
        ?string $baseUrlForScreenshots = null
    ): WebsiteSnifferPage {
        $absolutePath = $this->normalizePath($file->getRealPath() ?: $file->getPathname());
        $relativePath = $this->buildRelativePath($absolutePath, $baseDirectory);
        $pageUrl = null;
        if (null !== $baseUrlForScreenshots) {
            $pageUrl = $this->buildPageUrlFromBase($baseUrlForScreenshots, $relativePath);
        }

        $content = @file_get_contents($absolutePath);
        $html = false === $content ? '' : $content;
        $parsed = $this->parseHtml($html);

        $screenshotPath = null;
        if ('' !== trim($html)) {
            if (null !== $pageUrl) {
                $screenshotPath = $this->websiteScreenshotService->capture($pageUrl, $screenshotDirectory);
            }
            if (null === $screenshotPath) {
                $screenshotPath = $this->websiteScreenshotService->captureLocalFile($absolutePath, $screenshotDirectory);
            }
        }

        return (new WebsiteSnifferPage())
            ->setRun($run)
            ->setRelativePath($relativePath)
            ->setFilePath($pageUrl ?? $absolutePath)
            ->setTitle($parsed['title'])
            ->setH1($parsed['h1'])
            ->setMetaDescription($parsed['metaDescription'])
            ->setHtmlContent($html)
            ->setParsedData($parsed['parsedData'])
            ->setBodyTextLength($parsed['bodyTextLength'])
            ->setFileSize($file->getSize())
            ->setScreenshotPath($screenshotPath);
    }

    private function buildPageDedupeKey(string $filePath, string $relativePath): string
    {
        $normalizedRelative = trim($relativePath);
        if ('' !== $normalizedRelative) {
            $normalizedRelative = (string) preg_replace('/[#?].*$/', '', str_replace('\\', '/', $normalizedRelative));
            $normalizedRelative = strtolower(trim($normalizedRelative, '/'));
            if ('' !== $normalizedRelative) {
                return 'rel:' . $normalizedRelative;
            }
        }

        $trimmedFilePath = trim($filePath);
        if ($this->isHttpUrl($trimmedFilePath)) {
            $parts = parse_url($trimmedFilePath);
            if (is_array($parts)) {
                $scheme = strtolower((string) ($parts['scheme'] ?? ''));
                $host = strtolower((string) ($parts['host'] ?? ''));
                $path = $this->normalizeUrlPath((string) ($parts['path'] ?? '/'));

                return sprintf('url:%s://%s%s', $scheme, $host, $path);
            }
        }

        return 'file:' . strtolower(str_replace('\\', '/', $trimmedFilePath));
    }

    /**
     * @return array{html:string,size:int}|null
     */
    private function downloadRemoteHtml(string $url): ?array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => self::HTTP_TIMEOUT_SECONDS,
                'header' => "User-Agent: TFD-Sniffer/1.0\r\nAccept: text/html,application/xhtml+xml\r\n",
                'ignore_errors' => true,
                'follow_location' => 1,
                'max_redirects' => 5,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);
        if (false === $content) {
            return null;
        }

        /** @var array<int, string> $headers */
        $headers = $http_response_header ?? [];
        if (!$this->isHtmlResponse($url, $headers, $content)) {
            return null;
        }

        return [
            'html' => $content,
            'size' => strlen($content),
        ];
    }

    /**
     * @param array<int, string> $headers
     */
    private function isHtmlResponse(string $url, array $headers, string $content): bool
    {
        $statusCode = null;
        foreach ($headers as $headerLine) {
            if (preg_match('/^HTTP\/\d+(?:\.\d+)?\s+(\d{3})/i', trim($headerLine), $matches)) {
                $statusCode = (int) $matches[1];
                break;
            }
        }

        if (null !== $statusCode && ($statusCode < 200 || $statusCode >= 400)) {
            return false;
        }

        foreach ($headers as $headerLine) {
            if (str_starts_with(strtolower($headerLine), 'content-type:')) {
                $contentType = strtolower($headerLine);
                if (str_contains($contentType, 'text/html') || str_contains($contentType, 'application/xhtml+xml')) {
                    return true;
                }

                return false;
            }
        }

        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        if ('' !== $path && preg_match('/\.(html?|xhtml)$/', $path)) {
            return true;
        }

        return str_contains(strtolower(substr($content, 0, 2048)), '<html');
    }

    /**
     * @param array<string, mixed> $parsedData
     *
     * @return array<int, string>
     */
    private function extractCrawlCandidates(array $parsedData): array
    {
        $links = $parsedData['links'] ?? null;
        if (!is_array($links)) {
            return [];
        }

        $result = [];
        $seen = [];

        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }

            $href = trim((string) ($link['href'] ?? ''));
            if ('' === $href || isset($seen[$href])) {
                continue;
            }

            $seen[$href] = true;
            $result[] = $href;
        }

        return $result;
    }

    private function normalizeDiscoveredUrl(string $href, string $currentUrl): ?string
    {
        $href = trim($href);
        if ('' === $href) {
            return null;
        }

        $lowerHref = strtolower($href);
        if (str_starts_with($lowerHref, '#')
            || str_starts_with($lowerHref, 'javascript:')
            || str_starts_with($lowerHref, 'mailto:')
            || str_starts_with($lowerHref, 'tel:')
            || str_starts_with($lowerHref, 'data:')) {
            return null;
        }

        if (str_starts_with($href, '//')) {
            $scheme = (string) parse_url($currentUrl, PHP_URL_SCHEME);
            if ('' === $scheme) {
                return null;
            }

            return $this->normalizeHttpUrl($scheme . ':' . $href);
        }

        if ($this->isHttpUrl($href)) {
            return $this->normalizeHttpUrl($href);
        }

        $current = parse_url($currentUrl);
        if (!is_array($current)) {
            return null;
        }

        $scheme = strtolower((string) ($current['scheme'] ?? ''));
        $host = strtolower((string) ($current['host'] ?? ''));
        if ('' === $scheme || '' === $host) {
            return null;
        }

        $port = isset($current['port']) ? (int) $current['port'] : null;
        $authority = $host;
        if (null !== $port && !in_array([$scheme, $port], [['http', 80], ['https', 443]], true)) {
            $authority .= ':' . $port;
        }

        $hrefParts = parse_url($href);
        if (false === $hrefParts) {
            return null;
        }

        $query = isset($hrefParts['query']) ? (string) $hrefParts['query'] : '';
        $pathPart = (string) ($hrefParts['path'] ?? '');

        if (str_starts_with($href, '/')) {
            $path = $this->normalizeUrlPath($pathPart);
        } else {
            $currentPath = (string) ($current['path'] ?? '/');
            if ('' === $currentPath) {
                $currentPath = '/';
            }

            $basePath = str_ends_with($currentPath, '/')
                ? $currentPath
                : (string) preg_replace('#/[^/]*$#', '/', $currentPath);
            if ('' === $basePath) {
                $basePath = '/';
            }

            $path = $this->normalizeUrlPath($basePath . $pathPart);
        }

        $candidate = sprintf('%s://%s%s', $scheme, $authority, $path);
        if ('' !== $query) {
            $candidate .= '?' . $query;
        }

        return $this->normalizeHttpUrl($candidate);
    }

    private function isUrlInsideBase(string $candidateUrl, string $baseUrl, bool $includeSubdirectories): bool
    {
        $candidate = parse_url($candidateUrl);
        $base = parse_url($baseUrl);

        if (!is_array($candidate) || !is_array($base)) {
            return false;
        }

        if (strtolower((string) ($candidate['scheme'] ?? '')) !== strtolower((string) ($base['scheme'] ?? ''))) {
            return false;
        }

        if (strtolower((string) ($candidate['host'] ?? '')) !== strtolower((string) ($base['host'] ?? ''))) {
            return false;
        }

        $candidatePort = isset($candidate['port']) ? (int) $candidate['port'] : null;
        $basePort = isset($base['port']) ? (int) $base['port'] : null;
        if ($candidatePort !== $basePort) {
            $resolvedCandidatePort = $candidatePort ?? (($candidate['scheme'] ?? '') === 'https' ? 443 : 80);
            $resolvedBasePort = $basePort ?? (($base['scheme'] ?? '') === 'https' ? 443 : 80);
            if ($resolvedCandidatePort !== $resolvedBasePort) {
                return false;
            }
        }

        $basePath = $this->normalizeUrlPath((string) ($base['path'] ?? '/'));
        $candidatePath = $this->normalizeUrlPath((string) ($candidate['path'] ?? '/'));

        if ('/' === $basePath) {
            return true;
        }

        if ($candidatePath !== $basePath && !str_starts_with($candidatePath, $basePath . '/')) {
            return false;
        }

        if ($includeSubdirectories) {
            return true;
        }

        if ($candidatePath === $basePath) {
            return true;
        }

        $relative = ltrim(substr($candidatePath, strlen($basePath)), '/');

        return '' !== $relative && !str_contains($relative, '/');
    }

    private function buildRelativeUrlPath(string $url, string $baseUrl): string
    {
        $urlParts = parse_url($url);
        $baseParts = parse_url($baseUrl);

        if (!is_array($urlParts) || !is_array($baseParts)) {
            return $url;
        }

        $urlPath = $this->normalizeUrlPath((string) ($urlParts['path'] ?? '/'));
        $basePath = $this->normalizeUrlPath((string) ($baseParts['path'] ?? '/'));

        $relative = null;
        if ('/' === $basePath) {
            $relative = ltrim($urlPath, '/');
        } elseif ($urlPath === $basePath) {
            $relative = '';
        } elseif (str_starts_with($urlPath, $basePath . '/')) {
            $relative = ltrim(substr($urlPath, strlen($basePath)), '/');
        }

        if (null === $relative) {
            $relative = ltrim($urlPath, '/');
        }

        if ('' === $relative) {
            $relative = 'index';
        }

        $query = (string) ($urlParts['query'] ?? '');
        if ('' !== $query) {
            $relative .= '?' . $query;
        }

        return $relative;
    }

    private function isHttpUrl(string $value): bool
    {
        $scheme = strtolower((string) parse_url(trim($value), PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }

    private function normalizeHttpUrl(string $url): ?string
    {
        $parts = parse_url(trim($url));
        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if ('' === $scheme || '' === $host || !in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        $isDefaultPort = ('http' === $scheme && 80 === $port) || ('https' === $scheme && 443 === $port);

        $path = $this->normalizeUrlPath((string) ($parts['path'] ?? '/'));
        $query = (string) ($parts['query'] ?? '');

        $normalized = $scheme . '://' . $host;
        if (null !== $port && !$isDefaultPort) {
            $normalized .= ':' . $port;
        }

        $normalized .= $path;

        if ('' !== $query) {
            $normalized .= '?' . $query;
        }

        return $normalized;
    }

    private function normalizeUrlPath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        if ('' === $path) {
            return '/';
        }

        $leadingSlash = str_starts_with($path, '/');
        $segments = explode('/', $path);
        $stack = [];

        foreach ($segments as $segment) {
            if ('' === $segment || '.' === $segment) {
                continue;
            }

            if ('..' === $segment) {
                array_pop($stack);
                continue;
            }

            $stack[] = $segment;
        }

        $normalized = ($leadingSlash ? '/' : '') . implode('/', $stack);
        if ('' === $normalized) {
            $normalized = '/';
        }

        if ('/' !== $normalized && str_ends_with($path, '/')) {
            $normalized = rtrim($normalized, '/');
        }

        return $normalized;
    }

    private function resolveLocalDirectoryFromUrl(string $url): ?string
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return null;
        }

        $path = rawurldecode((string) ($parts['path'] ?? '/'));
        $normalizedPath = $this->normalizeUrlPath($path);

        $candidate = $this->projectDir . '/public';
        $trimmedPath = trim($normalizedPath, '/');
        if ('' !== $trimmedPath) {
            $candidate .= '/' . $trimmedPath;
        }

        $resolved = realpath($candidate);
        if (false === $resolved || !is_dir($resolved) || !is_readable($resolved)) {
            return null;
        }

        return rtrim($this->normalizePath($resolved), '/');
    }

    private function buildPageUrlFromBase(string $baseUrl, string $relativePath): ?string
    {
        $parts = parse_url($baseUrl);
        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if ('' === $scheme || '' === $host || !in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $authority = $host;
        $port = isset($parts['port']) ? (int) $parts['port'] : null;
        if (null !== $port && !in_array([$scheme, $port], [['http', 80], ['https', 443]], true)) {
            $authority .= ':' . $port;
        }

        $basePath = $this->normalizeUrlPath((string) ($parts['path'] ?? '/'));
        $basePath = '/' === $basePath ? '/' : rtrim($basePath, '/');

        $relativePath = ltrim(str_replace('\\', '/', trim($relativePath)), '/');
        $fullPath = '/' === $basePath ? '/' . $relativePath : $basePath . '/' . $relativePath;
        $fullPath = $this->normalizeUrlPath($fullPath);

        return sprintf('%s://%s%s', $scheme, $authority, $fullPath);
    }

    private function resolveDirectory(string $directoryPath): string
    {
        $directoryPath = trim($directoryPath);
        if ('' === $directoryPath) {
            throw new \RuntimeException('Kein Verzeichnis konfiguriert.');
        }

        $candidate = $directoryPath;
        if (!$this->isAbsolutePath($directoryPath)) {
            $candidate = $this->projectDir . '/' . ltrim($directoryPath, '/');
        }

        $resolved = realpath($candidate);
        if (false === $resolved || !is_dir($resolved)) {
            throw new \RuntimeException(sprintf('Verzeichnis nicht gefunden: %s', $directoryPath));
        }

        if (!is_readable($resolved)) {
            throw new \RuntimeException(sprintf('Verzeichnis nicht lesbar: %s', $resolved));
        }

        return rtrim($this->normalizePath($resolved), '/');
    }

    private function isAbsolutePath(string $path): bool
    {
        if (str_starts_with($path, '/')) {
            return true;
        }

        return (bool) preg_match('/^[A-Za-z]:[\/\\\\]/', $path);
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    private function buildRelativePath(string $absolutePath, string $baseDirectory): string
    {
        $absolutePath = $this->normalizePath($absolutePath);
        $baseDirectory = rtrim($this->normalizePath($baseDirectory), '/');

        if (str_starts_with($absolutePath, $baseDirectory . '/')) {
            return ltrim(substr($absolutePath, strlen($baseDirectory)), '/');
        }

        return basename($absolutePath);
    }

    /**
     * @return array{title:?string,h1:?string,metaDescription:?string,bodyTextLength:?int,parsedData:array<string,mixed>}
     */
    private function parseHtml(string $html): array
    {
        $emptyParsedData = [
            'meta' => [],
            'headings' => [],
            'assets' => [
                'images' => [],
                'scripts' => [],
                'stylesheets' => [],
            ],
            'links' => [],
            'forms' => [],
            'tagCounts' => [],
            'content' => [
                'htmlLength' => mb_strlen($html),
                'bodyTextLength' => null,
            ],
        ];

        $empty = [
            'title' => null,
            'h1' => null,
            'metaDescription' => null,
            'bodyTextLength' => null,
            'parsedData' => $emptyParsedData,
        ];

        if ('' === trim($html)) {
            return $empty;
        }

        $dom = new \DOMDocument();
        $previousUseInternalErrors = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseInternalErrors);

        if (!$loaded) {
            return $empty;
        }

        $xpath = new \DOMXPath($dom);

        $title = $this->limitLength($this->toNullableString($xpath->evaluate('string(//title[1])')), 500);
        $h1 = $this->limitLength($this->toNullableString($xpath->evaluate('string(//h1[1])')), 500);
        $metaDescription = $this->toNullableString(
            $xpath->evaluate('string(//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="description"][1]/@content)')
        );
        $metaKeywords = $this->toNullableString(
            $xpath->evaluate('string(//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="keywords"][1]/@content)')
        );
        $metaRobots = $this->toNullableString(
            $xpath->evaluate('string(//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="robots"][1]/@content)')
        );

        $bodyText = $this->toNullableString($xpath->evaluate('string(//body[1])'));
        $bodyTextLength = null;
        if (null !== $bodyText) {
            $bodyTextLength = mb_strlen($bodyText);
        }

        $headings = [];
        for ($i = 1; $i <= 6; ++$i) {
            $headings['h' . $i] = $this->extractTextValues($xpath, sprintf('//h%d', $i), self::DEFAULT_MAX_ITEMS);
        }

        $parsedData = [
            'meta' => [
                'description' => $metaDescription,
                'keywords' => $metaKeywords,
                'robots' => $metaRobots,
            ],
            'headings' => $headings,
            'assets' => [
                'images' => $this->extractAttributeValues($xpath, '//img[@src]', 'src', self::DEFAULT_MAX_ITEMS),
                'scripts' => $this->extractAttributeValues($xpath, '//script[@src]', 'src', self::DEFAULT_MAX_ITEMS),
                'stylesheets' => $this->extractAttributeValues(
                    $xpath,
                    '//link[@href and contains(translate(@rel, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "stylesheet")]',
                    'href',
                    self::DEFAULT_MAX_ITEMS
                ),
            ],
            'links' => $this->extractLinks($xpath, self::DEFAULT_MAX_ITEMS),
            'forms' => $this->extractForms($xpath, self::DEFAULT_MAX_ITEMS),
            'tagCounts' => $this->extractTagCounts($dom),
            'content' => [
                'htmlLength' => mb_strlen($html),
                'bodyTextLength' => $bodyTextLength,
            ],
        ];

        return [
            'title' => $title,
            'h1' => $h1,
            'metaDescription' => $metaDescription,
            'bodyTextLength' => $bodyTextLength,
            'parsedData' => $parsedData,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function extractTextValues(\DOMXPath $xpath, string $query, int $maxItems): array
    {
        $nodes = $xpath->query($query);
        if (false === $nodes) {
            return [];
        }

        $results = [];
        $seen = [];
        foreach ($nodes as $node) {
            if (!($node instanceof \DOMElement)) {
                continue;
            }

            $value = $this->toNullableString($node->textContent);
            if (null === $value) {
                continue;
            }

            if (isset($seen[$value])) {
                continue;
            }

            $seen[$value] = true;
            $results[] = $value;
            if (count($results) >= $maxItems) {
                break;
            }
        }

        return $results;
    }

    /**
     * @return array<int, string>
     */
    private function extractAttributeValues(\DOMXPath $xpath, string $query, string $attribute, int $maxItems): array
    {
        $nodes = $xpath->query($query);
        if (false === $nodes) {
            return [];
        }

        $results = [];
        $seen = [];
        foreach ($nodes as $node) {
            if (!($node instanceof \DOMElement)) {
                continue;
            }

            $value = $this->toNullableString($node->getAttribute($attribute));
            if (null === $value) {
                continue;
            }

            if (isset($seen[$value])) {
                continue;
            }

            $seen[$value] = true;
            $results[] = $value;
            if (count($results) >= $maxItems) {
                break;
            }
        }

        return $results;
    }

    /**
     * @return array<int, array{href:string,text:?string}>
     */
    private function extractLinks(\DOMXPath $xpath, int $maxItems): array
    {
        $nodes = $xpath->query('//a[@href]');
        if (false === $nodes) {
            return [];
        }

        $results = [];
        $seen = [];
        foreach ($nodes as $node) {
            if (!($node instanceof \DOMElement)) {
                continue;
            }

            $href = $this->toNullableString($node->getAttribute('href'));
            if (null === $href) {
                continue;
            }

            $text = $this->toNullableString($node->textContent);
            $key = $href . '|' . ($text ?? '');
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $results[] = [
                'href' => $href,
                'text' => $text,
            ];

            if (count($results) >= $maxItems) {
                break;
            }
        }

        return $results;
    }

    /**
     * @return array<int, array{action:?string,method:string,inputCount:int}>
     */
    private function extractForms(\DOMXPath $xpath, int $maxItems): array
    {
        $nodes = $xpath->query('//form');
        if (false === $nodes) {
            return [];
        }

        $results = [];
        foreach ($nodes as $node) {
            if (!($node instanceof \DOMElement)) {
                continue;
            }

            $action = $this->toNullableString($node->getAttribute('action'));
            $method = strtolower(trim((string) $node->getAttribute('method')));
            if ('' === $method) {
                $method = 'get';
            }

            $inputCount = 0;
            foreach ($node->getElementsByTagName('input') as $inputNode) {
                if ($inputNode instanceof \DOMElement) {
                    ++$inputCount;
                }
            }

            $results[] = [
                'action' => $action,
                'method' => $method,
                'inputCount' => $inputCount,
            ];

            if (count($results) >= $maxItems) {
                break;
            }
        }

        return $results;
    }

    /**
     * @return array<string, int>
     */
    private function extractTagCounts(\DOMDocument $dom): array
    {
        $counts = [];
        foreach ($dom->getElementsByTagName('*') as $node) {
            if (!($node instanceof \DOMElement)) {
                continue;
            }

            $tag = strtolower($node->tagName);
            if (!isset($counts[$tag])) {
                $counts[$tag] = 0;
            }

            ++$counts[$tag];
        }

        ksort($counts);

        return $counts;
    }

    private function toNullableString(mixed $value): ?string
    {
        $stringValue = trim((string) ($value ?? ''));
        if ('' === $stringValue) {
            return null;
        }

        $normalized = preg_replace('/\s+/u', ' ', $stringValue);
        $normalizedString = null === $normalized ? $stringValue : trim($normalized);

        return '' === $normalizedString ? null : $normalizedString;
    }

    private function limitLength(?string $value, int $maxLength): ?string
    {
        if (null === $value) {
            return null;
        }

        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $maxLength));
    }
}
