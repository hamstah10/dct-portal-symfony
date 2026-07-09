<?php

namespace App\Service\Api;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class OpenClawApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly OpenClawApiCredentials $credentials,
        #[Autowire('%env(string:OPENCLAW_API_BASE)%')]
        private readonly string $apiBase,
        #[Autowire('%env(int:OPENCLAW_API_TIMEOUT)%')]
        private readonly int $apiTimeout,
        #[Autowire('%env(string:OPENCLAW_API_USER_AGENT)%')]
        private readonly string $userAgent,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function requestJson(string $method, string $path = '', array $options = []): array
    {
        $response = $this->request($method, $path, $options);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode >= 400) {
            throw new OpenClawApiException(
                sprintf('OpenClaw API error (%d).', $statusCode),
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
            throw new OpenClawApiException(
                'OpenClaw API returned invalid JSON.',
                $statusCode,
                $this->truncate($content),
                $this->buildUrl($path)
            );
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $options
     */
    public function requestRaw(string $method, string $path = '', array $options = []): ResponseInterface
    {
        $response = $this->request($method, $path, $options);
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            $content = $response->getContent(false);

            throw new OpenClawApiException(
                sprintf('OpenClaw API error (%d).', $statusCode),
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
    public function index(): array
    {
        return $this->requestJson('GET');
    }

    /**
     * @param array<string, scalar|array<array-key, scalar|null>|null> $query
     *
     * @return array<string, mixed>
     */
    public function list(string $resource, array $query = []): array
    {
        return $this->requestJson('GET', '/' . $this->encodePathSegment($resource), ['query' => $query]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(string $resource): array
    {
        return $this->requestJson('GET', '/' . $this->encodePathSegment($resource) . '/schema');
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(string $resource, string|int $id): array
    {
        return $this->requestJson('GET', sprintf('/%s/%s', $this->encodePathSegment($resource), rawurlencode((string) $id)));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function create(string $resource, array $payload): array
    {
        return $this->requestJson('POST', '/' . $this->encodePathSegment($resource), ['json' => $payload]);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function update(string $resource, string|int $id, array $payload, bool $partial = true): array
    {
        return $this->requestJson(
            $partial ? 'PATCH' : 'PUT',
            sprintf('/%s/%s', $this->encodePathSegment($resource), rawurlencode((string) $id)),
            ['json' => $payload]
        );
    }

    public function delete(string $resource, string|int $id): void
    {
        $this->requestRaw(
            'DELETE',
            sprintf('/%s/%s', $this->encodePathSegment($resource), rawurlencode((string) $id))
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    private function request(string $method, string $path, array $options): ResponseInterface
    {
        $token = $this->credentials->getApiToken();
        if ('' === $token) {
            throw new OpenClawApiException('OpenClaw API token is missing.', 0, null, $this->buildUrl($path));
        }

        $headers = $options['headers'] ?? [];
        if (!is_array($headers)) {
            $headers = [];
        }

        $headers = array_replace([
            'Accept' => 'application/json',
            'User-Agent' => $this->userAgent,
        ], $this->credentials->getAuthorizationHeaders(), $headers);

        $options['headers'] = $headers;
        $options['timeout'] = $options['timeout'] ?? max(1, $this->apiTimeout);

        return $this->httpClient->request($method, $this->buildUrl($path), $options);
    }

    private function buildUrl(string $path): string
    {
        $base = rtrim(trim($this->apiBase), '/');
        $suffix = ltrim($path, '/');

        if ('' === $suffix) {
            return $base;
        }

        return $base . '/' . $suffix;
    }

    private function encodePathSegment(string $value): string
    {
        return rawurlencode(trim($value));
    }

    private function truncate(string $value, int $maxLength = 2000): string
    {
        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return substr($value, 0, $maxLength) . '... [truncated]';
    }
}
