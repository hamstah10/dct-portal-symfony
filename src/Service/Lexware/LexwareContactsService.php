<?php

namespace App\Service\Lexware;

final class LexwareContactsService
{
    public function __construct(
        private readonly LexwareApiClient $client,
    ) {
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    public function list(array $query = []): array
    {
        return $this->client->requestJson('GET', '/v1/contacts', ['query' => $query]);
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $id): array
    {
        return $this->client->requestJson('GET', sprintf('/v1/contacts/%s', $id));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        return $this->client->requestJson('POST', '/v1/contacts', ['json' => $payload]);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function update(string $id, array $payload): array
    {
        return $this->client->requestJson('PUT', sprintf('/v1/contacts/%s', $id), ['json' => $payload]);
    }
}
