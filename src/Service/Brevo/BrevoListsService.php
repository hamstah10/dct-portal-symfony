<?php

namespace App\Service\Brevo;

final class BrevoListsService
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
        return $this->client->requestJson('GET', '/contacts/lists', ['query' => $query]);
    }

    /**
     * @param list<string> $emails
     *
     * @return array<string, mixed>
     */
    public function addContactsToList(int $listId, array $emails): array
    {
        return $this->client->requestJson('POST', sprintf('/contacts/lists/%d/contacts/add', $listId), [
            'json' => [
                'emails' => array_values($emails),
            ],
        ]);
    }
}
