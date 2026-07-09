<?php

namespace App\Service\Lexware;

use App\Repository\ApiTokenRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class LexwareApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ApiTokenRepository $apiTokenRepository,
        #[Autowire('%lexware.api_base%')]
        private readonly string $apiBase,
        #[Autowire('%lexware.api_key%')]
        private readonly string $apiKey,
        #[Autowire('%lexware.api_timeout%')]
        private readonly int $apiTimeout,
        #[Autowire('%lexware.api_user_agent%')]
        private readonly string $userAgent,
        #[Autowire('%lexware.api_token_name%')]
        private readonly string $tokenName,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function requestJson(string $method, string $path, array $options = []): array
    {
        $response = $this->request($method, $path, $options);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);
        if ($statusCode >= 400) {
            throw new LexwareApiException(
                sprintf('Lexware API error (%d).', $statusCode),
                $statusCode,
                $this->truncate($content),
                $this->buildUrl($path)
            );
        }

        if ('' === trim($content)) {
            return [];
        }

        try {
            $decoded = $response->toArray(false);
        } catch (ExceptionInterface) {
            throw new LexwareApiException(
                'Lexware API returned invalid JSON.',
                $statusCode,
                $this->truncate($content),
                $this->buildUrl($path)
            );
        }

        return is_array($decoded) ? $decoded : [];
    }

    public function requestRaw(string $method, string $path, array $options = []): ResponseInterface
    {
        $response = $this->request($method, $path, $options);
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            $content = $response->getContent(false);
            throw new LexwareApiException(
                sprintf('Lexware API error (%d).', $statusCode),
                $statusCode,
                $this->truncate($content),
                $this->buildUrl($path)
            );
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProfile(): array
    {
        return $this->requestJson('GET', '/v1/profile');
    }

    private function request(string $method, string $path, array $options): ResponseInterface
    {
        $apiKey = $this->resolveApiKey();
        if ('' === $apiKey) {
            throw new LexwareApiException('Lexware API key is missing.', 0, null, $this->buildUrl($path));
        }

        $headers = $options['headers'] ?? [];
        if (!is_array($headers)) {
            $headers = [];
        }

        $headers = array_replace([
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
            'User-Agent' => $this->userAgent,
        ], $headers);

        $options['headers'] = $headers;
        $options['timeout'] = $options['timeout'] ?? max(1, $this->apiTimeout);

        $url = $this->buildUrl($path);
        $maxRetries = 2;
        $attempt = 0;

        do {
            $response = $this->httpClient->request($method, $url, $options);
            $status = $response->getStatusCode();

            if (429 !== $status) {
                return $response;
            }

            $headers = $response->getHeaders(false);
            $retryAfter = $headers['retry-after'][0] ?? null;
            $delaySeconds = is_numeric($retryAfter) ? (int) $retryAfter : (1 + $attempt);
            if ($delaySeconds > 0) {
                usleep($delaySeconds * 1000000);
            }

            $attempt++;
        } while ($attempt <= $maxRetries);

        return $response;
    }

    private function buildUrl(string $path): string
    {
        $base = rtrim(trim($this->apiBase), '/');
        $suffix = ltrim($path, '/');

        return $base . '/' . $suffix;
    }

    private function truncate(string $value, int $maxLength = 2000): string
    {
        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return substr($value, 0, $maxLength) . '... [truncated]';
    }

    private function resolveApiKey(): string
    {
        $configuredApiKey = trim($this->apiKey);
        if ('' !== $configuredApiKey) {
            return $configuredApiKey;
        }

        $name = trim($this->tokenName);
        if ('' !== $name) {
            $token = $this->apiTokenRepository->findActiveByApplicationName($name);
            if (null !== $token) {
                $value = trim((string) $token->getApiToken());
                if ('' !== $value) {
                    return $value;
                }
            }
        }

        return '';
    }
}
