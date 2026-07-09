<?php

namespace App\Controller\Api;

use App\Entity\Kunde;
use App\Repository\KundeRepository;
use App\Service\Api\CustomerApiAuthService;
use App\Service\Api\CustomerApiPayloadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
final class AuthController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly KundeRepository $kundeRepository,
        private readonly CustomerApiAuthService $authService,
        private readonly CustomerApiPayloadService $payloadService,
    ) {
    }

    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $email = trim((string) ($payload['email'] ?? $payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? $payload['passwort'] ?? '');
        $deviceName = isset($payload['deviceName'])
            ? (string) $payload['deviceName']
            : (isset($payload['device_name']) ? (string) $payload['device_name'] : null);

        if ('' === $email || '' === trim($password)) {
            return $this->errorResponse('email und password sind erforderlich.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $customer = $this->kundeRepository->findOneBy(['email' => $email]);
        if (!$customer instanceof Kunde || !$this->isPasswordValid($customer, $password)) {
            return $this->errorResponse('Login fehlgeschlagen.', Response::HTTP_UNAUTHORIZED);
        }

        if ($customer->isGesperrt()) {
            return $this->errorResponse('Kunde ist gesperrt.', Response::HTTP_FORBIDDEN);
        }

        $tokens = $this->authService->issueTokenPair($customer, $deviceName);

        return new JsonResponse([
            ...$tokens,
            'customer' => $this->payloadService->normalizeAuthCustomer($customer),
        ]);
    }

    #[Route('/refresh', name: 'api_auth_refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $refreshToken = trim((string) ($payload['refreshToken'] ?? ''));
        if ('' === $refreshToken) {
            return $this->errorResponse('refreshToken ist erforderlich.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tokens = $this->authService->refreshByRefreshToken($refreshToken);
        if (null === $tokens) {
            return $this->errorResponse('Refresh-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse($tokens);
    }

    #[Route('/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(Request $request): JsonResponse
    {
        $accessToken = $this->getBearerToken($request);
        if (null === $accessToken) {
            return $this->errorResponse('Access-Token fehlt.', Response::HTTP_UNAUTHORIZED);
        }

        $customer = $this->authService->findCustomerByAccessToken($accessToken);
        if (!$customer instanceof Kunde) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'customer' => $this->payloadService->normalizeAuthCustomer($customer),
        ]);
    }

    #[Route('/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(Request $request): Response
    {
        $accessToken = $this->getBearerToken($request);
        if (null === $accessToken) {
            return $this->errorResponse('Access-Token fehlt.', Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->authService->isValidAccessToken($accessToken)) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $this->authService->revokeSessionByAccessToken($accessToken);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function isPasswordValid(Kunde $customer, string $plainPassword): bool
    {
        $hashedPassword = $customer->getPasswort();
        if (null === $hashedPassword || '' === trim($hashedPassword)) {
            return false;
        }

        return password_verify($plainPassword, $hashedPassword);
    }
}
