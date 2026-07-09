<?php

namespace App\Controller\Admin;

use App\Entity\ManualUidEntityInterface;

trait HandlesManualUidTrait
{
    protected function applyCreateDefaults(ManualUidEntityInterface $entity, int $nextUid): void
    {
        if (0 === $entity->getUid()) {
            $entity->setUid($nextUid);
        }

        if (0 === $entity->getCrdate()) {
            $entity->setCrdate(time());
        }

        $entity->setTstamp(time());
    }

    protected function applyUpdateDefaults(ManualUidEntityInterface $entity): void
    {
        $entity->setTstamp(time());
    }
}
