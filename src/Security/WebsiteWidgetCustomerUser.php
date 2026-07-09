<?php

namespace App\Security;

use App\Entity\Kunde;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class WebsiteWidgetCustomerUser implements UserInterface, PasswordAuthenticatedUserInterface
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
        return ['ROLE_WEBSITE_WIDGET_CUSTOMER'];
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
