<?php

namespace App\Service;

use App\Entity\ApiToken;
use App\Entity\ChiptuningWidgetConfig;
use App\Entity\VehicleDataPool;
use App\Repository\ChiptuningWidgetConfigRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ChiptuningWidgetConfigManager
{
    public function __construct(
        private readonly ChiptuningWidgetConfigRepository $widgetRepository,
    ) {
    }

    public function prepareForSave(ChiptuningWidgetConfig $widget, EntityManagerInterface $entityManager, bool $new): void
    {
        if (null === $widget->getEmbedKey()) {
            $widget->setEmbedKey($this->generateEmbedKey());
        }

        if (null === $widget->getVehicleDataPool()) {
            $defaultPool = $entityManager->getRepository(VehicleDataPool::class)->findOneBy(['active' => true], ['name' => 'ASC']);
            if ($defaultPool instanceof VehicleDataPool) {
                $widget->setVehicleDataPool($defaultPool);
            }
        }

        if (null === $widget->getApiToken()) {
            $token = new ApiToken();
            $token
                ->setApplicationName('Chiptuning Widget: ' . ($widget->getTitle() ?? 'Fahrzeugdatenbank'))
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
