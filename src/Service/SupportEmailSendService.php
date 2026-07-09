<?php

namespace App\Service;

use App\Entity\EmailKonto;
use App\Entity\SupportEmailMessage;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class SupportEmailSendService
{
    public function send(SupportEmailMessage $message, EmailKonto $account): void
    {
        if (!$account->isActive()) {
            throw new \RuntimeException('E-Mail-Konto ist inaktiv.');
        }
        if (!$account->isSendEnabled()) {
            throw new \RuntimeException('Versand ist fuer dieses Konto deaktiviert.');
        }

        $toAddresses = $this->parseRecipientList($message->getToEmail());
        if ([] === $toAddresses) {
            throw new \RuntimeException('Es wurde kein gueltiger Empfaenger gefunden.');
        }

        $email = (new Email())
            ->from($this->createFromAddress($account))
            ->to(...$toAddresses)
            ->subject($this->normalizeSubject($message->getSubject()));

        $ccAddresses = $this->parseRecipientList($message->getCcEmail());
        if ([] !== $ccAddresses) {
            $email->cc(...$ccAddresses);
        }

        $bccAddresses = $this->parseRecipientList($message->getBccEmail());
        if ([] !== $bccAddresses) {
            $email->bcc(...$bccAddresses);
        }

        $textBody = trim($message->getBodyText());
        $htmlBody = trim((string) ($message->getBodyHtml() ?? ''));

        if ('' === $textBody && '' === $htmlBody) {
            $textBody = '(Kein Inhalt)';
        }

        if ('' !== $htmlBody) {
            $email->html($htmlBody);
        }

        if ('' !== $textBody) {
            $email->text($textBody);
        } elseif ('' !== $htmlBody) {
            $email->text(trim(strip_tags($htmlBody)));
        }

        $this->sendWithTlsFallback($email, $account);
    }

    private function sendWithTlsFallback(Email $email, EmailKonto $account): void
    {
        try {
            $mailer = new Mailer($this->createTransport($account, false));
            $mailer->send($email);

            return;
        } catch (\Throwable $throwable) {
            if (!$this->isTlsCertificateVerificationError($throwable)) {
                throw $throwable;
            }
        }

        $fallbackMailer = new Mailer($this->createTransport($account, true));
        $fallbackMailer->send($email);
    }

    private function createTransport(EmailKonto $account, bool $disablePeerVerification): TransportInterface
    {
        $dsn = $this->buildSmtpDsn($account, $disablePeerVerification);

        try {
            return Transport::fromDsn($dsn);
        } catch (\Throwable $throwable) {
            $message = str_replace($dsn, '[redacted-dsn]', trim($throwable->getMessage()));
            throw new \RuntimeException('SMTP-Konfiguration ungueltig: ' . $message, 0, $throwable);
        }
    }

    private function buildSmtpDsn(EmailKonto $account, bool $disablePeerVerification): string
    {
        $host = trim($account->getSmtpHost());
        if ('' === $host) {
            throw new \RuntimeException('SMTP Host fehlt.');
        }

        $port = max(1, $account->getSmtpPort());
        $encryption = strtolower(trim($account->getSmtpEncryption()));
        $username = trim((string) ($account->getSmtpUsername() ?: $account->getEmailAddress()));
        $password = trim((string) ($account->getSmtpPassword() ?? ''));

        $credentials = '';
        if ('' !== $username) {
            $credentials = rawurlencode($username);
            if ('' !== $password) {
                $credentials .= ':' . rawurlencode($password);
            }
            $credentials .= '@';
        }

        $useImplicitTls = EmailKonto::ENCRYPTION_SSL === $encryption
            || (465 === $port && in_array($encryption, [EmailKonto::ENCRYPTION_TLS, EmailKonto::ENCRYPTION_STARTTLS], true));

        $queryParams = [];
        if (EmailKonto::ENCRYPTION_NONE === $encryption) {
            // Explizit ohne STARTTLS arbeiten, wenn "Keine" ausgewaehlt wurde.
            $queryParams['auto_tls'] = 'false';
        } elseif (!$useImplicitTls && (EmailKonto::ENCRYPTION_TLS === $encryption || EmailKonto::ENCRYPTION_STARTTLS === $encryption)) {
            $queryParams['encryption'] = 'tls';
            $queryParams['require_tls'] = 'true';
        }
        if ($disablePeerVerification) {
            // Fallback fuer Server mit selbstsigniertem/ungueltigem Zertifikat (z.B. Mailcow ohne gueltige Chain).
            $queryParams['verify_peer'] = '0';
        }

        $query = '';
        if ([] !== $queryParams) {
            $query = '?' . http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        }

        $scheme = $useImplicitTls ? 'smtps' : 'smtp';

        return sprintf(
            '%s://%s%s:%d%s',
            $scheme,
            $credentials,
            $host,
            $port,
            $query
        );
    }

    private function isTlsCertificateVerificationError(\Throwable $throwable): bool
    {
        for ($current = $throwable; null !== $current; $current = $current->getPrevious()) {
            $message = strtolower(trim($current->getMessage()));
            if ('' === $message) {
                continue;
            }

            if (str_contains($message, 'certificate verify failed')) {
                return true;
            }
        }

        return false;
    }

    private function createFromAddress(EmailKonto $account): Address
    {
        $fromEmail = trim($account->getEmailAddress());
        if ('' === $fromEmail) {
            throw new \RuntimeException('Absenderadresse des Kontos fehlt.');
        }

        $fromName = trim((string) ($account->getFromName() ?: $account->getName()));

        return '' !== $fromName
            ? new Address($fromEmail, $fromName)
            : new Address($fromEmail);
    }

    private function normalizeSubject(string $subject): string
    {
        $normalized = trim($subject);

        return '' !== $normalized ? $normalized : '(Ohne Betreff)';
    }

    /**
     * @return array<int, Address>
     */
    private function parseRecipientList(?string $raw): array
    {
        $value = trim((string) ($raw ?? ''));
        if ('' === $value) {
            return [];
        }

        $parts = preg_split('/[,;\n\r]+/', $value) ?: [];
        $addresses = [];
        $invalidParts = [];

        foreach ($parts as $part) {
            $candidate = trim((string) $part);
            if ('' === $candidate) {
                continue;
            }

            if (preg_match('/<([^>]+)>/', $candidate, $matches)) {
                $candidate = trim((string) ($matches[1] ?? $candidate));
            }

            if (false === filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                $invalidParts[] = $part;
                continue;
            }

            $lower = strtolower($candidate);
            $addresses[$lower] = new Address($candidate);
        }

        if ([] !== $invalidParts) {
            throw new \RuntimeException(sprintf('Ungueltige Empfaengeradresse(n): %s', implode(', ', $invalidParts)));
        }

        return array_values($addresses);
    }
}
