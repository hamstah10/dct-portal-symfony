<?php

namespace App\Websites\Chiptuningfile\Controller;

use App\Entity\Firma;
use App\Entity\Portal;
use App\Websites\Chiptuningfile\Service\WebsiteCompanyContextResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalController extends AbstractController
{
    #[Route(path: '/impressum', name: 'cpt_website_impressum', methods: ['GET'])]
    public function impressum(WebsiteCompanyContextResolver $contextResolver): Response
    {
        ['portal' => $portal, 'firma' => $firma] = $contextResolver->resolve();

        return $this->render('website/chiptuningfile/legal/impressum.html.twig', [
            'portal' => $portal,
            'firma' => $firma,
            'brandName' => $this->brandName($portal, $firma),
            'addressLine1' => $this->addressLine1($firma),
            'addressLine2' => $this->addressLine2($firma),
            'websiteUrl' => $this->websiteUrl($portal, $firma),
        ]);
    }

    #[Route(path: '/datenschutz', name: 'cpt_website_datenschutz', methods: ['GET'])]
    public function datenschutz(WebsiteCompanyContextResolver $contextResolver): Response
    {
        ['portal' => $portal, 'firma' => $firma] = $contextResolver->resolve();

        return $this->render('website/chiptuningfile/legal/datenschutz.html.twig', [
            'portal' => $portal,
            'firma' => $firma,
            'brandName' => $this->brandName($portal, $firma),
            'addressLine1' => $this->addressLine1($firma),
            'addressLine2' => $this->addressLine2($firma),
            'websiteUrl' => $this->websiteUrl($portal, $firma),
        ]);
    }

    private function brandName(?Portal $portal, ?Firma $firma): string
    {
        return trim((string) ($portal?->getName() ?? $firma?->getName() ?? 'Chiptuningfile'));
    }

    private function addressLine1(?Firma $firma): string
    {
        if (!$firma instanceof Firma) {
            return '';
        }

        return trim(sprintf('%s %s', (string) ($firma->getStrasse() ?? ''), (string) ($firma->getHausnummer() ?? '')));
    }

    private function addressLine2(?Firma $firma): string
    {
        if (!$firma instanceof Firma) {
            return '';
        }

        return trim(sprintf(
            '%s %s%s',
            (string) ($firma->getPlz() ?? ''),
            (string) ($firma->getStadt() ?? ''),
            $firma->getLand() ? ', ' . $firma->getLand() : ''
        ), ' ,');
    }

    private function websiteUrl(?Portal $portal, ?Firma $firma): ?string
    {
        $website = trim((string) ($firma?->getWebsite() ?? $portal?->getWebsiteUrl() ?? ''));
        if ('' === $website) {
            return null;
        }

        if (!str_starts_with($website, 'http://') && !str_starts_with($website, 'https://')) {
            $website = 'https://' . ltrim($website, '/');
        }

        return $website;
    }
}
