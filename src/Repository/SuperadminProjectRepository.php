<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SuperadminProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SuperadminProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuperadminProject::class);
    }
}
