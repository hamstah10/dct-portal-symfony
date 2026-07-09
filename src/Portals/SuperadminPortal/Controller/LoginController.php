<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    #[Route('/login', name: 'superadmin_portal_login', methods: ['GET', 'POST'])]
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('superadmin_portal_dashboard');
        }

        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        return $this->render('portal/superadmin_portal/login/index.html.twig', [
            'portalConfig' => $portal,
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/logout', name: 'superadmin_portal_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \LogicException('This method should not be called directly.');
    }
}
