<?php

namespace App\Portals\Chiptuningfile\Service;

use App\Entity\Kunde;
use App\Portals\Chiptuningfile\Entity\PortalPasswordResetToken;
use App\Portals\Chiptuningfile\Repository\PortalPasswordResetTokenRepository;
use App\Repository\KundeRepository;
use App\Portals\Chiptuningfile\Security\ChiptuningfilePortalAccessChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PortalPasswordResetService
{
    public function __construct(
        private readonly KundeRepository $kundeRepository,
        private readonly PortalPasswordResetTokenRepository $tokenRepository,
        private readonly ChiptuningfilePortalAccessChecker $accessChecker,
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire('%portal.mailer_from%')]
        private readonly string $fromAddress,
    ) {
    }

    public function requestReset(string $email): void
    {
        $customer = $this->kundeRepository->findOneBy(['email' => trim($email)]);
        if (!$customer instanceof Kunde || !$this->accessChecker->canAccess($customer)) {
            return;
        }

        $plainToken = bin2hex(random_bytes(32));
        $this->tokenRepository->invalidateActiveForCustomer($customer);

        $token = (new PortalPasswordResetToken())
            ->setKunde($customer)
            ->setTokenHash(PortalPasswordResetTokenRepository::hashToken($plainToken))
            ->setRequestedAt(new \DateTimeImmutable())
            ->setExpiresAt(new \DateTimeImmutable('+2 hours'));

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $resetUrl = $this->urlGenerator->generate('cpt_portal_password_reset_confirm', [
            'token' => $plainToken,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = (new Email())
            ->from(new Address($this->fromAddress, 'Chiptuningfile Portal'))
            ->to((string) $customer->getEmail())
            ->subject('Passwort fuer dein Chiptuningfile Portal zuruecksetzen')
            ->text($this->buildTextBody($customer, $resetUrl))
            ->html($this->buildHtmlBody($customer, $resetUrl));

        $this->mailer->send($message);
    }

    public function findValidToken(string $plainToken): ?PortalPasswordResetToken
    {
        return $this->tokenRepository->findValidByPlainToken($plainToken);
    }

    public function resetPassword(PortalPasswordResetToken $token, string $plainPassword): void
    {
        $customer = $token->getKunde();
        if (!$customer instanceof Kunde) {
            throw new \RuntimeException('Kein gueltiger Kunde zum Reset-Token gefunden.');
        }

        $customer
            ->setPasswort(password_hash($plainPassword, PASSWORD_BCRYPT))
            ->setPasswordChangedAt(new \DateTimeImmutable())
            ->eraseCredentials();

        $token->setUsedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    private function buildTextBody(Kunde $customer, string $resetUrl): string
    {
        return trim(sprintf(
            "Hallo %s,\n\num dein Passwort fuer das Chiptuningfile Portal zurueckzusetzen, oeffne bitte diesen Link:\n\n%s\n\nDer Link ist 2 Stunden gueltig.\n\nFalls du diese Anfrage nicht gestellt hast, kannst du diese E-Mail ignorieren.",
            (string) $customer,
            $resetUrl
        ));
    }

    private function buildHtmlBody(Kunde $customer, string $resetUrl): string
    {
        return sprintf(
            '<p>Hallo %s,</p><p>um dein Passwort fuer das Chiptuningfile Portal zurueckzusetzen, klicke bitte auf den folgenden Link:</p><p><a href="%s">%s</a></p><p>Der Link ist 2 Stunden gueltig.</p><p>Falls du diese Anfrage nicht gestellt hast, kannst du diese E-Mail ignorieren.</p>',
            htmlspecialchars((string) $customer, ENT_QUOTES),
            htmlspecialchars($resetUrl, ENT_QUOTES),
            htmlspecialchars($resetUrl, ENT_QUOTES)
        );
    }
}
