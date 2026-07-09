<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\Api\MobileApiAuthService;
use Symfony\Component\HttpFoundation\Request;

trait MobileAuthTrait
{
    private function requireMobileUser(Request $request, MobileApiAuthService $authService): ?User
    {
        $accessToken = $this->getBearerToken($request);
        if (null === $accessToken) {
            return null;
        }

        return $authService->findUserByAccessToken($accessToken);
    }
}
