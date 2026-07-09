<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait ApiControllerTrait
{
    /**
     * @return array<string, mixed>
     */
    private function readJsonPayload(Request $request): array
    {
        $content = trim($request->getContent());
        if ('' === $content) {
            return [];
        }

        try {
            $decoded = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new \InvalidArgumentException('Ungueltiges JSON.');
        }

        if (!\is_array($decoded)) {
            throw new \InvalidArgumentException('JSON-Body muss ein Objekt sein.');
        }

        return $decoded;
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return new JsonResponse(['message' => $message], $status);
    }

    private function getBearerToken(Request $request): ?string
    {
        $header = $request->headers->get('Authorization');
        if (null === $header) {
            return null;
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches)) {
            return null;
        }

        $token = trim($matches[1]);

        return '' === $token ? null : $token;
    }
}
