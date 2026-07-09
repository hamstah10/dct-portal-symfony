<?php

namespace App\Service\Lexware;

final class LexwareApiException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly ?string $responseBody = null,
        private readonly ?string $requestUrl = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    public function getRequestUrl(): ?string
    {
        return $this->requestUrl;
    }
}
