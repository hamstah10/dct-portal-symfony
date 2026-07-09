<?php

declare(strict_types=1);

namespace Tuningfux\InstagramBundle\Service;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tuningfux\InstagramBundle\Exception\InstagramBundleException;

final class InstagramClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $appId,
        private readonly string $appSecret,
        private readonly string $graphVersion,
    ) {
    }

    private function graphUrl(string $path): string
    {
        return sprintf('https://graph.facebook.com/%s/%s', $this->graphVersion, ltrim($path, '/'));
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $this->graphUrl($path), ['query' => $query]);
    }

    public function post(string $path, array $body = []): array
    {
        return $this->request('POST', $this->graphUrl($path), ['body' => $body]);
    }

    public function delete(string $path, array $query = []): array
    {
        return $this->request('DELETE', $this->graphUrl($path), ['query' => $query]);
    }

    public function exchangeCodeForAccessToken(string $code, string $redirectUri): array
    {
        return $this->request('POST', 'https://api.instagram.com/oauth/access_token', [
            'body' => [
                'client_id' => $this->appId,
                'client_secret' => $this->appSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ],
        ]);
    }

    public function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        return $this->request('GET', $this->graphUrl('access_token'), [
            'query' => [
                'grant_type' => 'ig_exchange_token',
                'client_secret' => $this->appSecret,
                'access_token' => $shortLivedToken,
            ],
        ]);
    }

    public function refreshLongLivedToken(string $longLivedToken): array
    {
        return $this->request('GET', $this->graphUrl('refresh_access_token'), [
            'query' => [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $longLivedToken,
            ],
        ]);
    }

    private function request(string $method, string $url, array $options = []): array
    {
        try {
            return $this->httpClient->request($method, $url, $options)->toArray(false);
        } catch (ExceptionInterface $e) {
            throw new InstagramBundleException('Instagram API request failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
