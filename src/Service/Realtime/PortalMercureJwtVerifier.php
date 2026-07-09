<?php

namespace App\Service\Realtime;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class PortalMercureJwtVerifier
{
    public function __construct(
        #[Autowire('%env(string:MERCURE_JWT_SECRET)%')] private readonly string $secret,
    ) {
    }

    public function isValid(?string $jwt): bool
    {
        if (null === $jwt || '' === trim($jwt)) {
            return false;
        }

        $parts = explode('.', trim($jwt));
        if (3 !== \count($parts)) {
            return false;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $header = $this->decode($encodedHeader);
        $payload = $this->decode($encodedPayload);
        if (!\is_array($header) || !\is_array($payload)) {
            return false;
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            return false;
        }

        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', $encodedHeader.'.'.$encodedPayload, $this->secret, true));
        if (!hash_equals($expectedSignature, $encodedSignature)) {
            return false;
        }

        $exp = $payload['exp'] ?? null;
        if (\is_numeric($exp) && time() >= (int) $exp) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decode(string $part): ?array
    {
        $decoded = base64_decode(strtr($part, '-_', '+/').str_repeat('=', (4 - strlen($part) % 4) % 4), true);
        if (false === $decoded) {
            return null;
        }

        $data = json_decode($decoded, true);

        return \is_array($data) ? $data : null;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
