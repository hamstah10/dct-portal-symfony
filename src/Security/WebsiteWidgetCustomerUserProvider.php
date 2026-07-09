<?php

namespace App\Security;

use App\Entity\Kunde;
use App\Repository\KundeRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class WebsiteWidgetCustomerUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly KundeRepository $kundeRepository,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $customer = $this->kundeRepository->findOneBy(['email' => trim($identifier)]);
        if (!$customer instanceof Kunde || $customer->isGesperrt() || null === $customer->getPasswort()) {
            $exception = new UserNotFoundException(sprintf('Customer "%s" was not found.', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return new WebsiteWidgetCustomerUser($customer);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof WebsiteWidgetCustomerUser) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s".', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === WebsiteWidgetCustomerUser::class;
    }
}
