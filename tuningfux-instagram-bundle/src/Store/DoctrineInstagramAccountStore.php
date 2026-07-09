<?php

namespace Tuningfux\InstagramBundle\Store;

use Doctrine\ORM\EntityManagerInterface;
use Tuningfux\InstagramBundle\Entity\InstagramAccount;
use Tuningfux\InstagramBundle\Repository\InstagramAccountRepository;

class DoctrineInstagramAccountStore implements InstagramAccountStoreInterface
{
    private InstagramAccountRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $repository = $this->entityManager->getRepository(InstagramAccount::class);

        if (!$repository instanceof InstagramAccountRepository) {
            throw new \RuntimeException(sprintf(
                'Expected repository "%s", got "%s".',
                InstagramAccountRepository::class,
                get_debug_type($repository)
            ));
        }

        $this->repository = $repository;
    }

    public function save(InstagramAccount $account): void
    {
        $account->touch();
        $this->entityManager->persist($account);
        $this->entityManager->flush();
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findById(int $id): ?InstagramAccount
    {
        return $this->repository->find($id);
    }

    public function findByInstagramUserId(string $instagramUserId): ?InstagramAccount
    {
        return $this->repository->findOneByInstagramUserId($instagramUserId);
    }
}