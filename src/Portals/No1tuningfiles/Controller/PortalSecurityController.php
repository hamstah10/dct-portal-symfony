<?php

namespace App\Portals\No1tuningfiles\Controller;

use App\Brand\Common\Context\BrandContextResolver;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use App\Portals\No1tuningfiles\Form\PasswordResetConfirmFormType;
use App\Portals\No1tuningfiles\Form\PasswordResetRequestFormType;
use App\Portals\No1tuningfiles\Form\PortalRegistrationFormType;
use App\Portals\No1tuningfiles\Service\PortalPasswordResetService;
use App\Portals\No1tuningfiles\Service\PortalRegistrationService;
use App\Portals\Chiptuningfile\Security\PortalCustomerAuthenticator;
use App\Portals\No1tuningfiles\Security\No1tuningfilesPortalCustomerUser;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PortalSecurityController extends AbstractController
{
    #[Route('/login', name: 'no1_portal_login', methods: ['GET', 'POST'])]
    public function login(
        AuthenticationUtils $authenticationUtils,
        BrandContextResolver $brandContextResolver,
        PortalRuntimeResolver $portalRuntimeResolver,
        CsrfTokenManagerInterface $csrfTokenManager,
        TranslatorInterface $translator,
    ): Response
    {
        if ($this->getUser() instanceof No1tuningfilesPortalCustomerUser) {
            return $this->redirectToRoute('no1_portal_dashboard');
        }

        $portalConfig = $portalRuntimeResolver->requirePortalSystem();

        return $this->render('portal/no1tuningfiles/security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
            'reactBootstrap' => [
                'portalDesign' => $portalConfig->getResolvedDesign(),
                'portalDesign' => $portalConfig->getResolvedDesign(),
                'login' => [
                    'lastUsername' => $authenticationUtils->getLastUsername(),
                    'csrfToken' => $csrfTokenManager->getToken('portal_customer_authenticate')->getValue(),
                    'error' => $authenticationUtils->getLastAuthenticationError() !== null
                        ? $translator->trans(
                            $authenticationUtils->getLastAuthenticationError()->getMessageKey(),
                            $authenticationUtils->getLastAuthenticationError()->getMessageData(),
                            'security'
                        )
                        : null,
                ],
            ],
        ]);
    }

    #[Route('/registrieren', name: 'no1_portal_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        BrandContextResolver $brandContextResolver,
        PortalRegistrationService $portalRegistrationService,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        if ($this->getUser() instanceof No1tuningfilesPortalCustomerUser) {
            return $this->redirectToRoute('no1_portal_dashboard');
        }

        $portalConfig = $portalRuntimeResolver->requireRegistrationEnabled($request);

        $form = $this->createForm(PortalRegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $portalRegistrationService->register([
                    'vorname' => $form->get('vorname')->getData(),
                    'nachname' => $form->get('nachname')->getData(),
                    'firmenname' => $form->get('firmenname')->getData(),
                    'email' => $form->get('email')->getData(),
                    'telefon' => $form->get('telefon')->getData(),
                    'adresse' => $form->get('adresse')->getData(),
                    'website' => $form->get('website')->getData(),
                    'ustId' => $form->get('ustId')->getData(),
                    'land' => $form->get('land')->getData(),
                    'firmenbeschreibung' => $form->get('firmenbeschreibung')->getData(),
                    'newsletter' => $form->get('newsletter')->getData(),
                    'plainPassword' => $form->get('plainPassword')->getData(),
                ]);
            } catch (\InvalidArgumentException $exception) {
                $this->addFlash('danger', $exception->getMessage());

                return $this->render('portal/no1tuningfiles/security/register.html.twig', [
                    'form' => $form->createView(),
                    'brandContext' => $brandContextResolver->resolve(),
                    'portalConfig' => $portalConfig,
                    'reactBootstrap' => [
                'portalDesign' => $portalConfig->getResolvedDesign(),
                        'register' => $this->buildRegisterBootstrap($form, $request),
                    ],
                ]);
            }

            $this->addFlash('success', 'Dein Kundenkonto wurde erstellt. Du kannst dich jetzt einloggen.');

            return $this->redirectToRoute('no1_portal_login');
        }

        return $this->render('portal/no1tuningfiles/security/register.html.twig', [
            'form' => $form->createView(),
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
            'reactBootstrap' => [
                'portalDesign' => $portalConfig->getResolvedDesign(),
                'register' => $this->buildRegisterBootstrap($form, $request),
            ],
        ]);
    }

    #[Route('/passwort-vergessen', name: 'no1_portal_password_reset_request', methods: ['GET', 'POST'])]
    public function requestPasswordReset(
        Request $request,
        BrandContextResolver $brandContextResolver,
        PortalPasswordResetService $portalPasswordResetService,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        $portalConfig = $portalRuntimeResolver->requirePortalSystem($request);
        $form = $this->createForm(PasswordResetRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (string) $form->get('email')->getData();
            $portalPasswordResetService->requestReset($email);
            $this->addFlash('success', 'Wenn ein passender Portalzugang existiert, wurde eine E-Mail zum Zuruecksetzen verschickt.');

            return $this->redirectToRoute('no1_portal_login');
        }

        return $this->render('portal/no1tuningfiles/security/password_reset_request.html.twig', [
            'form' => $form->createView(),
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
        ]);
    }

    #[Route('/passwort-zuruecksetzen/{token}', name: 'no1_portal_password_reset_confirm', methods: ['GET', 'POST'])]
    public function confirmPasswordReset(
        string $token,
        Request $request,
        BrandContextResolver $brandContextResolver,
        PortalPasswordResetService $portalPasswordResetService,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        $portalConfig = $portalRuntimeResolver->requirePortalSystem($request);
        $resetToken = $portalPasswordResetService->findValidToken($token);
        if (null === $resetToken) {
            $this->addFlash('danger', 'Der Reset-Link ist ungueltig oder abgelaufen.');

            return $this->redirectToRoute('no1_portal_password_reset_request');
        }

        $form = $this->createForm(PasswordResetConfirmFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            $portalPasswordResetService->resetPassword($resetToken, $plainPassword);
            $this->addFlash('success', 'Dein Passwort wurde aktualisiert. Du kannst dich jetzt einloggen.');

            return $this->redirectToRoute('no1_portal_login');
        }

        return $this->render('portal/no1tuningfiles/security/password_reset_confirm.html.twig', [
            'form' => $form->createView(),
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
        ]);
    }

    #[Route('/logout', name: 'no1_portal_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \LogicException('This method is intercepted by the firewall logout handler.');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRegisterBootstrap(FormInterface $form, Request $request): array
    {
        $formName = $form->getName();
        $postData = $request->request->all($formName);
        if (!is_array($postData)) {
            $postData = [];
        }

        $plainPassword = $postData['plainPassword'] ?? [];
        if (!is_array($plainPassword)) {
            $plainPassword = [];
        }

        $errors = [];
        foreach ($form->getErrors(true, true) as $error) {
            $message = trim((string) $error->getMessage());
            if ('' !== $message) {
                $errors[] = $message;
            }
        }
        $errors = array_values(array_unique($errors));

        return [
            'formName' => $formName,
            'csrfToken' => $form->has('_token') ? (string) ($form->get('_token')->getViewData() ?? '') : '',
            'errors' => $errors,
            'values' => [
                'vorname' => (string) ($postData['vorname'] ?? ''),
                'nachname' => (string) ($postData['nachname'] ?? ''),
                'firmenname' => (string) ($postData['firmenname'] ?? ''),
                'email' => (string) ($postData['email'] ?? ''),
                'telefon' => (string) ($postData['telefon'] ?? ''),
                'adresse' => (string) ($postData['adresse'] ?? ''),
                'website' => (string) ($postData['website'] ?? ''),
                'ustId' => (string) ($postData['ustId'] ?? ''),
                'land' => (string) ($postData['land'] ?? 'Deutschland'),
                'firmenbeschreibung' => (string) ($postData['firmenbeschreibung'] ?? ''),
                'newsletter' => (bool) ($postData['newsletter'] ?? false),
                'plainPasswordFirst' => (string) ($plainPassword['first'] ?? ''),
                'plainPasswordSecond' => (string) ($plainPassword['second'] ?? ''),
            ],
        ];
    }
}
