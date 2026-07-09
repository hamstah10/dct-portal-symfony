<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiRequestLoggerSubscriber implements EventSubscriberInterface
{
    private const int MAX_LOGGED_BODY_LENGTH = 4000;

    public function __construct(
        #[Autowire(service: 'monolog.logger.api_requests')]
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isApiRequest($request)) {
            return;
        }

        $request->attributes->set('_api_request_started_at', microtime(true));
        $request->attributes->set('_api_request_log_id', bin2hex(random_bytes(6)));

        $this->logger->info('API request received', [
            'request_id' => $request->attributes->get('_api_request_log_id'),
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'route' => (string) $request->attributes->get('_route', ''),
            'query' => $request->query->all(),
            'content_type' => (string) $request->headers->get('Content-Type'),
            'body' => $this->extractRequestBody($request),
            'client_ip' => $request->getClientIp(),
            'user_agent' => substr((string) $request->headers->get('User-Agent'), 0, 255),
        ]);
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isApiRequest($request)) {
            return;
        }

        $startedAt = $request->attributes->get('_api_request_started_at');
        $durationMs = null;

        if (\is_float($startedAt)) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        }

        $requestId = (string) $request->attributes->get('_api_request_log_id', '');
        if ('' !== $requestId) {
            $event->getResponse()->headers->set('X-Request-Id', $requestId);
        }

        $statusCode = $event->getResponse()->getStatusCode();

        $this->logger->info('API response sent', [
            'request_id' => $requestId,
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'route' => (string) $request->attributes->get('_route', ''),
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? null,
            'duration_ms' => $durationMs,
        ]);
    }

    private function isApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/');
    }

    /**
     * @return array<string, mixed>|string|null
     */
    private function extractRequestBody(Request $request): array|string|null
    {
        if (\in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return null;
        }

        if ($request->request->count() > 0) {
            return $this->sanitizeData($request->request->all());
        }

        $rawContent = trim((string) $request->getContent());
        if ('' === $rawContent) {
            return null;
        }

        $contentType = strtolower((string) $request->headers->get('Content-Type'));
        if (str_contains($contentType, 'application/json')) {
            try {
                $decoded = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);
                if (\is_array($decoded)) {
                    return $this->sanitizeData($decoded);
                }

                if (\is_scalar($decoded) || null === $decoded) {
                    return $this->truncate((string) json_encode($decoded));
                }
            } catch (\JsonException) {
                // Fallback below if JSON is malformed.
            }
        }

        return $this->truncate($rawContent);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $sanitized[$key] = '***';

                continue;
            }

            if ($this->isPayloadKey((string) $key)) {
                $sanitized[$key] = $this->maskPayloadValue($value);

                continue;
            }

            if (\is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);

                continue;
            }

            if (\is_string($value)) {
                $sanitized[$key] = $this->truncate($value);

                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach ([
            'password',
            'passwort',
            'token',
            'authorization',
            'api_key',
            'apikey',
            'secret',
            'access_token',
            'refresh_token',
        ] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function isPayloadKey(string $key): bool
    {
        return \in_array(strtolower($key), ['filedata', 'file_data'], true);
    }

    private function maskPayloadValue(mixed $value): string
    {
        if (\is_string($value)) {
            return sprintf('[omitted payload, length=%d]', \strlen($value));
        }

        if (\is_array($value)) {
            return sprintf('[omitted payload array, items=%d]', \count($value));
        }

        return '[omitted payload]';
    }

    private function truncate(string $value): string
    {
        if (\strlen($value) <= self::MAX_LOGGED_BODY_LENGTH) {
            return $value;
        }

        return substr($value, 0, self::MAX_LOGGED_BODY_LENGTH).'... [truncated]';
    }
}
