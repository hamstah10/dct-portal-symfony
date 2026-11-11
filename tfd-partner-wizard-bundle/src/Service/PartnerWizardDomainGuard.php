<?php

namespace Tfd\PartnerWizardBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;

final class PartnerWizardDomainGuard
{
    /**
     * Prüft Token und Domain einer API-Anfrage.
     *
     * Regeln:
     * - Token mitgeschickt → muss gültig sein (sonst 401); gültig = erlaubt, Domain egal.
     * - Kein Token, Client verlangt Token immer → 401.
     * - Kein Token, kein Origin/Referer (Server-Aufruf), Client hat Token → 401.
     * - Sonst Domain-Prüfung über „Erlaubte Domains" (403 wenn nicht erlaubt).
     *
     * @return array{status: int, error: string}|null null = erlaubt
     */
    public function authorize(PartnerWizardClient $client, Request $request): ?array
    {
        $token = $this->extractToken($request);

        if ($token !== null) {
            return $client->isApiTokenValid($token)
                ? null
                : ['status' => 401, 'error' => 'Invalid API token'];
        }

        if ($client->isApiTokenRequired()) {
            return ['status' => 401, 'error' => 'API token required'];
        }

        $hasOrigin = (bool) ($request->headers->get('Origin') ?: $request->headers->get('Referer'));
        if (!$hasOrigin && $client->hasApiToken()) {
            return ['status' => 401, 'error' => 'API token required'];
        }

        return $this->isAllowed($client, $request)
            ? null
            : ['status' => 403, 'error' => 'Origin not allowed'];
    }

    public function extractToken(Request $request): ?string
    {
        $header = (string) $request->headers->get('Authorization', '');
        if (preg_match('/^Bearer\s+(\S+)$/i', trim($header), $m)) {
            return $m[1];
        }

        $key = trim((string) $request->headers->get('X-TFD-Api-Key', ''));

        return $key !== '' ? $key : null;
    }

    public function isAllowed(PartnerWizardClient $client, Request $request): bool
    {
        $allowed = array_filter($client->getAllowedDomains());
        if ($allowed === []) {
            return true;
        }

        $origin = $request->headers->get('Origin') ?: $request->headers->get('Referer') ?: '';
        if ($origin === '') {
            return true;
        }

        $host = parse_url($origin, PHP_URL_HOST) ?: $origin;

        foreach ($allowed as $domain) {
            $domain = strtolower(trim((string) $domain));
            $host = strtolower($host);

            if ($domain === $host) {
                return true;
            }

            if (str_starts_with($domain, '*.')) {
                $suffix = substr($domain, 1);
                if (str_ends_with($host, $suffix)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getOriginDomain(Request $request): ?string
    {
        $origin = $request->headers->get('Origin') ?: $request->headers->get('Referer');
        if (!$origin) {
            return null;
        }

        return parse_url($origin, PHP_URL_HOST) ?: $origin;
    }
}
