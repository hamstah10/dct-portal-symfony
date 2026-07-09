<?php

namespace App\Repository;

use App\Entity\WebhookEndpoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebhookEndpoint>
 */
class WebhookEndpointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebhookEndpoint::class);
    }

    /**
     * @return array<int, WebhookEndpoint>
     */
    public function findActiveForEvent(string $eventName): array
    {
        $normalizedEvent = strtolower(trim($eventName));
        if ('' === $normalizedEvent) {
            return [];
        }

        $candidates = $this->createQueryBuilder('w')
            ->andWhere('w.active = :active')
            ->setParameter('active', true)
            ->orderBy('w.id', 'ASC')
            ->getQuery()
            ->getResult();

        return array_values(array_filter(
            $candidates,
            static fn (WebhookEndpoint $endpoint): bool => $endpoint->supportsEvent($normalizedEvent)
        ));
    }
}
