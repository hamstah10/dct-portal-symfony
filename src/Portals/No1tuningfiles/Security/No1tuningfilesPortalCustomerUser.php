<?php

declare(strict_types=1);

namespace App\Portals\No1tuningfiles\Security;

use App\Entity\Kunde;
use App\Portals\Common\Security\PortalCustomerUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class No1tuningfilesPortalCustomerUser implements PortalCustomerUserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private readonly Kunde $customer,
    ) {
    }

    public function getCustomer(): Kunde
    {
        return $this->customer;
    }

    public function getRoles(): array
    {
        return ['ROLE_PORTAL_CUSTOMER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->customer->getEmail();
    }

    public function getPassword(): ?string
    {
        return $this->customer->getPasswort();
    }
}
