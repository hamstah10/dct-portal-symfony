<?php

namespace App\Repository;

use App\Entity\WebsiteSnifferRun;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebsiteSnifferRun>
 */
class WebsiteSnifferRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebsiteSnifferRun::class);
    }
}
