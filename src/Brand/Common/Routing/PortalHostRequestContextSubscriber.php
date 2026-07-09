<?php

declare(strict_types=1);

namespace App\Brand\Common\Routing;

use App\Brand\Common\Context\SystemType;
use App\Repository\PortalRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Stopwatch\Stopwatch;

final class PortalHostRequestContextSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PortalRepository $portalRepository,
        private readonly ?Stopwatch $stopwatch = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 250],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $this->profileStart('brand_context.resolve');

        try {
            if (!$event->isMainRequest()) {
                return;
            }

            $request = $event->getRequest();
            $host = strtolower(trim((string) $request->getHost()));
            $request->attributes->set('_brand_host', $host);

            $this->profileStart('brand_context.host_lookup');
            try {
                $portal = $this->portalRepository->findOneByKnownHost($host);
            } finally {
                $this->profileStop('brand_context.host_lookup');
            }

            if ($portal === null) {
                $request->attributes->set('_brand_system_type', SystemType::SUPERADMIN);
                $request->attributes->set('_brand_portal_key', 'superadmin');

                return;
            }

            $systemType = SystemType::SUPERADMIN;
            if ($portal->getPortalHost() !== null && strtolower($portal->getPortalHost()) === $host) {
                $systemType = SystemType::PORTAL;
            } elseif ($portal->getWebsiteHost() !== null && strtolower($portal->getWebsiteHost()) === $host) {
                $systemType = SystemType::WEBSITE;
            }

            $request->attributes->set('_brand_system_type', $systemType);
            $request->attributes->set('_brand_portal_key', $portal->getPortalKey());
            $request->attributes->set('_brand_portal_id', $portal->getId());
        } finally {
            $this->profileStop('brand_context.resolve');
        }
    }

    private function profileStart(string $name): void
    {
        $this->stopwatch?->start($name, 'request-bootstrap');
    }

    private function profileStop(string $name): void
    {
        if (null === $this->stopwatch || !$this->stopwatch->isStarted($name)) {
            return;
        }

        $this->stopwatch->stop($name);
    }
}
