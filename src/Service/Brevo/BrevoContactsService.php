<?php

namespace App\Service\Brevo;

final class BrevoContactsService
{
    public function __construct(
        private readonly BrevoApiClient $client,
    ) {
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    public function list(array $query = []): array
    {
        return $this->client->requestJson('GET', '/contacts', ['query' => $query]);
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $identifier): array
    {
        return $this->client->requestJson('GET', sprintf('/contacts/%s', urlencode($identifier)));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        return $this->client->requestJson('POST', '/contacts', ['json' => $payload]);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function update(string $identifier, array $payload): array
    {
        return $this->client->requestJson('PUT', sprintf('/contacts/%s', urlencode($identifier)), ['json' => $payload]);
    }
}
