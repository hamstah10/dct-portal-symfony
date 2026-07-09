<?php

declare(strict_types=1);

namespace App\Portals\Chiptuningfile\Service;

use App\Entity\CreditPackage;
use App\Entity\Kunde;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PaypalOrderService
{
    private ?string $accessToken = null;
    private int $accessTokenExpiresAt = 0;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $paypalClientId = '',
        private readonly string $paypalClientSecret = '',
        private readonly string $paypalBaseUrl = '',
    ) {
    }

    public function isConfigured(): bool
    {
        return '' !== trim($this->paypalClientId) && '' !== trim($this->paypalClientSecret);
    }

    public function getClientId(): string
    {
        return $this->paypalClientId;
    }

    /**
     * @param array{priceGross:float} $pricing
     */
    public function createOrder(CreditPackage $package, Kunde $customer, array $pricing): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('PayPal ist nicht konfiguriert.');
        }

        $customerId = $customer->getId();
        if (null === $customerId) {
            throw new \RuntimeException('Kunde ist nicht persistent.');
        }

        $response = $this->requestJson('POST', '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'custom_id' => sprintf('cp%d-c%d', (int) $package->getId(), $customerId),
                'description' => sprintf('Credit-Paket %d Credits', $package->getCredits()),
                'amount' => [
                    'currency_code' => 'EUR',
                    'value' => number_format($pricing['priceGross'], 2, '.', ''),
                ],
            ]],
        ]);

        $orderId = $response['id'] ?? null;
        if (!is_string($orderId) || '' === trim($orderId)) {
            throw new \RuntimeException('PayPal Order-ID fehlt in der Antwort.');
        }

        return $orderId;
    }

    /**
     * @param array{priceNet:float,priceGross:float,taxRate:float} $pricing
     * @param array<string, scalar|null> $metadata
     */
    public function createCustomCreditsOrder(int $credits, Kunde $customer, array $pricing, array $metadata = []): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('PayPal ist nicht konfiguriert.');
        }

        $customerId = $customer->getId();
        if (null === $customerId) {
            throw new \RuntimeException('Kunde ist nicht persistent.');
        }

        if ($credits <= 0) {
            throw new \RuntimeException('Ungueltige Credit-Anzahl.');
        }

        $gross = round((float) ($pricing['priceGross'] ?? 0.0), 2);
        if ($gross <= 0.0) {
            throw new \RuntimeException('Ungueltiger Zahlungsbetrag.');
        }

        $customId = $this->buildCustomCreditsOrderId($credits, $customerId, $metadata);
        $description = sprintf('Credit-Kauf %d Credits', $credits);
        if (isset($metadata['context']) && is_scalar($metadata['context']) && trim((string) $metadata['context']) !== '') {
            $description .= ' | ' . trim((string) $metadata['context']);
        }

        $response = $this->requestJson('POST', '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'custom_id' => $customId,
                'description' => $description,
                'amount' => [
                    'currency_code' => 'EUR',
                    'value' => number_format($gross, 2, '.', ''),
                ],
            ]],
        ]);

        $orderId = $response['id'] ?? null;
        if (!is_string($orderId) || '' === trim($orderId)) {
            throw new \RuntimeException('PayPal Order-ID fehlt in der Antwort.');
        }

        return $orderId;
    }

    /**
     * @return array<string, mixed>
     */
    public function captureOrder(string $orderId): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('PayPal ist nicht konfiguriert.');
        }

        $orderId = trim($orderId);
        if ('' === $orderId) {
            throw new \RuntimeException('PayPal Order-ID fehlt.');
        }

        return $this->requestCapture(sprintf('/v2/checkout/orders/%s/capture', rawurlencode($orderId)));
    }

    /**
     * @param array<string, mixed>|object $payload
     *
     * @return array<string, mixed>
     */
    private function requestJson(string $method, string $path, array|object $payload): array
    {
        $response = $this->httpClient->request($method, $this->getBaseUrl() . $path, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ],
            'auth_bearer' => $this->getAccessToken(),
            'json' => $payload,
        ]);

        $statusCode = $response->getStatusCode();
        $data = $response->toArray(false);
        if ($statusCode >= 400) {
            $message = $data['message'] ?? $data['details'][0]['description'] ?? 'Unbekannter PayPal-Fehler.';

            $this->logger->error('PayPal API request failed.', [
                'method' => $method,
                'path' => $path,
                'statusCode' => $statusCode,
                'paypalDebugId' => $response->getHeaders(false)['paypal-debug-id'][0] ?? null,
                'response' => $data,
            ]);

            throw new \RuntimeException(sprintf('PayPal API Fehler: %s', (string) $message));
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function requestCapture(string $path): array
    {
        $response = $this->httpClient->request('POST', $this->getBaseUrl() . $path, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'auth_bearer' => $this->getAccessToken(),
            'body' => '{}',
        ]);

        $statusCode = $response->getStatusCode();
        $data = $response->toArray(false);
        if ($statusCode >= 400) {
            $message = $data['message'] ?? $data['details'][0]['description'] ?? 'Unbekannter PayPal-Fehler.';

            $this->logger->error('PayPal API request failed.', [
                'method' => 'POST',
                'path' => $path,
                'statusCode' => $statusCode,
                'paypalDebugId' => $response->getHeaders(false)['paypal-debug-id'][0] ?? null,
                'response' => $data,
            ]);

            throw new \RuntimeException(sprintf('PayPal API Fehler: %s', (string) $message));
        }

        return is_array($data) ? $data : [];
    }

    private function getAccessToken(): string
    {
        if (null !== $this->accessToken && $this->accessTokenExpiresAt > (time() + 30)) {
            return $this->accessToken;
        }

        $response = $this->httpClient->request('POST', $this->getBaseUrl() . '/v1/oauth2/token', [
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Language' => 'de_DE',
            ],
            'auth_basic' => [$this->paypalClientId, $this->paypalClientSecret],
            'body' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $data = $response->toArray(false);
        if ($statusCode >= 400) {
            $message = $data['error_description'] ?? $data['error'] ?? 'OAuth-Token konnte nicht geladen werden.';

            $this->logger->error('PayPal auth request failed.', [
                'statusCode' => $statusCode,
                'paypalDebugId' => $response->getHeaders(false)['paypal-debug-id'][0] ?? null,
                'response' => $data,
            ]);

            throw new \RuntimeException(sprintf('PayPal Auth Fehler: %s', (string) $message));
        }

        $accessToken = $data['access_token'] ?? null;
        if (!is_string($accessToken) || '' === trim($accessToken)) {
            throw new \RuntimeException('PayPal Access Token fehlt in der Antwort.');
        }

        $this->accessToken = $accessToken;
        $this->accessTokenExpiresAt = time() + max(60, (int) ($data['expires_in'] ?? 0));

        return $accessToken;
    }

    private function getBaseUrl(): string
    {
        $baseUrl = trim($this->paypalBaseUrl);

        return '' !== $baseUrl ? rtrim($baseUrl, '/') : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * @param array<string, scalar|null> $metadata
     */
    private function buildCustomCreditsOrderId(int $credits, int $customerId, array $metadata): string
    {
        $parts = [
            'ctx:' . preg_replace('/[^a-z0-9_:-]+/i', '', strtolower((string) ($metadata['context'] ?? 'credit_topup'))),
            'credits:' . $credits,
            'customer:' . $customerId,
        ];

        foreach (['pendingOrderId', 'requiredCredits'] as $key) {
            if (!array_key_exists($key, $metadata) || null === $metadata[$key]) {
                continue;
            }

            $parts[] = strtolower($key) . ':' . preg_replace('/[^a-z0-9_:-]+/i', '', (string) $metadata[$key]);
        }

        $customId = implode(';', $parts);

        return substr($customId, 0, 127);
    }
}
