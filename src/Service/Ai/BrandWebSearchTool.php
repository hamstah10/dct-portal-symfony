<?php

declare(strict_types=1);

namespace App\Service\Ai;

use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Websuche über die Perplexity Search API. Gibt die besten Treffer mit Titel, URL und Auszug zurück.
 */
#[AsTool('web_search', description: 'Search the internet. Returns up to 8 results with title, url and a short snippet.', method: 'search')]
final class BrandWebSearchTool
{
    private const ENDPOINT = 'https://api.perplexity.ai/search';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(default::PERPLEXITY_API_KEY)%')] private readonly ?string $apiKey,
    ) {
    }

    /** @return list<array{title: string, url: string, snippet: string}> */
    public function search(string $query): array
    {
        if (null === $this->apiKey || '' === $this->apiKey) {
            return [['title' => 'Websuche nicht konfiguriert', 'url' => '', 'snippet' => 'PERPLEXITY_API_KEY fehlt.']];
        }

        $response = $this->httpClient->request('POST', self::ENDPOINT, [
            'headers' => ['Authorization' => 'Bearer '.$this->apiKey, 'Accept' => 'application/json'],
            'json' => ['query' => $query, 'max_results' => 8, 'country' => 'DE'],
            'timeout' => 20,
        ]);

        $data = $response->toArray(false);

        $results = [];
        foreach ($data['results'] ?? [] as $item) {
            $results[] = [
                'title' => (string) ($item['title'] ?? ''),
                'url' => (string) ($item['url'] ?? ''),
                'snippet' => strip_tags((string) ($item['snippet'] ?? '')),
            ];
        }

        return $results;
    }
}
