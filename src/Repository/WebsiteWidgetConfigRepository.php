<?php

namespace App\Repository;

use App\Entity\Kunde;
use App\Entity\WebsiteWidgetConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebsiteWidgetConfig>
 */
final class WebsiteWidgetConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebsiteWidgetConfig::class);
    }

    public function findActiveByEmbedKey(string $embedKey): ?WebsiteWidgetConfig
    {
        $key = strtolower(trim($embedKey));
        if ('' === $key) {
            return null;
        }

        return $this->findOneBy([
            'embedKey' => $key,
            'active' => true,
        ]);
    }

    public function findLatestForCustomer(Kunde $customer): ?WebsiteWidgetConfig
    {
        return $this->createQueryBuilder('widget')
            ->andWhere('widget.kunde = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('widget.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
