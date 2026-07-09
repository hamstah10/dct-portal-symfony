<?php

namespace App\Service\Brevo;

final class BrevoService
{
    public function __construct(
        private readonly BrevoApiClient $client,
        private readonly BrevoContactsService $contacts,
        private readonly BrevoListsService $lists,
    ) {
    }

    public function client(): BrevoApiClient
    {
        return $this->client;
    }

    public function contacts(): BrevoContactsService
    {
        return $this->contacts;
    }

    public function lists(): BrevoListsService
    {
        return $this->lists;
    }
}
