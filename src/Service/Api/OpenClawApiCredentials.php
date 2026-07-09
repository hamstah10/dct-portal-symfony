<?php

namespace App\Service\Api;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class OpenClawApiCredentials
{
    public function __construct(
        #[Autowire('%openclaw.api_token%')]
        private readonly string $apiToken,
    ) {
    }

    public function getApiToken(): string
    {
        return trim($this->apiToken);
    }

    /**
     * @return array<string, string>
     */
    public function getAuthorizationHeaders(): array
    {
        $token = $this->getApiToken();
        if ('' === $token) {
            return [];
        }

        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }
}
