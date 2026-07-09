<?php

namespace App\Repository;

use App\Entity\WebsiteSnifferPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebsiteSnifferPage>
 */
class WebsiteSnifferPageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebsiteSnifferPage::class);
    }
}
