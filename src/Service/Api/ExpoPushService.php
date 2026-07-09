<?php

namespace App\Service\Api;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ExpoPushService
{
    private const ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param list<string> $expoPushTokens
     */
    public function sendToTokens(array $expoPushTokens, string $title, string $body): bool
    {
        $expoPushTokens = array_values(array_filter($expoPushTokens, static fn (string $token): bool => str_starts_with($token, 'ExponentPushToken[') || str_starts_with($token, 'ExpoPushToken[')));

        if ([] === $expoPushTokens) {
            return false;
        }

        $messages = array_map(static fn (string $token): array => [
            'to' => $token,
            'title' => $title,
            'body' => $body,
            'sound' => 'default',
        ], $expoPushTokens);

        try {
            $response = $this->httpClient->request('POST', self::ENDPOINT, [
                'json' => $messages,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

            return $response->getStatusCode() < 300;
        } catch (\Throwable $exception) {
            $this->logger->warning('Expo push send failed', ['exception' => $exception->getMessage()]);

            return false;
        }
    }
}
