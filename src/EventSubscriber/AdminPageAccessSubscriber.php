<?php

namespace App\EventSubscriber;

use App\Service\AdminPageAccessService;
use App\Service\AdminPageRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\SecurityBundle\Security;

final class AdminPageAccessSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AdminPageAccessService $pageAccessService,
        private readonly Security $security,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isAdminRequest($request)) {
            return;
        }

        $user = $this->security->getUser();
        if (null === $user) {
            return;
        }

        $pageKey = $this->resolvePageKey($request);
        if (null === $pageKey) {
            return;
        }

        if (!$this->pageAccessService->isAllowed($pageKey, $user)) {
            throw new AccessDeniedException('Keine Berechtigung fuer diese Seite.');
        }
    }

    private function isAdminRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/admin');
    }

    private function resolvePageKey(Request $request): ?string
    {
        $crudController = $this->getCrudControllerFqcn($request);
        if (null !== $crudController) {
            return AdminPageRegistry::crudKey($crudController);
        }

        $routeName = (string) $request->attributes->get('_route', '');
        if ('admin' === $routeName || 'admin_trailing_slash_redirect' === $routeName) {
            return AdminPageRegistry::DASHBOARD;
        }

        if ('' !== $routeName) {
            return AdminPageRegistry::routeKey($routeName);
        }

        return null;
    }

    private function getCrudControllerFqcn(Request $request): ?string
    {
        $fqcn = $request->query->get('crudControllerFqcn');
        if (is_string($fqcn) && '' !== $fqcn) {
            return $fqcn;
        }

        $fqcn = $request->attributes->get('crudControllerFqcn');
        if (is_string($fqcn) && '' !== $fqcn) {
            return $fqcn;
        }

        return null;
    }
}
