<?php

namespace App\Repository;

trait ProvidesNextUidTrait
{
    public function getNextUid(): int
    {
        $max = (int) $this->createQueryBuilder('entity')
            ->select('MAX(entity.uid)')
            ->getQuery()
            ->getSingleScalarResult();

        return $max + 1;
    }
}
