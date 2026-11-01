<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Portals\Chiptuningfile\Security\PortalCustomerUser;
use App\Portals\No1tuningfiles\Security\No1tuningfilesPortalCustomerUser;
use App\Portals\Srfile\Security\SrfilePortalCustomerUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class CustomerLoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof PortalCustomerUser
            && !$user instanceof No1tuningfilesPortalCustomerUser
            && !$user instanceof SrfilePortalCustomerUser) {
            return;
        }

        $customer = $user->getCustomer();
        $customer->setLastLoginAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }
}
