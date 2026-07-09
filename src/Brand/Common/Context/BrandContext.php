<?php

namespace App\Brand\Common\Context;

final class BrandContext
{
    public function __construct(
        private readonly string $brandKey,
        private readonly string $systemType,
        private readonly string $host,
    ) {
    }

    public function getBrandKey(): string
    {
        return $this->brandKey;
    }

    public function getSystemType(): string
    {
        return $this->systemType;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function isPortal(): bool
    {
        return SystemType::PORTAL === $this->systemType;
    }
}
