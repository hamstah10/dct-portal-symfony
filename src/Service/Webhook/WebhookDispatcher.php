<?php

namespace App\Service\Webhook;

use App\Entity\WebhookEndpoint;
use App\Repository\WebhookEndpointRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WebhookDispatcher
{
    public function __construct(
        private readonly WebhookEndpointRepository $webhookEndpointRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
        #[Autowire(service: 'monolog.logger.webhook_events')]
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function dispatch(string $eventName, array $data): void
    {
        $normalizedEvent = strtolower(trim($eventName));
        if ('' === $normalizedEvent) {
            return;
        }

        $endpoints = $this->webhookEndpointRepository->findActiveForEvent($normalizedEvent);
        if ([] === $endpoints) {
            return;
        }

        $envelope = [
            'event' => $normalizedEvent,
            'sentAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'data' => $data,
        ];

        try {
            $jsonPayload = (string) json_encode($envelope, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $this->logger->error('Webhook payload encoding failed', [
                'event' => $normalizedEvent,
                'error' => $exception->getMessage(),
            ]);

            return;
        }
        $payloadHash = hash('sha256', $jsonPayload);

        foreach ($endpoints as $endpoint) {
            $this->deliverToEndpoint($endpoint, $normalizedEvent, $jsonPayload, $payloadHash);
        }
    }

    private function deliverToEndpoint(
        WebhookEndpoint $endpoint,
        string $eventName,
        string $jsonPayload,
        string $payloadHash
    ): void {
        $timestamp = (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM);
        $deliveryId = bin2hex(random_bytes(12));
        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'TFD-CRM-Webhook/1.0',
            'X-Webhook-Event' => $eventName,
            'X-Webhook-Delivery' => $deliveryId,
            'X-Webhook-Timestamp' => $timestamp,
            'X-Webhook-Payload-Sha256' => $payloadHash,
        ];

        $secret = $endpoint->getSecret();
        if (null !== $secret && '' !== trim($secret)) {
            $signature = hash_hmac('sha256', $timestamp . '.' . $jsonPayload, $secret);
            $headers['X-Webhook-Signature'] = 'sha256=' . $signature;
        }

        $timeout = $endpoint->getTimeoutSeconds();
        $options = [
            'headers' => $headers,
            'body' => $jsonPayload,
            'timeout' => $timeout,
            'max_duration' => $timeout + 2,
            'verify_peer' => $endpoint->isVerifySsl(),
            'verify_host' => $endpoint->isVerifySsl(),
        ];

        $this->logger->info('Webhook delivery started', [
            'endpoint_id' => $endpoint->getId(),
            'endpoint_name' => $endpoint->getName(),
            'target_url' => $endpoint->getTargetUrl(),
            'event' => $eventName,
            'delivery_id' => $deliveryId,
        ]);

        try {
            $response = $this->httpClient->request('POST', $endpoint->getTargetUrl(), $options);
            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false);
            $contentPreview = $this->truncate($content, 1000);

            $endpoint
                ->setLastDeliveryAt(new \DateTimeImmutable())
                ->setLastHttpStatus($statusCode)
                ->setLastError($statusCode >= 200 && $statusCode < 300 ? null : $this->buildHttpError($statusCode, $contentPreview));

            $this->entityManager->flush();

            $this->logger->info('Webhook delivery finished', [
                'endpoint_id' => $endpoint->getId(),
                'event' => $eventName,
                'delivery_id' => $deliveryId,
                'status_code' => $statusCode,
                'success' => $statusCode >= 200 && $statusCode < 300,
            ]);
        } catch (ExceptionInterface|\Throwable $exception) {
            $endpoint
                ->setLastDeliveryAt(new \DateTimeImmutable())
                ->setLastHttpStatus(null)
                ->setLastError($this->truncate($exception->getMessage(), 2000));

            $this->entityManager->flush();

            $this->logger->error('Webhook delivery failed', [
                'endpoint_id' => $endpoint->getId(),
                'event' => $eventName,
                'delivery_id' => $deliveryId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function buildHttpError(int $statusCode, string $contentPreview): string
    {
        if ('' === trim($contentPreview)) {
            return sprintf('HTTP %d', $statusCode);
        }

        return sprintf('HTTP %d: %s', $statusCode, $contentPreview);
    }

    private function truncate(string $value, int $maxLength): string
    {
        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return substr($value, 0, $maxLength) . '... [truncated]';
    }
}
