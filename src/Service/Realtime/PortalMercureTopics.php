<?php

namespace App\Service\Realtime;

final class PortalMercureTopics
{
    public const PORTAL_GLOBAL = 'tfd/portal/global';
    public const PORTAL_ADMIN = 'tfd/portal/admin';
    public const PORTAL_APP = 'tfd/portal/app';
    public const PORTAL_INCOMING = 'tfd/portal/incoming';

    /**
     * @return list<string>
     */
    public function adminNotifications(): array
    {
        return [
            self::PORTAL_ADMIN,
            self::PORTAL_INCOMING,
        ];
    }

    /**
     * @return list<string>
     */
    public function appNotifications(): array
    {
        return [
            self::PORTAL_GLOBAL,
            self::PORTAL_APP,
        ];
    }

    public function aiProject(int $projectId): string
    {
        return sprintf('tfd/portal/ai-project/%d', $projectId);
    }

    public function customerOrders(int $customerId): string
    {
        return sprintf('tfd/portal/customer/%d/orders', $customerId);
    }
}
