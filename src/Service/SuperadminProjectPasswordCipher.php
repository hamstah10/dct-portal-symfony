<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class SuperadminProjectPasswordCipher
{
    private readonly string $key;

    public function __construct(#[Autowire('%kernel.secret%')] string $appSecret)
    {
        $this->key = hash('sha256', 'superadmin-project-password|' . $appSecret, true);
    }

    public function encrypt(string $plain): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($plain, $nonce, $this->key);

        return base64_encode($nonce . $cipher);
    }

    public function decrypt(string $encoded): ?string
    {
        $raw = base64_decode($encoded, true);
        if (false === $raw || strlen($raw) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            return null;
        }

        $nonce = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open($cipher, $nonce, $this->key);

        return false === $plain ? null : $plain;
    }
}
