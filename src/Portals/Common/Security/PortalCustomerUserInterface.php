<?php

declare(strict_types=1);

namespace App\Portals\Common\Security;

use App\Entity\Kunde;
use Symfony\Component\Security\Core\User\UserInterface;

interface PortalCustomerUserInterface extends UserInterface
{
    public function getCustomer(): Kunde;
}
