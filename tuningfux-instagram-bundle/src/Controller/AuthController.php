<?php

declare(strict_types=1);

namespace Tuningfux\InstagramBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Tuningfux\InstagramBundle\Service\InstagramAuthService;

final class AuthController extends AbstractController
{
    public function __construct(
        private readonly InstagramAuthService $authService,
    ) {
    }

    public function connect(): RedirectResponse
    {
        return new RedirectResponse($this->authService->getAuthorizeUrl());
    }

    public function callback(Request $request): JsonResponse
    {
        $code = $request->query->get('code');

        if (!is_string($code) || $code === '') {
            return $this->json([
                'ok' => false,
                'error' => 'Kein OAuth Code vorhanden.',
            ], 400);
        }

        try {
            $account = $this->authService->handleCallback($code);

            return $this->json([
                'ok' => true,
                'id' => $account->getId(),
                'instagramUserId' => $account->getInstagramUserId(),
                'username' => $account->getUsername(),
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
