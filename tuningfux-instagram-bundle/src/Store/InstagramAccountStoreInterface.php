<?php

declare(strict_types=1);

namespace Tuningfux\InstagramBundle\Store;

use Tuningfux\InstagramBundle\Entity\InstagramAccount;

interface InstagramAccountStoreInterface
{
    public function save(InstagramAccount $account): void;

    /** @return list<InstagramAccount> */
    public function findAll(): array;

    public function findById(int $id): ?InstagramAccount;

    public function findByInstagramUserId(string $instagramUserId): ?InstagramAccount;
}
