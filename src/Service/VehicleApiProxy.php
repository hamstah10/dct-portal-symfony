<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Portal;
use App\Entity\VehicleDataPool;
use App\Portals\Common\Security\PortalCustomerUserInterface;
use App\Repository\ApiTokenRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class VehicleApiProxy
{
    private const MAX_RETRIES = 2;
    /** @var array<string,string>|null */
    private ?array $remoteManifest = null;
    /** @var list<int> */
    private const RETRYABLE_STATUS_CODES = [429, 502, 503, 504];
    /** @var list<string> */
    private const IMAGE_KEYS = ['image', 'icon', 'logo', 'manufacturerLogo', 'brandLogo', 'logoUrl', 'thumbnail', 'src'];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly string $apiBaseUrl,
        private readonly LoggerInterface $logger,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function proxy(string $path, array $query = [], ?PortalCustomerUserInterface $user = null, ?Portal $portal = null): JsonResponse
    {
        return $this->callRequest('GET', $path, ['query' => $query], $user, $portal);
    }

    public function proxyPost(string $path, array $payload = [], ?PortalCustomerUserInterface $user = null, ?Portal $portal = null): JsonResponse
    {
        return $this->callRequest('POST', $path, ['json' => $payload], $user, $portal);
    }

    public function proxyAsset(string $path): HttpResponse
    {
        $normalizedPath = $this->normalizeAssetPath($path);
        if (null === $normalizedPath) {
            return new HttpResponse('Asset-Pfad ungueltig.', HttpResponse::HTTP_BAD_REQUEST);
        }

        $candidatePaths = $this->buildAssetCandidates($normalizedPath);

        foreach ($candidatePaths as $candidatePath) {
            $url = rtrim($this->apiBaseUrl, '/') . $candidatePath;

            try {
                $response = $this->requestWithRetry('GET', $url, [
                    'timeout' => 8.0,
                    'max_duration' => 12.0,
                    'headers' => ['Accept' => '*/*'],
                ]);
            } catch (\Throwable $exception) {
                $this->logger->warning('Vehicle API asset proxy request failed', [
                    'url' => $url,
                    'error' => $exception->getMessage(),
                ]);

                continue;
            }

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 400) {
                continue;
            }

            $headers = $response->getHeaders(false);
            $contentType = (string) (($headers['content-type'][0] ?? null) ?? 'application/octet-stream');
            $cacheControl = (string) (($headers['cache-control'][0] ?? null) ?? 'public, max-age=86400');
            $etag = (string) (($headers['etag'][0] ?? null) ?? '');
            $lastModified = (string) (($headers['last-modified'][0] ?? null) ?? '');

            $out = new HttpResponse($response->getContent(false), HttpResponse::HTTP_OK, [
                'Content-Type' => $contentType,
                'Cache-Control' => $cacheControl,
            ]);

            if ('' !== $etag) {
                $out->headers->set('ETag', $etag);
            }
            if ('' !== $lastModified) {
                $out->headers->set('Last-Modified', $lastModified);
            }

            return $out;
        }

        return new HttpResponse('Asset nicht gefunden.', HttpResponse::HTTP_NOT_FOUND);
    }

    /**
     * @return list<string>
     */
    private function buildAssetCandidates(string $normalizedPath): array
    {
        // 1. Try local hashed asset (e.g. locally published assets)
        $resolved = $this->resolveHashedPublicAssetPath($normalizedPath);
        if (is_string($resolved) && $resolved !== $normalizedPath) {
            return [$resolved, $normalizedPath];
        }

        // 2. Try remote manifest (assets from superadmin/API server with hash suffix)
        $remoteResolved = $this->resolveViaRemoteManifest($normalizedPath);
        if (is_string($remoteResolved) && $remoteResolved !== $normalizedPath) {
            return [$remoteResolved, $normalizedPath];
        }

        return [$normalizedPath];
    }

    private function resolveHashedPublicAssetPath(string $normalizedPath): ?string
    {
        if (!preg_match('#^/assets/(.+)\.(svg|png|jpe?g|webp|gif|avif)$#i', $normalizedPath, $matches)) {
            return null;
        }

        $relativeWithoutExt = (string) ($matches[1] ?? '');
        $extension = (string) ($matches[2] ?? '');
        if ($relativeWithoutExt === '' || $extension === '') {
            return null;
        }

        $segments = explode('/', $relativeWithoutExt);
        $baseName = (string) array_pop($segments);
        if ($baseName === '') {
            return null;
        }

        $directory = implode('/', $segments);
        $publicDirectory = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        if ($publicDirectory === '') {
            return null;
        }

        $scanDir = $publicDirectory . '/assets/' . $directory;
        if (!is_dir($scanDir)) {
            return null;
        }

        $globPattern = $scanDir . '/' . $baseName . '-*.' . $extension;
        $matchesOnDisk = glob($globPattern);
        if (!is_array($matchesOnDisk) || $matchesOnDisk === []) {
            return null;
        }

        usort($matchesOnDisk, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
        $picked = $matchesOnDisk[0] ?? null;
        if (!is_string($picked) || $picked === '') {
            return null;
        }

        $filename = basename($picked);

        return '/assets/' . ($directory !== '' ? $directory . '/' : '') . $filename;
    }

    private function resolveViaRemoteManifest(string $normalizedPath): ?string
    {
        if ($this->remoteManifest === null) {
            try {
                $response = $this->httpClient->request('GET', rtrim($this->apiBaseUrl, '/') . '/assets/manifest.json', [
                    'timeout' => 5.0,
                ]);
                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getContent(false), true);
                    $this->remoteManifest = is_array($data) ? $data : [];
                } else {
                    $this->remoteManifest = [];
                }
            } catch (\Throwable) {
                $this->remoteManifest = [];
            }
        }

        // manifest keys are like "icons/brands-tuning/moto/aprilia.png"
        // normalizedPath is like "/assets/icons/brands-tuning/moto/aprilia.png"
        $key = ltrim(preg_replace('#^/assets/#', '', $normalizedPath) ?? '', '/');
        $hashedPath = $this->remoteManifest[$key] ?? null;

        return is_string($hashedPath) && $hashedPath !== '' ? $hashedPath : null;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function callRequest(string $method, string $path, array $options, ?PortalCustomerUserInterface $user, ?Portal $portal): JsonResponse
    {
        $portalPool = $portal?->getVehicleDataPool();

        try {
            [$token, $tokenId] = $this->resolveVehicleApiCredentials($user, $portalPool?->isActive() ? $portalPool : null);
        } catch (AccessDeniedHttpException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        $poolKey = $portalPool?->isActive() ? $portalPool->getPoolKey() : null;
        if (is_string($poolKey) && $poolKey !== '') {
            $query = $options['query'] ?? [];
            if (is_array($query) && !array_key_exists('poolKey', $query)) {
                $query['poolKey'] = $poolKey;
                $options['query'] = $query;
            }
        }

        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            [
                'Authorization' => 'Bearer ' . $token,
                'X-Token-Id' => $tokenId,
                'Accept' => 'application/json',
            ]
        );
        $options['timeout'] = $options['timeout'] ?? 8.0;
        $options['max_duration'] = $options['max_duration'] ?? 12.0;

        $url = rtrim($this->apiBaseUrl, '/') . '/api/fahrzeugdatenbank' . $path;

        try {
            $response = $this->requestWithRetry($method, $url, $options);
        } catch (\Throwable $exception) {
            $this->logger->warning('Vehicle API proxy request failed', [
                'method' => $method,
                'url' => $url,
                'query' => $options['query'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            return new JsonResponse([
                'message' => 'Vehicle API ist derzeit nicht erreichbar.',
            ], Response::HTTP_BAD_GATEWAY);
        }

        return $this->toJsonResponse($response, $portal);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function resolveVehicleApiCredentials(?PortalCustomerUserInterface $user, ?VehicleDataPool $vehicleDataPool = null): array
    {
        $tokenEntity = null;

        if ($user instanceof PortalCustomerUserInterface && $vehicleDataPool instanceof VehicleDataPool) {
            $tokenEntity = $this->apiTokenRepository->findLatestActiveForCustomerAndPool($user->getCustomer(), $vehicleDataPool);
        }

        if (null === $tokenEntity && null === $vehicleDataPool && $user instanceof PortalCustomerUserInterface) {
            $tokenEntity = $this->apiTokenRepository->findLatestActiveForCustomer($user->getCustomer());
        }

        if (null === $tokenEntity && $vehicleDataPool instanceof VehicleDataPool) {
            $tokenEntity = $this->apiTokenRepository->findLatestActiveGlobalForPool($vehicleDataPool);
        }

        if (null === $tokenEntity) {
            $tokenEntity = $this->apiTokenRepository->findLatestActiveGlobal();
        }

        if (null === $tokenEntity) {
            if ($vehicleDataPool instanceof VehicleDataPool) {
                throw new AccessDeniedHttpException(sprintf(
                    'Kein aktiver Vehicle API Token fuer den Datenbestand "%s" vorhanden.',
                    $vehicleDataPool->getPoolKey()
                ));
            }

            throw new AccessDeniedHttpException('Kein aktiver Vehicle API Token vorhanden.');
        }

        return [(string) $tokenEntity->getApiToken(), (string) $tokenEntity->getTokenIdentifier()];
    }

    private function toJsonResponse(ResponseInterface $response, ?Portal $portal = null): JsonResponse
    {
        $decoded = json_decode((string) $response->getContent(false), true);

        if (is_array($decoded)) {
            $baseUrl = $this->resolvePortalBaseUrl($portal);
            $filtered = $this->filterInactiveEntries($decoded);

            return new JsonResponse($this->sanitizeImageUrls($filtered, $baseUrl), $response->getStatusCode());
        }

        return new JsonResponse(['message' => 'Ungültige Antwort der Vehicle API.'], Response::HTTP_BAD_GATEWAY);
    }

    /**
     * @param array<string|int, mixed> $payload
     * @param string|null $baseUrl
     *
     * @return array<string|int, mixed>
     */
    private function sanitizeImageUrls(array $payload, ?string $baseUrl = null): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->sanitizeImageUrls($value, $baseUrl);

                continue;
            }

            if (is_string($key) && is_string($value) && ($this->isImageKey($key) || $this->looksLikeImagePath($value))) {
                $payload[$key] = $this->normalizeLocalPublicPath($value, $baseUrl);
            }
        }

        return $payload;
    }

    private function isImageKey(string $key): bool
    {
        return in_array($key, self::IMAGE_KEYS, true);
    }

    /**
     * @param array<string|int, mixed> $payload
     * @return array<string|int, mixed>
     */
    private function filterInactiveEntries(array $payload): array
    {
        $isList = array_is_list($payload);
        $result = [];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $value = $this->filterInactiveEntries($value);

                if ($this->isInactiveRecord($value)) {
                    continue;
                }

                if ($isList) {
                    $result[] = $value;
                } else {
                    $result[$key] = $value;
                }

                continue;
            }

            if ($isList) {
                $result[] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param array<string|int, mixed> $record
     */
    private function isInactiveRecord(array $record): bool
    {
        if (!array_key_exists('active', $record)) {
            return false;
        }

        return $record['active'] === false || $record['active'] === 0 || $record['active'] === '0';
    }

    private function looksLikeImagePath(string $value): bool
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return false;
        }

        if (preg_match('#^https?://#i', $trimmed) === 1) {
            $path = parse_url($trimmed, PHP_URL_PATH);
            if (!is_string($path)) {
                return false;
            }
            $trimmed = $path;
        }

        $normalized = str_starts_with($trimmed, '/') ? $trimmed : '/' . ltrim($trimmed, '/');

        if (str_starts_with($normalized, '/uploads/') || str_starts_with($normalized, '/assets/')) {
            return true;
        }

        return (bool) preg_match('#\.(svg|png|jpe?g|webp|gif|avif)(\?.*)?$#i', $normalized);
    }

    private function normalizeLocalPublicPath(string $value, ?string $baseUrl = null): ?string
    {
        $trimmed = trim($value);
        if ('' === $trimmed) {
            return null;
        }

        if (str_starts_with($trimmed, 'data:')) {
            return $trimmed;
        }

        if (preg_match('#^https?://#i', $trimmed) === 1) {
            $parsedPath = parse_url($trimmed, PHP_URL_PATH);
            if (!is_string($parsedPath) || '' === trim($parsedPath)) {
                return null;
            }
            $trimmed = trim($parsedPath);
        }

        if (str_starts_with($trimmed, '//') || str_contains($trimmed, '://')) {
            return null;
        }

        $path = str_starts_with($trimmed, '/') ? $trimmed : '/' . ltrim($trimmed, '/');
        if (str_starts_with($path, '/assets/')) {
            $assetProxyPath = '/vehicle-api/asset?path=' . rawurlencode($path);
            if (!is_string($baseUrl) || '' === trim($baseUrl)) {
                return $assetProxyPath;
            }

            return rtrim($baseUrl, '/') . $assetProxyPath;
        }

        if (!is_string($baseUrl) || '' === trim($baseUrl)) {
            return $path;
        }

        return rtrim($baseUrl, '/') . $path;
    }

    private function normalizeAssetPath(string $path): ?string
    {
        $trimmed = trim($path);
        if ('' === $trimmed || str_contains($trimmed, '..')) {
            return null;
        }

        if (preg_match('#^https?://#i', $trimmed) === 1) {
            $parsedPath = parse_url($trimmed, PHP_URL_PATH);
            if (!is_string($parsedPath) || '' === trim($parsedPath)) {
                return null;
            }
            $trimmed = trim($parsedPath);
        }

        if (str_starts_with($trimmed, '//') || str_contains($trimmed, '://')) {
            return null;
        }

        $normalized = str_starts_with($trimmed, '/') ? $trimmed : '/' . ltrim($trimmed, '/');

        return str_starts_with($normalized, '/assets/') ? $normalized : null;
    }

    private function resolvePortalBaseUrl(?Portal $portal): ?string
    {
        $request = $this->requestStack->getMainRequest();
        $requestHost = strtolower((string) ($request?->getHost() ?? ''));

        if ($portal instanceof Portal) {
            $portalHost = strtolower((string) ($portal->getPortalHost() ?? ''));
            $websiteHost = strtolower((string) ($portal->getWebsiteHost() ?? ''));

            if ('' !== $requestHost && '' !== $websiteHost && $requestHost === $websiteHost) {
                return $portal->getWebsiteUrl();
            }
            if ('' !== $requestHost && '' !== $portalHost && $requestHost === $portalHost) {
                return $portal->getPortalUrl();
            }

            return $portal->getPortalUrl() ?? $portal->getWebsiteUrl();
        }

        return $request?->getSchemeAndHttpHost();
    }

    /**
     * @param array<string, mixed> $options
     */
    private function requestWithRetry(string $method, string $url, array $options): ResponseInterface
    {
        $attempt = 0;

        while (true) {
            try {
                $response = $this->httpClient->request($method, $url, $options);
                $statusCode = $response->getStatusCode();

                $shouldRetry = in_array($statusCode, self::RETRYABLE_STATUS_CODES, true) && $attempt < self::MAX_RETRIES;
                if (!$shouldRetry) {
                    return $response;
                }

                $this->logger->notice('Vehicle API proxy transient upstream status, retrying', [
                    'method' => $method,
                    'url' => $url,
                    'statusCode' => $statusCode,
                    'attempt' => $attempt + 1,
                ]);
            } catch (\Throwable $exception) {
                if ($attempt >= self::MAX_RETRIES) {
                    throw $exception;
                }

                $this->logger->notice('Vehicle API proxy transport error, retrying', [
                    'method' => $method,
                    'url' => $url,
                    'attempt' => $attempt + 1,
                    'error' => $exception->getMessage(),
                ]);
            }

            ++$attempt;
            usleep(150_000 * $attempt);
        }
    }
}
