<?php

namespace App\Service\Lexware;

final class LexwareService
{
    public function __construct(
        private readonly LexwareApiClient $client,
        private readonly LexwareContactsService $contacts,
        private readonly LexwareArticlesService $articles,
        private readonly LexwareVouchersService $vouchers,
        private readonly LexwareInvoicesService $invoices,
    ) {
    }

    public function client(): LexwareApiClient
    {
        return $this->client;
    }

    public function contacts(): LexwareContactsService
    {
        return $this->contacts;
    }

    public function products(): LexwareArticlesService
    {
        return $this->articles;
    }

    public function vouchers(): LexwareVouchersService
    {
        return $this->vouchers;
    }

    public function invoices(): LexwareInvoicesService
    {
        return $this->invoices;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProfile(): array
    {
        return $this->client->getProfile();
    }
}
