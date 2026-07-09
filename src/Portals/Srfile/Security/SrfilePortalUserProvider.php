<?php

declare(strict_types=1);

namespace App\Portals\Srfile\Security;

use App\Entity\Kunde;
use App\Repository\KundeRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class SrfilePortalUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly KundeRepository $kundeRepository,
        private readonly SrfilePortalAccessChecker $accessChecker,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $customer = $this->kundeRepository->findOneBy(['email' => trim($identifier)]);
        if (!$customer instanceof Kunde) {
            $exception = new UserNotFoundException(sprintf('Customer "%s" was not found.', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        if (!$this->accessChecker->canAccess($customer)) {
            $exception = new UserNotFoundException(sprintf('Customer "%s" is not allowed to access this portal.', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return new SrfilePortalCustomerUser($customer);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SrfilePortalCustomerUser) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s".', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === SrfilePortalCustomerUser::class;
    }
}
