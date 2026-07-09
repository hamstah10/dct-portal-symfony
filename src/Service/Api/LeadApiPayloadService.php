<?php

namespace App\Service\Api;

use App\Api\ApiValidationException;
use App\Entity\Lead;

final class LeadApiPayloadService
{
    /**
     * @return array<string, mixed>
     */
    public function normalizeLead(Lead $lead): array
    {
        return [
            'id' => $lead->getId(),
            'firma' => $lead->getFirma(),
            'vorname' => $lead->getVorname(),
            'nachname' => $lead->getNachname(),
            'email' => $lead->getEmail(),
            'telefon' => $lead->getTelefon(),
            'quelle' => $lead->getQuelle(),
            'status' => $lead->getStatus(),
            'newsletter' => $lead->isNewsletter(),
            'notiz' => $lead->getNotiz(),
            'createdAt' => $lead->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $lead->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function applyPayload(Lead $lead, array $payload): void
    {
        $firma = $this->readNullableString($payload, ['firma', 'company'], 255);
        $vorname = $this->readNullableString($payload, ['vorname', 'firstName', 'first_name'], 120);
        $nachname = $this->readNullableString($payload, ['nachname', 'lastName', 'last_name'], 120);
        $email = $this->readNullableString($payload, ['email'], 180);
        $telefon = $this->readNullableString($payload, ['telefon', 'phone'], 80);
        $quelle = $this->readNullableString($payload, ['quelle', 'source'], 120);
        $notiz = $this->readNullableString($payload, ['notiz', 'note']);
        $status = $this->readNullableString($payload, ['status'], 50);
        $newsletter = $this->readNullableBool($payload, ['newsletter']);

        if (null !== $email && false === filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new ApiValidationException('email ist keine gueltige E-Mail-Adresse.');
        }

        if (
            null === $firma
            && null === $vorname
            && null === $nachname
            && null === $email
            && null === $telefon
        ) {
            throw new ApiValidationException('Mindestens eines der Felder firma, vorname, nachname, email oder telefon muss gesetzt sein.');
        }

        if (null !== $status) {
            $allowedStatuses = array_values(Lead::getStatusChoices());
            if (!in_array($status, $allowedStatuses, true)) {
                throw new ApiValidationException(sprintf(
                    'status ist ungueltig. Erlaubt sind: %s',
                    implode(', ', $allowedStatuses)
                ));
            }
        }

        $lead
            ->setFirma($firma)
            ->setVorname($vorname)
            ->setNachname($nachname)
            ->setEmail($email)
            ->setTelefon($telefon)
            ->setQuelle($quelle)
            ->setNewsletter($newsletter ?? false)
            ->setNotiz($notiz);

        if (null !== $status) {
            $lead->setStatus($status);
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<string> $keys
     */
    private function readNullableString(array $payload, array $keys, ?int $maxLength = null): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = $payload[$key];
            if (null === $value) {
                return null;
            }

            if (!is_scalar($value)) {
                throw new ApiValidationException(sprintf('%s muss ein String sein.', $key));
            }

            $normalized = trim((string) $value);
            if ('' === $normalized) {
                return null;
            }

            if (null !== $maxLength && mb_strlen($normalized) > $maxLength) {
                throw new ApiValidationException(sprintf('%s ist zu lang (max. %d Zeichen).', $key, $maxLength));
            }

            return $normalized;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<string> $keys
     */
    private function readNullableBool(array $payload, array $keys): ?bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $payload)) {
                continue;
            }

            $value = $payload[$key];

            if (is_bool($value)) {
                return $value;
            }

            if (is_int($value) || is_string($value)) {
                $normalized = filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);
                if (null !== $normalized) {
                    return $normalized;
                }
            }

            throw new ApiValidationException(sprintf('%s muss true oder false sein.', $key));
        }

        return null;
    }
}
