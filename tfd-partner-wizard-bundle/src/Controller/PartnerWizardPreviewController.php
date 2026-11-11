<?php

namespace Tfd\PartnerWizardBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Tfd\PartnerWizardBundle\Controller\Api\PartnerWizardTextController;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;

final class PartnerWizardPreviewController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/partner-wizard/preview/{clientId}', name: 'tfd_partner_wizard_preview', methods: ['GET'])]
    public function preview(string $clientId, Request $request): Response
    {
        $client = $this->em->getRepository(PartnerWizardClient::class)->findOneBy(['clientId' => $clientId]);
        if (!$client instanceof PartnerWizardClient) {
            throw $this->createNotFoundException('Partner-Wizard-Client nicht gefunden.');
        }

        // Text-Bearbeitungsmodus nur für Admins (leitet sonst zum Login).
        $editMode = $request->query->getBoolean('edit');
        if ($editMode) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        return $this->render('@TfdPartnerWizard/preview/wizard.html.twig', [
            'client' => $client,
            'editMode' => $editMode,
            'canEdit' => $editMode || $this->isGranted('ROLE_ADMIN'),
            'editCsrf' => $editMode ? $this->csrfTokenManager->getToken(PartnerWizardTextController::CSRF_TOKEN_ID)->getValue() : null,
        ]);
    }

    #[Route('/partner-wizard/embed/{clientId}', name: 'tfd_partner_wizard_embed', methods: ['GET'])]
    public function embed(string $clientId, Request $request): Response
    {
        $client = $this->em->getRepository(PartnerWizardClient::class)->findOneBy(['clientId' => $clientId]);
        if (!$client instanceof PartnerWizardClient) {
            throw $this->createNotFoundException('Partner-Wizard-Client nicht gefunden.');
        }

        $base = $request->getSchemeAndHttpHost();
        $theme = 'light' === $client->getTheme() ? 'light' : 'dark';
        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $code = sprintf(
            '<script src="%s/widgets/partner-wizard/embed.js" data-client-id="%s" data-primary-color="%s" data-theme="%s" async></script>',
            $e($base), $e($client->getClientId()), $e($client->getPrimaryColor()), $theme
        );

        return $this->render('@TfdPartnerWizard/preview/embed.html.twig', [
            'client' => $client,
            'code' => $code,
        ]);
    }
}
