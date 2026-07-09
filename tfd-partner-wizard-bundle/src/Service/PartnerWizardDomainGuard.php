<?php

namespace Tfd\PartnerWizardBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;

final class PartnerWizardDomainGuard
{
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
