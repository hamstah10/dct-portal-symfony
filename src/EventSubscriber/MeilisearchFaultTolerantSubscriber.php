<?php

namespace App\EventSubscriber;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Meilisearch\Bundle\SearchManagerInterface;
use Psr\Log\LoggerInterface;

final class MeilisearchFaultTolerantSubscriber
{
    public function __construct(
        private readonly SearchManagerInterface $searchManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        try {
            $this->searchManager->index($args->getObject());
        } catch (\Throwable $e) {
            $this->logger->warning('Meilisearch indexing failed (postUpdate): ' . $e->getMessage());
        }
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        try {
            $this->searchManager->index($args->getObject());
        } catch (\Throwable $e) {
            $this->logger->warning('Meilisearch indexing failed (postPersist): ' . $e->getMessage());
        }
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        try {
            $this->searchManager->remove($args->getObject());
        } catch (\Throwable $e) {
            $this->logger->warning('Meilisearch indexing failed (preRemove): ' . $e->getMessage());
        }
    }
}
