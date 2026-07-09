<?php

namespace App\Service;

use App\Entity\ApiToken;
use App\Entity\Kunde;
use App\Entity\WebsiteWidgetConfig;
use App\Repository\WebsiteWidgetConfigRepository;
use Doctrine\ORM\EntityManagerInterface;

final class WebsiteWidgetConfigManager
{
    public function __construct(
        private readonly WebsiteWidgetConfigRepository $widgetRepository,
    ) {
    }

    public function createForCustomer(Kunde $customer): WebsiteWidgetConfig
    {
        $title = trim((string) ($customer->getFirmenname() ?: $customer->getFirma()?->getName()));
        if ('' === $title) {
            $title = 'Fahrzeugdatenbank';
        }

        return (new WebsiteWidgetConfig())
            ->setKunde($customer)
            ->setTitle($title)
            ->setSubtitle('Fahrzeugdaten und Leistungsstufen direkt auf deiner Website')
            ->setButtonText('Anfrage senden');
    }

    public function prepareForSave(WebsiteWidgetConfig $widget, EntityManagerInterface $entityManager, bool $new): void
    {
        if (null === $widget->getEmbedKey()) {
            $widget->setEmbedKey($this->generateEmbedKey());
        }

        if (null === $widget->getApiToken()) {
            $token = new ApiToken();
            $token
                ->setApplicationName('Website Widget: ' . ($widget->getTitle() ?? 'Fahrzeugdatenbank'))
                ->setApplicationLink(null)
                ->setKunde($widget->getKunde())
                ->setActive(true);
            $this->ensureApiTokenSecrets($token);
            $widget->setApiToken($token);
            $entityManager->persist($token);
        } else {
            $this->ensureApiTokenSecrets($widget->getApiToken());
        }

        $now = new \DateTimeImmutable();
        if ($new || null === $widget->getCreatedAt()) {
            $widget->setCreatedAt($now);
        }
        $widget->setUpdatedAt($now);
    }

    private function ensureApiTokenSecrets(ApiToken $token): void
    {
        if ('' === trim((string) $token->getApiToken())) {
            $token->setApiToken(bin2hex(random_bytes(32)));
        }

        if ('' === trim((string) $token->getTokenIdentifier())) {
            $token->setTokenIdentifier(bin2hex(random_bytes(16)));
        }
    }

    private function generateEmbedKey(): string
    {
        do {
            $key = bin2hex(random_bytes(8));
        } while (null !== $this->widgetRepository->findOneBy(['embedKey' => $key]));

        return $key;
    }
}
