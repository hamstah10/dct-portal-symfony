<?php

namespace App\Portals\Chiptuningfile\Service;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Kunde;
use App\Entity\Portal;
use App\Repository\FirmaRepository;
use App\Repository\KundeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class PortalRegistrationService
{
    public function __construct(
        private readonly KundeRepository $kundeRepository,
        private readonly FirmaRepository $firmaRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        #[Autowire('%portal.mailer_from%')]
        private readonly string $fromAddress,
        #[Autowire('%portal.registration_notify_to%')]
        private readonly string $registrationNotifyTo,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function register(array $payload): Kunde
    {
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        if ('' === $email) {
            throw new \InvalidArgumentException('E-Mail ist erforderlich.');
        }

        $firmenname = trim((string) ($payload['firmenname'] ?? ''));
        if ('' === $firmenname) {
            throw new \InvalidArgumentException('Firma ist erforderlich.');
        }

        $adresse = trim((string) ($payload['adresse'] ?? ''));
        if ('' === $adresse) {
            throw new \InvalidArgumentException('Adresse ist erforderlich.');
        }

        $land = trim((string) ($payload['land'] ?? 'Deutschland'));
        if ('' === $land) {
            throw new \InvalidArgumentException('Land ist erforderlich.');
        }

        if ($this->kundeRepository->findOneBy(['email' => $email]) instanceof Kunde) {
            throw new \InvalidArgumentException('Zu dieser E-Mail existiert bereits ein Kundenkonto.');
        }

        $portalConfig = $this->portalRuntimeResolver->resolveCurrentPortal();
        $firma = $this->firmaRepository->findActiveByKuerzel('CPT') ?? $portalConfig?->getFirma();
        if (null === $firma) {
            throw new \RuntimeException('Die Firma chiptuningfile.de (CPT) wurde nicht gefunden oder ist inaktiv.');
        }

        $plainPassword = trim((string) ($payload['plainPassword'] ?? ''));
        if ('' === $plainPassword) {
            throw new \InvalidArgumentException('Passwort ist erforderlich.');
        }

        $customer = (new Kunde())
            ->setFirma($firma)
            ->setVorname(trim((string) ($payload['vorname'] ?? '')))
            ->setNachname(trim((string) ($payload['nachname'] ?? '')))
            ->setEmail($email)
            ->setFirmenname($firmenname)
            ->setTelefon($this->normalizeNullableString($payload['telefon'] ?? null))
            ->setStrasse($adresse)
            ->setLand($land)
            ->setWebsite($this->normalizeNullableString($payload['website'] ?? null))
            ->setUstId($this->normalizeNullableString($payload['ustId'] ?? null))
            ->setFirmenbeschreibung($this->normalizeNullableString($payload['firmenbeschreibung'] ?? null))
            ->setNewsletter((bool) ($payload['newsletter'] ?? false))
            ->setGesperrt(false)
            ->setPasswort(password_hash($plainPassword, PASSWORD_BCRYPT))
            ->setPasswordChangedAt(new \DateTimeImmutable());

        $this->entityManager->persist($customer);
        $this->entityManager->flush();
        $this->sendRegistrationMails($customer, $firma, $portalConfig);

        return $customer;
    }

    private function sendRegistrationMails(Kunde $customer, object $firma, ?Portal $portalConfig): void
    {
        try {
            $this->sendCustomerConfirmation($customer, $portalConfig);
            $this->sendInternalNotification($customer, $firma);
        } catch (\Throwable $throwable) {
            $this->logger->warning('Portal registration mail could not be sent.', [
                'customer_email' => $customer->getEmail(),
                'exception' => $throwable,
            ]);
        }
    }

    private function sendCustomerConfirmation(Kunde $customer, ?Portal $portalConfig): void
    {
        $portalName = $portalConfig?->getName() ?: 'Chiptuningfile';
        $message = (new Email())
            ->from(new Address($this->fromAddress, $portalName . ' Portal'))
            ->to((string) $customer->getEmail())
            ->subject(sprintf('Dein %s Portal Konto wurde erstellt', $portalName))
            ->text($this->buildCustomerTextBody($customer, $portalConfig))
            ->html($this->buildCustomerHtmlBody($customer, $portalConfig));

        $this->mailer->send($message);
    }

    private function sendInternalNotification(Kunde $customer, object $firma): void
    {
        $recipient = trim($this->registrationNotifyTo);
        if ('' === $recipient && method_exists($firma, 'getEmail')) {
            $recipient = trim((string) $firma->getEmail());
        }

        if ('' === $recipient) {
            return;
        }

        $message = (new Email())
            ->from(new Address($this->fromAddress, 'Chiptuningfile Portal'))
            ->to($recipient)
            ->subject('Neue Portal-Registrierung bei Chiptuningfile')
            ->text($this->buildInternalTextBody($customer))
            ->html($this->buildInternalHtmlBody($customer));

        $this->mailer->send($message);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return '' === $normalized ? null : $normalized;
    }

    private function buildCustomerTextBody(Kunde $customer, ?Portal $portalConfig): string
    {
        $portalName = $portalConfig?->getName() ?: 'Chiptuningfile';
        $portalUrl = ($portalConfig?->getPortalUrl() ?: 'https://chiptuningfile.tuningfux.de') . '/login';

        return trim(sprintf(
            "Hallo %s,\n\nDein Kundenkonto fuer das %s Portal wurde erfolgreich erstellt.\n\nDu kannst dich jetzt unter %s einloggen.\n\nViele Gruesse\n%s",
            (string) $customer
            ,
            $portalName,
            $portalUrl,
            $portalName
        ));
    }

    private function buildCustomerHtmlBody(Kunde $customer, ?Portal $portalConfig): string
    {
        $portalName = $portalConfig?->getName() ?: 'Chiptuningfile';
        $portalUrl = ($portalConfig?->getPortalUrl() ?: 'https://chiptuningfile.tuningfux.de') . '/login';

        return sprintf(
            '<p>Hallo %s,</p><p>dein Kundenkonto fuer das %s Portal wurde erfolgreich erstellt.</p><p>Du kannst dich jetzt unter <a href="%s">%s</a> einloggen.</p><p>Viele Gruesse<br>%s</p>',
            htmlspecialchars((string) $customer, ENT_QUOTES),
            htmlspecialchars($portalName, ENT_QUOTES),
            htmlspecialchars($portalUrl, ENT_QUOTES),
            htmlspecialchars($portalUrl, ENT_QUOTES),
            htmlspecialchars($portalName, ENT_QUOTES)
        );
    }

    private function buildInternalTextBody(Kunde $customer): string
    {
        return trim(sprintf(
            "Neue Registrierung im Chiptuningfile Portal\n\nName: %s\nE-Mail: %s\nFirma: %s\nTelefon: %s",
            (string) $customer,
            (string) $customer->getEmail(),
            (string) ($customer->getFirmenname() ?: $customer->getFirma()?->getName() ?: '-'),
            (string) ($customer->getTelefon() ?: '-')
        ));
    }

    private function buildInternalHtmlBody(Kunde $customer): string
    {
        return sprintf(
            '<p>Neue Registrierung im Chiptuningfile Portal</p><ul><li><strong>Name:</strong> %s</li><li><strong>E-Mail:</strong> %s</li><li><strong>Firma:</strong> %s</li><li><strong>Telefon:</strong> %s</li></ul>',
            htmlspecialchars((string) $customer, ENT_QUOTES),
            htmlspecialchars((string) $customer->getEmail(), ENT_QUOTES),
            htmlspecialchars((string) ($customer->getFirmenname() ?: $customer->getFirma()?->getName() ?: '-'), ENT_QUOTES),
            htmlspecialchars((string) ($customer->getTelefon() ?: '-'), ENT_QUOTES)
        );
    }
}
