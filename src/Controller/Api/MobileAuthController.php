<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\UserPushToken;
use App\Repository\PortalRepository;
use App\Repository\UserPushTokenRepository;
use App\Repository\UserRepository;
use App\Service\Api\ExpoPushService;
use App\Service\Api\MobileApiAuthService;
use App\Service\Api\MobileApiPayloadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/mobile')]
final class MobileAuthController extends AbstractController
{
    use ApiControllerTrait;
    use MobileAuthTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PortalRepository $portalRepository,
        private readonly UserPushTokenRepository $pushTokenRepository,
        private readonly MobileApiAuthService $authService,
        private readonly MobileApiPayloadService $payloadService,
        private readonly ExpoPushService $expoPushService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/auth/login', name: 'api_mobile_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $portalKey = trim((string) ($payload['portalKey'] ?? ''));
        $deviceName = isset($payload['deviceName']) ? (string) $payload['deviceName'] : null;

        if ('' === $email || '' === $password) {
            return $this->errorResponse('email und password sind erforderlich.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user instanceof User || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->errorResponse('Login fehlgeschlagen.', Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->isActive()) {
            return $this->errorResponse('Benutzer ist deaktiviert.', Response::HTTP_FORBIDDEN);
        }

        if (!\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->errorResponse('Kein Zugriff auf die mobile App.', Response::HTTP_FORBIDDEN);
        }

        $portal = '' !== $portalKey ? $this->portalRepository->findOneBy(['portalKey' => strtolower($portalKey)]) : null;

        $tokens = $this->authService->issueTokenPair($user, $deviceName);

        return new JsonResponse([
            'token' => $tokens['accessToken'],
            'refreshToken' => $tokens['refreshToken'],
            'user' => $this->payloadService->normalizeUser($user),
            'portal' => [
                'key' => $portal?->getPortalKey() ?? $portalKey,
                'name' => $portal?->getName() ?? '',
                'firma' => $portal?->getFirma()?->getName(),
            ],
        ]);
    }

    #[Route('/auth/push-token', name: 'api_mobile_auth_push_token', methods: ['POST'])]
    public function registerPushToken(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $pushToken = trim((string) ($payload['pushToken'] ?? ''));
        if ('' === $pushToken) {
            return $this->errorResponse('pushToken ist erforderlich.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $existing = $this->pushTokenRepository->findOneByToken($pushToken);
        if (null !== $existing) {
            $existing->setUser($user);
            $existing->setLastUsedAt(new \DateTimeImmutable());
        } else {
            $existing = (new UserPushToken())
                ->setUser($user)
                ->setToken($pushToken);
            $this->entityManager->persist($existing);
        }

        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/push/test', name: 'api_mobile_push_test', methods: ['POST'])]
    public function testPush(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $tokens = $this->pushTokenRepository->findByUser($user);
        if ([] === $tokens) {
            return new JsonResponse(['sent' => false]);
        }

        $sent = $this->expoPushService->sendToTokens(
            array_map(static fn (UserPushToken $t): string => $t->getToken(), $tokens),
            'TFD CRM',
            'Dies ist eine Test-Benachrichtigung.'
        );

        return new JsonResponse(['sent' => $sent]);
    }

    private function requireUser(Request $request): ?User
    {
        return $this->requireMobileUser($request, $this->authService);
    }
}
