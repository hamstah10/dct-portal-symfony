<?php

namespace App\Brand\Common\Context;

final class SystemType
{
    public const SUPERADMIN = 'superadmin';
    public const PORTAL = 'portal';
    public const WEBSITE = 'website';

    private function __construct()
    {
    }
}
