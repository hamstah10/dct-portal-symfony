<?php

namespace App\Service\Api;

use App\Api\ApiValidationException;
use App\Entity\Firma;
use App\Entity\Kunde;
use App\Repository\FirmaRepository;

final class CustomerApiPayloadService
{
    public function __construct(
        private readonly FirmaRepository $firmaRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeCustomer(Kunde $customer): array
    {
        return [
            'id' => $customer->getId(),
            'kundennummer' => $customer->getKundennummer(),
            'firmenname' => $customer->getFirmenname(),
            'vorname' => $customer->getVorname(),
            'nachname' => $customer->getNachname(),
            'email' => $customer->getEmail(),
            'firmaId' => $customer->getFirma()?->getId(),
            'telefon' => $customer->getTelefon(),
            'ustId' => $customer->getUstId(),
            'steuernummer' => $customer->getSteuernummer(),
            'strasse' => $customer->getStrasse(),
            'hausnummer' => $customer->getHausnummer(),
            'plz' => $customer->getPlz(),
            'stadt' => $customer->getStadt(),
            'land' => $customer->getLand(),
            'website' => $customer->getWebsite(),
            'hasDyno' => $customer->hasDyno(),
            'tuningTools' => $customer->getTuningTools(),
            'diagnoseTools' => $customer->getDiagnoseTools(),
            'bild' => $customer->getBild(),
            'zahlungsbedingungen' => $customer->getZahlungsbedingungen(),
            'registrierungsdatum' => $customer->getRegistrierungsdatum()?->format('Y-m-d'),
            'newsletter' => $customer->isNewsletter(),
            'gesperrt' => $customer->isGesperrt(),
            'createdAt' => $customer->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'passwordChangedAt' => $customer->getPasswordChangedAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array{id:int|null,companyName:string|null,email:string|null,username:string|null,gesperrt:bool}
     */
    public function normalizeAuthCustomer(Kunde $customer): array
    {
        return [
            'id' => $customer->getId(),
            'companyName' => $customer->getFirmenname() ?: $customer->getFirma()?->getName(),
            'email' => $customer->getEmail(),
            'username' => $customer->getEmail(),
            'gesperrt' => $customer->isGesperrt(),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function applyPayload(Kunde $customer, array $payload, bool $partial): void
    {
        if (!$partial) {
            foreach (['vorname', 'nachname', 'email', 'firmaId'] as $field) {
                if (!\array_key_exists($field, $payload)) {
                    throw new ApiValidationException(sprintf('Pflichtfeld fehlt: %s', $field));
                }
            }
        }

        $this->setString($payload, 'kundennummer', fn (?string $value) => $customer->setKundennummer($value), 50);
        $this->setString($payload, 'firmenname', fn (?string $value) => $customer->setFirmenname($value), 255);
        $this->setRequiredString($payload, 'vorname', fn (string $value) => $customer->setVorname($value));
        $this->setRequiredString($payload, 'nachname', fn (string $value) => $customer->setNachname($value));
        $this->setRequiredString($payload, 'email', fn (string $value) => $customer->setEmail($value));
        $this->setString($payload, 'telefon', fn (?string $value) => $customer->setTelefon($value), 80);
        $this->setString($payload, 'ustId', fn (?string $value) => $customer->setUstId($value), 50);
        $this->setString($payload, 'steuernummer', fn (?string $value) => $customer->setSteuernummer($value), 50);
        $this->setString($payload, 'strasse', fn (?string $value) => $customer->setStrasse($value), 255);
        $this->setString($payload, 'hausnummer', fn (?string $value) => $customer->setHausnummer($value), 30);
        $this->setString($payload, 'plz', fn (?string $value) => $customer->setPlz($value), 20);
        $this->setString($payload, 'stadt', fn (?string $value) => $customer->setStadt($value), 120);
        $this->setString($payload, 'land', fn (?string $value) => $customer->setLand($value), 120);
        $this->setString($payload, 'website', fn (?string $value) => $customer->setWebsite($value), 255);
        $this->setString($payload, 'bild', fn (?string $value) => $customer->setBild($value), 255);
        $this->setString($payload, 'zahlungsbedingungen', fn (?string $value) => $customer->setZahlungsbedingungen($value));

        if (\array_key_exists('hasDyno', $payload)) {
            $customer->setHasDyno((bool) $payload['hasDyno']);
        }

        if (\array_key_exists('tuningTools', $payload)) {
            $customer->setTuningTools($payload['tuningTools']);
        }

        if (\array_key_exists('diagnoseTools', $payload)) {
            $customer->setDiagnoseTools($payload['diagnoseTools']);
        }

        if (\array_key_exists('firmaId', $payload)) {
            $firma = $this->resolveFirma($payload['firmaId']);
            $customer->setFirma($firma);
        }

        if (\array_key_exists('newsletter', $payload)) {
            $customer->setNewsletter((bool) $payload['newsletter']);
        }

        if (\array_key_exists('gesperrt', $payload)) {
            $customer->setGesperrt((bool) $payload['gesperrt']);
        }

        if (\array_key_exists('registrierungsdatum', $payload)) {
            $customer->setRegistrierungsdatum($this->parseDate($payload['registrierungsdatum']));
        }

        $plainPassword = null;
        if (\array_key_exists('plainPassword', $payload)) {
            $plainPassword = $payload['plainPassword'];
        } elseif (\array_key_exists('plainPasswort', $payload)) {
            $plainPassword = $payload['plainPasswort'];
        }

        if (null !== $plainPassword) {
            $plainPassword = trim((string) $plainPassword);
            if ('' !== $plainPassword) {
                $customer->setPasswort(password_hash($plainPassword, PASSWORD_BCRYPT));
                $customer->setPasswordChangedAt(new \DateTimeImmutable());
                $customer->eraseCredentials();
            }
        }
    }

    private function resolveFirma(mixed $value): Firma
    {
        $id = (int) $value;
        if ($id <= 0) {
            throw new ApiValidationException('Ungueltige firmaId.');
        }

        $firma = $this->firmaRepository->find($id);
        if (!$firma instanceof Firma) {
            throw new ApiValidationException('Firma nicht gefunden.');
        }

        return $firma;
    }

    private function parseDate(mixed $value): ?\DateTimeImmutable
    {
        if (null === $value || '' === trim((string) $value)) {
            return null;
        }

        try {
            return new \DateTimeImmutable((string) $value);
        } catch (\Throwable) {
            throw new ApiValidationException('Ungueltiges Datum fuer registrierungsdatum.');
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @param callable(?string):void $setter
     */
    private function setString(array $payload, string $key, callable $setter, ?int $maxLength = null): void
    {
        if (!\array_key_exists($key, $payload)) {
            return;
        }

        $value = $payload[$key];
        if (null === $value) {
            $setter(null);

            return;
        }

        $stringValue = trim((string) $value);
        if ('' === $stringValue) {
            $setter(null);

            return;
        }

        if (null !== $maxLength && mb_strlen($stringValue) > $maxLength) {
            throw new ApiValidationException(sprintf('%s ist zu lang (max. %d Zeichen).', $key, $maxLength));
        }

        $setter($stringValue);
    }

    /**
     * @param array<string, mixed> $payload
     * @param callable(string):void $setter
     */
    private function setRequiredString(array $payload, string $key, callable $setter): void
    {
        if (!\array_key_exists($key, $payload)) {
            return;
        }

        $value = trim((string) $payload[$key]);
        if ('' === $value) {
            throw new ApiValidationException(sprintf('%s darf nicht leer sein.', $key));
        }

        $setter($value);
    }
}
