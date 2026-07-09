<?php

namespace Tfd\PartnerWizardBundle\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardLead;

final class PartnerWizardMailer
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function sendLeadNotification(PartnerWizardClient $client, PartnerWizardLead $lead): void
    {
        if (!$client->isSendNotificationEmail() || !$client->getNotificationEmail()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($client->getNotificationEmail())
            ->subject('Neue Partner Wizard Anfrage: ' . $lead->getName())
            ->htmlTemplate('@TfdPartnerWizard/email/new_lead.html.twig')
            ->context([
                'client' => $client,
                'lead' => $lead,
            ]);

        $this->mailer->send($email);
    }
}
