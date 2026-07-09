<?php

declare(strict_types=1);

namespace App\Portals\Chiptuningfile\Service;

use App\Entity\ApiToken;
use App\Entity\Kunde;
use App\Repository\ApiTokenRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class StripePaymentService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly ApiTokenRepository $apiTokenRepository,
        private readonly string $applicationName = 'Stripe',
        private readonly string $stripeBaseUrl = 'https://api.stripe.com'
    ) {
    }

    public function isConfigured(): bool
    {
        $token = $this->resolveToken();

        return null !== $token
            && '' !== trim((string) $token->getSecretKey())
            && '' !== trim((string) $token->getPublicKey());
    }

    public function getPublicKey(): ?string
    {
        return $this->resolveToken()?->getPublicKey();
    }

    /**
     * @param array<string, scalar|null> $metadata
     */
    public function createCreditPaymentIntent(Kunde $customer, float $grossAmount, int $credits, array $metadata = []): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Stripe ist nicht konfiguriert.');
        }

        if ($credits <= 0) {
            throw new \InvalidArgumentException('Ungültige Credit-Anzahl.');
        }

        if ($grossAmount <= 0) {
            throw new \InvalidArgumentException('Ungültiger Betrag.');
        }

        $token = $this->resolveToken();
        if (null === $token) {
            throw new \RuntimeException('Kein aktiver Stripe-Token gefunden.');
        }

        $amount = (int) round($grossAmount * 100);
        $payload = [
            'amount' => $amount,
            'currency' => 'eur',
            'description' => sprintf(
                'Credit-Kauf %d Credits für %s',
                $credits,
                trim((string) ($customer->getEmail() ?? ''))
            ),
            'metadata[credits]' => (string) $credits,
            'metadata[customerId]' => (string) ($customer->getId() ?? 0),
        ];

        foreach ($metadata as $key => $value) {
            $payload[sprintf('metadata[%s]', $key)] = (string) $value;
        }

        try {
            $response = $this->httpClient->request('POST', $this->stripeBaseUrl . '/v1/payment_intents', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'auth_basic' => [trim((string) $token->getSecretKey()), ''],
                'body' => $payload,
            ]);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Stripe payment intent request failed.', [
                'error' => $exception->getMessage(),
            ]);

            throw new \RuntimeException('Stripe-Zahlung konnte nicht gestartet werden.');
        }

        $statusCode = $response->getStatusCode();
        $data = $response->toArray(false);

        if ($statusCode >= 400) {
            $message = $data['error']['message'] ?? 'Unbekannter Stripe-Fehler.';
            $this->logger->error('Stripe payment intent error response.', [
                'status' => $statusCode,
                'response' => $data,
            ]);

            throw new \RuntimeException(sprintf('Stripe API Fehler: %s', (string) $message));
        }

        return [
            'id' => $data['id'] ?? null,
            'clientSecret' => $data['client_secret'] ?? null,
            'amount' => $data['amount'] ?? $amount,
            'currency' => $data['currency'] ?? 'eur',
            'metadata' => $data['metadata'] ?? [],
        ];
    }

    public function retrievePaymentIntent(string $intentId): array
    {
        $trimmed = trim($intentId);
        if ('' === $trimmed) {
            throw new \InvalidArgumentException('Stripe PaymentIntent-ID fehlt.');
        }

        if (!$this->isConfigured()) {
            throw new \RuntimeException('Stripe ist nicht konfiguriert.');
        }

        $token = $this->resolveToken();
        if (null === $token) {
            throw new \RuntimeException('Kein aktiver Stripe-Token gefunden.');
        }

        try {
            $response = $this->httpClient->request('GET', $this->stripeBaseUrl . '/v1/payment_intents/' . urlencode($trimmed), [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'auth_basic' => [trim((string) $token->getSecretKey()), ''],
            ]);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Stripe payment intent retrieve failed.', [
                'error' => $exception->getMessage(),
            ]);

            throw new \RuntimeException('Stripe PaymentIntent konnte nicht geladen werden.');
        }

        $statusCode = $response->getStatusCode();
        $data = $response->toArray(false);

        if ($statusCode >= 400) {
            $message = $data['error']['message'] ?? 'Unbekannter Stripe-Fehler.';
            $this->logger->error('Stripe payment intent retrieve error.', [
                'status' => $statusCode,
                'response' => $data,
            ]);

            throw new \RuntimeException(sprintf('Stripe API Fehler: %s', (string) $message));
        }

        return $data;
    }

    private function resolveToken(): ?ApiToken
    {
        return $this->apiTokenRepository->findActiveByApplicationName($this->applicationName);
    }
}
