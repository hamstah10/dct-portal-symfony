<?php

declare(strict_types=1);

namespace App\Brand\Common\Portal\Exception;

use App\Brand\Common\Context\SystemType;
use App\Entity\Portal;

final class PortalUnavailableException extends \RuntimeException
{
    public const REASON_INACTIVE = 'inactive';
    public const REASON_PORTAL_DISABLED = 'portal_disabled';
    public const REASON_WEBSITE_DISABLED = 'website_disabled';

    public function __construct(
        private readonly Portal $portal,
        private readonly string $systemType,
        private readonly string $reason,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function getPortal(): Portal
    {
        return $this->portal;
    }

    public function getSystemType(): string
    {
        return $this->systemType;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function isPortalSystem(): bool
    {
        return $this->systemType === SystemType::PORTAL;
    }

    public function isWebsiteSystem(): bool
    {
        return $this->systemType === SystemType::WEBSITE;
    }
}
