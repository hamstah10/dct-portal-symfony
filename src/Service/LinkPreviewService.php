<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Laedt eine Webseite und extrahiert Titel und Vorschaubild (OpenGraph/Twitter/HTML-Fallback).
 */
class LinkPreviewService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @return array{title: ?string, image: ?string}
     */
    public function fetch(string $url): array
    {
        $response = $this->httpClient->request('GET', $url, [
            'timeout' => 10,
            'max_duration' => 15,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'de-DE,de;q=0.9,en;q=0.8',
            ],
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException(sprintf('Seite antwortete mit HTTP %d.', $response->getStatusCode()));
        }

        // Nur die ersten 512 KB laden - Metadaten stehen im <head>.
        $html = '';
        foreach ($this->httpClient->stream($response) as $chunk) {
            $html .= $chunk->getContent();
            if (strlen($html) > 512 * 1024) {
                $response->cancel();
                break;
            }
        }

        $title = $this->firstNonEmpty([
            $this->extractMetaContent($html, 'og:title'),
            $this->extractMetaContent($html, 'twitter:title'),
            $this->extractTitleTag($html),
        ]);

        $image = $this->firstNonEmpty([
            $this->extractMetaContent($html, 'og:image'),
            $this->extractMetaContent($html, 'og:image:url'),
            $this->extractMetaContent($html, 'twitter:image'),
        ]);

        if (null !== $image) {
            $image = $this->resolveUrl($image, $url);
        }

        return [
            'title' => $title,
            'image' => $image,
        ];
    }

    private function extractMetaContent(string $html, string $name): ?string
    {
        $quotedName = preg_quote($name, '/');
        $patterns = [
            '/<meta[^>]+(?:property|name)\s*=\s*["\']' . $quotedName . '["\'][^>]+content\s*=\s*["\']([^"\']+)["\']/i',
            '/<meta[^>]+content\s*=\s*["\']([^"\']+)["\'][^>]+(?:property|name)\s*=\s*["\']' . $quotedName . '["\']/i',
        ];

        foreach ($patterns as $pattern) {
            if (1 === preg_match($pattern, $html, $matches)) {
                return $this->cleanText($matches[1]);
            }
        }

        return null;
    }

    private function extractTitleTag(string $html): ?string
    {
        if (1 === preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return $this->cleanText($matches[1]);
        }

        return null;
    }

    private function cleanText(string $value): ?string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = trim((string) preg_replace('/\s+/', ' ', $value));

        return '' !== $value ? $value : null;
    }

    private function resolveUrl(string $candidate, string $baseUrl): ?string
    {
        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
            return $candidate;
        }

        $parts = parse_url($baseUrl);
        if (false === $parts || !isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');

        if (str_starts_with($candidate, '//')) {
            return $parts['scheme'] . ':' . $candidate;
        }
        if (str_starts_with($candidate, '/')) {
            return $origin . $candidate;
        }

        $basePath = $parts['path'] ?? '/';
        $baseDir = rtrim(substr($basePath, 0, (int) strrpos($basePath, '/')), '/');

        return $origin . $baseDir . '/' . $candidate;
    }

    /**
     * @param array<?string> $values
     */
    private function firstNonEmpty(array $values): ?string
    {
        foreach ($values as $value) {
            if (null !== $value && '' !== $value) {
                return $value;
            }
        }

        return null;
    }
}
