<?php

namespace App\Websites\Chiptuningfile\Service;

use App\Entity\Firma;
use App\Entity\Portal;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class StaticWebsiteRenderer
{
    private const ALLOWED_PAGES = [
        'index',
        'faq',
        'haendlerbereich',
        'konfigurator',
        'kontakt',
        'preisliste',
        'services',
        'ueber-uns',
    ];

    public function __construct(
        private readonly WebsiteCompanyContextResolver $companyContextResolver,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function renderPage(string $page): Response
    {
        if (!in_array($page, self::ALLOWED_PAGES, true)) {
            throw new NotFoundHttpException();
        }

        $path = sprintf(
            '%s/public/assets/portals/chiptuningfile/website/%s.html',
            $this->projectDir,
            $page
        );

        if (!is_file($path)) {
            throw new NotFoundHttpException();
        }

        $html = file_get_contents($path);
        if (false === $html) {
            throw new NotFoundHttpException();
        }

        $html = str_replace(
            ['href="css/', 'src="css/', 'href="js/', 'src="js/'],
            [
                'href="/assets/portals/chiptuningfile/website/css/',
                'src="/assets/portals/chiptuningfile/website/css/',
                'href="/assets/portals/chiptuningfile/website/js/',
                'src="/assets/portals/chiptuningfile/website/js/',
            ],
            $html
        );

        $html = $this->replaceCompanyData($html);

        return new Response($html, Response::HTTP_OK, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function replaceCompanyData(string $html): string
    {
        ['portal' => $portal, 'firma' => $firma] = $this->companyContextResolver->resolve();

        $brandName = trim((string) ($portal?->getName() ?? $firma?->getName() ?? 'Chiptuningfile'));
        $email = trim((string) ($firma?->getEmail() ?? ''));
        $phone = trim((string) ($firma?->getTelefon() ?? ''));
        $website = trim((string) ($firma?->getWebsite() ?? $portal?->getWebsiteUrl() ?? ''));
        $portalRegistrationUrl = rtrim((string) ($portal?->getPortalUrl() ?? 'https://chiptuningfile.tuningfux.de'), '/') . '/registrieren';

        $email = '' !== $email ? $email : 'info@chiptuningfile.de';
        $phoneDisplay = '' !== $phone ? $phone : '+49 (0) 123 456 78';
        $phoneHref = $this->normalizePhoneHref($phoneDisplay);
        $location = $this->buildLocation($firma);
        $copyright = sprintf('&copy; %s %s', date('Y'), $brandName);

        $html = str_replace('Chiptuningfile.de', $brandName, $html);
        $html = str_replace('info@chiptuningfile.de', $email, $html);
        $html = str_replace('mailto:info@chiptuningfile.de', 'mailto:' . $email, $html);
        $html = str_replace('+49 (0) 123 456 78', $phoneDisplay, $html);
        $html = str_replace('tel:+4912345678', 'tel:' . $phoneHref, $html);
        $html = preg_replace('/&copy;\s*2026\s+[^<.]+(?:\.\s*Alle Rechte vorbehalten\.)?/', $copyright . '. Alle Rechte vorbehalten.', $html) ?? $html;
        $html = preg_replace('/&copy;\s*2026\s+[^<.]+/', $copyright, $html) ?? $html;
        $html = str_replace('<p class="fw-medium text-ct-slate-900 mb-0">Deutschland</p>', '<p class="fw-medium text-ct-slate-900 mb-0">' . htmlspecialchars($location, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>', $html);
        $html = str_replace('<span class="d-block text-white">Deutschland</span>', '<span class="d-block text-white">' . htmlspecialchars($location, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>', $html);

        if ('' !== $website) {
            $html = $this->injectWebsiteBlock($html, $website);
        }

        $html = str_replace('href="#">Datenschutz</a>', 'href="/datenschutz">Datenschutz</a>', $html);
        $html = str_replace('href="#">Impressum</a>', 'href="/impressum">Impressum</a>', $html);
        $html = str_replace(
            'href="kontakt.html" class="btn btn-ct-primary btn-ct-primary-lg" data-turbo-action="advance" data-testid="dealer-register-btn"',
            'href="' . htmlspecialchars($portalRegistrationUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" class="btn btn-ct-primary btn-ct-primary-lg" data-testid="dealer-register-btn"',
            $html
        );

        return $html;
    }

    private function injectWebsiteBlock(string $html, string $website): string
    {
        $normalizedWebsite = $this->normalizeWebsite($website);
        $websiteLabel = preg_replace('#^https?://#', '', $normalizedWebsite) ?? $normalizedWebsite;

        $contactWebsiteBlock = '<div class="d-flex gap-3 scroll-reveal" data-animation="fade-up" style="transition-duration:600ms;transition-delay:150ms" data-testid="contact-info-website"><div class="ct-stat-icon flex-shrink-0" style="width:48px;height:48px;font-size:1.125rem"><i class="bi bi-globe"></i></div><div><small class="text-ct-slate-400 text-uppercase" style="letter-spacing:0.1em">Website</small><a href="' . htmlspecialchars($normalizedWebsite, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" class="d-block fw-medium text-ct-slate-900 text-decoration-none" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($websiteLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a></div></div>';
        $footerWebsiteBlock = '<li class="d-flex gap-3"><i class="bi bi-globe ct-footer-contact-icon"></i><div><small class="text-ct-slate-400">Website</small><a href="' . htmlspecialchars($normalizedWebsite, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" class="d-block text-white text-decoration-none" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($websiteLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</a></div></li>';

        $html = preg_replace(
            '/(<div class="d-flex gap-3 scroll-reveal"[^>]*data-testid="contact-info-1"[\s\S]*?<\/div><\/div>)/',
            '$1' . $contactWebsiteBlock,
            $html,
            1
        ) ?? $html;

        $html = str_replace(
            '</li><li class="d-flex gap-3"><i class="bi bi-geo-alt ct-footer-contact-icon"></i>',
            '</li>' . $footerWebsiteBlock . '<li class="d-flex gap-3"><i class="bi bi-geo-alt ct-footer-contact-icon"></i>',
            $html
        );

        return $html;
    }

    private function buildLocation(?Firma $firma): string
    {
        if (!$firma instanceof Firma) {
            return 'Deutschland';
        }

        $street = trim(sprintf(
            '%s %s',
            (string) ($firma->getStrasse() ?? ''),
            (string) ($firma->getHausnummer() ?? '')
        ));
        $cityLine = trim(sprintf(
            '%s %s',
            (string) ($firma->getPlz() ?? ''),
            (string) ($firma->getStadt() ?? '')
        ));

        $parts = array_values(array_filter([
            $street,
            $cityLine,
            trim((string) ($firma->getLand() ?? '')),
        ], static fn (string $value): bool => '' !== $value));

        return [] !== $parts ? implode(', ', $parts) : 'Deutschland';
    }

    private function normalizePhoneHref(string $phone): string
    {
        $normalized = preg_replace('/[^0-9+]+/', '', $phone) ?? $phone;

        return '' !== $normalized ? $normalized : '+4912345678';
    }

    private function normalizeWebsite(string $website): string
    {
        $website = trim($website);
        if ('' === $website) {
            return '';
        }

        if (!str_starts_with($website, 'http://') && !str_starts_with($website, 'https://')) {
            return 'https://' . ltrim($website, '/');
        }

        return $website;
    }
}
