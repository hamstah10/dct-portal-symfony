<?php

namespace App\Repository;

use App\Entity\Formula;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Formula>
 */
final class FormulaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formula::class);
    }

    /**
     * @return list<Formula>
     */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['area' => 'ASC', 'name' => 'ASC']);
    }

    public function findOneByKey(string $key): ?Formula
    {
        return $this->findOneBy(['key' => $key]);
    }
}
