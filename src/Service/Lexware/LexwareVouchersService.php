<?php

namespace App\Service\Lexware;

final class LexwareVouchersService
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
        return $this->client->requestJson('GET', '/v1/vouchers', ['query' => $query]);
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    public function listVoucherList(array $query = []): array
    {
        return $this->client->requestJson('GET', '/v1/voucherlist', ['query' => $query]);
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $id): array
    {
        return $this->client->requestJson('GET', sprintf('/v1/vouchers/%s', $id));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        return $this->client->requestJson('POST', '/v1/vouchers', ['json' => $payload]);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function update(string $id, array $payload): array
    {
        return $this->client->requestJson('PUT', sprintf('/v1/vouchers/%s', $id), ['json' => $payload]);
    }
}
