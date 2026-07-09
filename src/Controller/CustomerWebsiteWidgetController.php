<?php

namespace App\Controller;

use App\Entity\Kunde;
use App\Entity\WebsiteWidgetConfig;
use App\Form\WebsiteWidgetConfigFormType;
use App\Repository\WebsiteWidgetConfigRepository;
use App\Security\WebsiteWidgetCustomerUser;
use App\Service\WebsiteWidgetConfigManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CustomerWebsiteWidgetController extends AbstractController
{
    public function __construct(
        private readonly WebsiteWidgetConfigRepository $widgetRepository,
        private readonly WebsiteWidgetConfigManager $widgetManager,
    ) {
    }

    #[Route('/kunden-widget/login', name: 'customer_widget_login', methods: ['GET', 'POST'])]
    public function login(
        AuthenticationUtils $authenticationUtils,
        CsrfTokenManagerInterface $csrfTokenManager,
        TranslatorInterface $translator,
    ): Response {
        if ($this->getUser() instanceof WebsiteWidgetCustomerUser) {
            return $this->redirectToRoute('customer_widget_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('customer_widget/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'csrf_token' => $csrfTokenManager->getToken('customer_widget_authenticate')->getValue(),
            'error' => null !== $error
                ? $translator->trans($error->getMessageKey(), $error->getMessageData(), 'security')
                : null,
        ]);
    }

    #[Route('/kunden-widget', name: 'customer_widget_index', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = $this->getCurrentCustomer();
        $widget = $this->widgetRepository->findLatestForCustomer($customer);
        $isNew = !$widget instanceof WebsiteWidgetConfig;

        if ($isNew) {
            $widget = $this->widgetManager->createForCustomer($customer);
        }

        $form = $this->createForm(WebsiteWidgetConfigFormType::class, $widget);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $widget->setKunde($customer);
            $this->widgetManager->prepareForSave($widget, $entityManager, $isNew);
            $entityManager->persist($widget);
            $entityManager->flush();

            $this->addFlash('success', $isNew ? 'Widget wurde angelegt.' : 'Widget wurde gespeichert.');

            return $this->redirectToRoute('customer_widget_index');
        }

        return $this->render('customer_widget/edit.html.twig', [
            'form' => $form->createView(),
            'widget' => $widget,
            'isNew' => $isNew,
            'customer' => $customer,
            'widgetUrl' => $isNew ? null : $this->buildWidgetUrl($widget),
            'javascriptSnippet' => $isNew ? null : $this->buildJavascriptSnippet($widget),
            'iframeSnippet' => $isNew ? null : $this->buildIframeSnippet($widget),
        ]);
    }

    #[Route('/kunden-widget/logout', name: 'customer_widget_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \LogicException('This method is intercepted by the firewall logout handler.');
    }

    private function getCurrentCustomer(): Kunde
    {
        $user = $this->getUser();
        if (!$user instanceof WebsiteWidgetCustomerUser) {
            throw $this->createAccessDeniedException();
        }

        return $user->getCustomer();
    }

    private function buildWidgetUrl(WebsiteWidgetConfig $widget): string
    {
        return $this->generateUrl('app_website_widget_show', ['embedKey' => $widget->getEmbedKey()], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function buildJavascriptSnippet(WebsiteWidgetConfig $widget): string
    {
        return sprintf(
            '<div id="tuningfux-widget"></div>' . "\n" . '<script src="%s"></script>',
            $this->generateUrl('app_website_widget_embed_js', ['embedKey' => $widget->getEmbedKey()], UrlGeneratorInterface::ABSOLUTE_URL)
        );
    }

    private function buildIframeSnippet(WebsiteWidgetConfig $widget): string
    {
        return sprintf(
            '<iframe src="%s" width="100%%" height="%d" frameborder="0" style="border:none;"></iframe>',
            $this->buildWidgetUrl($widget),
            $widget->getIframeHeight()
        );
    }
}
