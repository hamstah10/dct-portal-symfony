<?php

namespace Tfd\PartnerWizardBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;

final class PartnerWizardPreviewController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('/partner-wizard/preview/{clientId}', name: 'tfd_partner_wizard_preview', methods: ['GET'])]
    public function preview(string $clientId): Response
    {
        $client = $this->em->getRepository(PartnerWizardClient::class)->findOneBy(['clientId' => $clientId]);
        if (!$client instanceof PartnerWizardClient) {
            throw $this->createNotFoundException('Partner-Wizard-Client nicht gefunden.');
        }

        return $this->render('@TfdPartnerWizard/preview/wizard.html.twig', [
            'client' => $client,
        ]);
    }
}
