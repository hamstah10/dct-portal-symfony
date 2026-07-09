<?php

declare(strict_types=1);

namespace App\Brand\Common\Portal;

use App\Brand\Common\Context\BrandContextResolver;
use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\Exception\PortalUnavailableException;
use App\Entity\Portal;
use App\Repository\PortalRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PortalRuntimeResolver
{
    public function __construct(
        private readonly BrandContextResolver $brandContextResolver,
        private readonly PortalRepository $portalRepository,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function resolveCurrentPortal(?Request $request = null): ?Portal
    {
        $brandContext = $this->brandContextResolver->resolve($request);
        if ($brandContext->getBrandKey() === 'superadmin') {
            return null;
        }

        return $this->portalRepository->findOneBy(['portalKey' => $brandContext->getBrandKey()]);
    }

    public function requirePortalSystem(?Request $request = null): Portal
    {
        $request ??= $this->requestStack->getCurrentRequest();
        $portal = $this->resolveCurrentPortal($request);
        if (!$portal instanceof Portal) {
            throw new NotFoundHttpException('Portal configuration not found.');
        }

        $systemType = $this->brandContextResolver->resolve($request)->getSystemType();

        if (!$portal->isActive()) {
            throw new PortalUnavailableException(
                $portal,
                $systemType,
                PortalUnavailableException::REASON_INACTIVE,
                'Portal is disabled.',
            );
        }

        if ($systemType === SystemType::PORTAL && !$portal->isPortalEnabled()) {
            throw new PortalUnavailableException(
                $portal,
                $systemType,
                PortalUnavailableException::REASON_PORTAL_DISABLED,
                'Portal is not available.',
            );
        }

        if ($systemType === SystemType::WEBSITE && !$portal->isWebsiteEnabled()) {
            throw new PortalUnavailableException(
                $portal,
                $systemType,
                PortalUnavailableException::REASON_WEBSITE_DISABLED,
                'Website is not available.',
            );
        }

        return $portal;
    }

    public function requirePortalModule(string $module, ?Request $request = null): Portal
    {
        $portal = $this->requirePortalSystem($request);
        if (!in_array($module, $portal->getEnabledModules(), true)) {
            throw new NotFoundHttpException(sprintf('Module "%s" is not enabled.', $module));
        }

        return $portal;
    }

    public function requireRegistrationEnabled(?Request $request = null): Portal
    {
        $portal = $this->requirePortalModule(Portal::MODULE_REGISTRATION, $request);
        if (!$portal->isCustomerRegistrationEnabled()) {
            throw new NotFoundHttpException('Registration is not available.');
        }

        return $portal;
    }
}
