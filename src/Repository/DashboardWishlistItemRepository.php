<?php

namespace App\Repository;

use App\Entity\DashboardWishlistItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DashboardWishlistItem>
 */
class DashboardWishlistItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DashboardWishlistItem::class);
    }

    /**
     * @return DashboardWishlistItem[]
     */
    public function findOrdered(): array
    {
        return $this->findBy([], ['sorting' => 'ASC', 'id' => 'ASC']);
    }
}
