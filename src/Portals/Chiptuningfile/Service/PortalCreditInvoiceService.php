<?php

declare(strict_types=1);

namespace App\Portals\Chiptuningfile\Service;

use App\Entity\CreditPackage;
use App\Entity\Kunde;
use App\Entity\LexwareInvoice;
use App\Service\Lexware\LexwareApiException;
use App\Service\Lexware\LexwareService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class PortalCreditInvoiceService
{
    public function __construct(
        private readonly LexwareService $lexwareService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @param array{
     *     title:string,
     *     credits:int,
     *     priceNet:float,
     *     priceGross:float,
     *     taxRate:float,
     *     paymentFee:float
     * } $pricing
     */
    public function createInvoiceForCreditTopup(Kunde $customer, CreditPackage $package, array $pricing): LexwareInvoice
    {
        $contactId = $this->ensureLexwareContact($customer);

        $payload = $this->buildInvoicePayload($customer, $package, $pricing, $contactId);
        $response = $this->lexwareService->invoices()->create($payload, true);
        $invoiceId = trim((string) ($response['id'] ?? ''));
        if ('' === $invoiceId) {
            throw new \RuntimeException('Keine Lexoffice Invoice-ID erhalten.');
        }

        $details = $this->lexwareService->invoices()->get($invoiceId);
        $remoteStatus = trim((string) ($details['status'] ?? ''));
        if ('' !== $remoteStatus && 'paid' !== strtolower($remoteStatus)) {
            $this->logger->info('Lexoffice invoice remains open after PayPal capture; keeping local portal invoice as paid.', [
                'customerId' => $customer->getId(),
                'invoiceId' => $invoiceId,
                'remoteStatus' => $remoteStatus,
            ]);
        }

        $voucherNumber = trim((string) ($details['voucherNumber'] ?? ''));
        $status = trim((string) ($details['status'] ?? 'paid'));
        if ('' === $status || 'paid' !== strtolower($status)) {
            $status = 'paid';
        }

        $filePath = $this->safeDownloadInvoicePdf($invoiceId);

        return $this->persistInvoiceRecord($customer, $invoiceId, $voucherNumber, $status, $filePath);
    }

    public function createInvoiceForPrepayment(
        Kunde $customer,
        int $requestedCredits,
        float $priceNet,
        float $priceGross,
        float $taxRate = 19.0,
    ): LexwareInvoice {
        if ($requestedCredits <= 0) {
            throw new \RuntimeException('Ungueltige Credit-Anzahl fuer Vorkasse.');
        }

        if ($priceNet <= 0 || $priceGross <= 0) {
            throw new \RuntimeException('Ungueltiger Preis fuer Vorkasse.');
        }

        $contactId = $this->ensureLexwareContact($customer);
        $payload = $this->buildPrepaymentInvoicePayload($customer, $requestedCredits, $priceNet, $priceGross, $taxRate, $contactId);

        $response = $this->lexwareService->invoices()->create($payload, true);
        $invoiceId = trim((string) ($response['id'] ?? ''));
        if ('' === $invoiceId) {
            throw new \RuntimeException('Keine Lexoffice Invoice-ID erhalten.');
        }

        $details = $this->lexwareService->invoices()->get($invoiceId);
        $voucherNumber = trim((string) ($details['voucherNumber'] ?? ''));
        $status = trim((string) ($details['status'] ?? 'open'));
        if ('' === $status) {
            $status = 'open';
        }

        $filePath = $this->safeDownloadInvoicePdf($invoiceId);

        return $this->persistInvoiceRecord($customer, $invoiceId, $voucherNumber, $status, $filePath);
    }

    public function createInvoiceForExactCreditTopup(
        Kunde $customer,
        int $requestedCredits,
        float $priceNet,
        float $priceGross,
        float $taxRate = 19.0,
    ): LexwareInvoice {
        if ($requestedCredits <= 0) {
            throw new \RuntimeException('Ungueltige Credit-Anzahl fuer Kreditkauf.');
        }

        if ($priceNet <= 0 || $priceGross <= 0) {
            throw new \RuntimeException('Ungueltiger Preis fuer Kreditkauf.');
        }

        $contactId = $this->ensureLexwareContact($customer);
        $payload = $this->buildExactCreditTopupInvoicePayload($customer, $requestedCredits, $priceNet, $priceGross, $taxRate, $contactId);

        $response = $this->lexwareService->invoices()->create($payload, true);
        $invoiceId = trim((string) ($response['id'] ?? ''));
        if ('' === $invoiceId) {
            throw new \RuntimeException('Keine Lexoffice Invoice-ID erhalten.');
        }

        $details = $this->lexwareService->invoices()->get($invoiceId);
        $voucherNumber = trim((string) ($details['voucherNumber'] ?? ''));
        $status = trim((string) ($details['status'] ?? 'paid'));
        if ('' === $status || 'paid' !== strtolower($status)) {
            $status = 'paid';
        }

        $filePath = $this->safeDownloadInvoicePdf($invoiceId);

        return $this->persistInvoiceRecord($customer, $invoiceId, $voucherNumber, $status, $filePath);
    }

    /**
     * @param array{
     *     title:string,
     *     credits:int,
     *     priceNet:float,
     *     priceGross:float,
     *     taxRate:float,
     *     paymentFee:float
     * } $pricing
     *
     * @return array<string, mixed>
     */
    private function buildInvoicePayload(Kunde $customer, CreditPackage $package, array $pricing, ?string $contactId): array
    {
        $customerName = trim(sprintf('%s %s', (string) $customer->getVorname(), (string) $customer->getNachname()));
        $company = trim((string) $customer->getFirmenname());
        $name = '' !== $company ? $company : $customerName;

        $street = trim((string) $customer->getStrasse());
        $houseNumber = trim((string) $customer->getHausnummer());
        $streetLine = trim($street . ' ' . $houseNumber);
        $zip = trim((string) $customer->getPlz());
        $city = trim((string) $customer->getStadt());
        $countryCode = $this->resolveCountryCode($customer->getLand());

        if ('' === $name && null === $contactId) {
            throw new \RuntimeException('Kunde hat keinen Namen für die Rechnung.');
        }

        if ('' === $streetLine || '' === $zip || '' === $city) {
            throw new \RuntimeException('Kundenadresse ist unvollständig (Straße, PLZ, Stadt).');
        }

        $address = [
            'street' => $streetLine,
            'zip' => $zip,
            'city' => $city,
            'countryCode' => $countryCode,
        ];
        if ('' !== $name) {
            $address['name'] = $name;
        }
        if (null !== $contactId) {
            $address['contactId'] = $contactId;
        }

        $lineItemName = sprintf(
            'Credit-Paket %s (%d Credits)',
            (string) ($package->getTitle() ?? (string) $package),
            $package->getCredits()
        );

        $voucherDate = (new \DateTimeImmutable('today'))->format('Y-m-d\\T00:00:00.000P');

        return [
            'archived' => false,
            'voucherDate' => $voucherDate,
            'address' => $address,
            'lineItems' => [[
                'type' => 'custom',
                'name' => $lineItemName,
                'quantity' => 1.0,
                'unitName' => 'Paket',
                'unitPrice' => [
                    'currency' => 'EUR',
                    'taxRatePercentage' => (float) $pricing['taxRate'],
                    'netAmount' => round((float) $pricing['priceNet'], 4),
                    'grossAmount' => round((float) $pricing['priceGross'], 4),
                ],
            ]],
            'totalPrice' => [
                'currency' => 'EUR',
            ],
            'taxConditions' => [
                'taxType' => 'net',
            ],
            'shippingConditions' => [
                'shippingType' => 'none',
            ],
            'paymentConditions' => [
                'paymentTermLabel' => 'Bezahlt via PayPal',
                'paymentTermDuration' => 0,
            ],
            'title' => 'Rechnung',
            'introduction' => 'Vielen Dank für den Kauf deiner Credits.',
            'remark' => 'PayPal-Zahlung erfolgreich verbucht.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPrepaymentInvoicePayload(
        Kunde $customer,
        int $requestedCredits,
        float $priceNet,
        float $priceGross,
        float $taxRate,
        ?string $contactId,
    ): array {
        $customerName = trim(sprintf('%s %s', (string) $customer->getVorname(), (string) $customer->getNachname()));
        $company = trim((string) $customer->getFirmenname());
        $name = '' !== $company ? $company : $customerName;

        $street = trim((string) $customer->getStrasse());
        $houseNumber = trim((string) $customer->getHausnummer());
        $streetLine = trim($street . ' ' . $houseNumber);
        $zip = trim((string) $customer->getPlz());
        $city = trim((string) $customer->getStadt());
        $countryCode = $this->resolveCountryCode($customer->getLand());

        if ('' === $name && null === $contactId) {
            throw new \RuntimeException('Kunde hat keinen Namen für die Rechnung.');
        }

        if ('' === $streetLine || '' === $zip || '' === $city) {
            throw new \RuntimeException('Kundenadresse ist unvollständig (Straße, PLZ, Stadt).');
        }

        $address = [
            'street' => $streetLine,
            'zip' => $zip,
            'city' => $city,
            'countryCode' => $countryCode,
        ];
        if ('' !== $name) {
            $address['name'] = $name;
        }
        if (null !== $contactId) {
            $address['contactId'] = $contactId;
        }

        $voucherDate = (new \DateTimeImmutable('today'))->format('Y-m-d\\T00:00:00.000P');

        return [
            'archived' => false,
            'voucherDate' => $voucherDate,
            'address' => $address,
            'lineItems' => [[
                'type' => 'custom',
                'name' => sprintf('Vorkasse Credits (%d Credits)', $requestedCredits),
                'quantity' => 1.0,
                'unitName' => 'Anfrage',
                'unitPrice' => [
                    'currency' => 'EUR',
                    'taxRatePercentage' => round($taxRate, 2),
                    'netAmount' => round($priceNet, 4),
                    'grossAmount' => round($priceGross, 4),
                ],
            ]],
            'totalPrice' => [
                'currency' => 'EUR',
            ],
            'taxConditions' => [
                'taxType' => 'net',
            ],
            'shippingConditions' => [
                'shippingType' => 'none',
            ],
            'paymentConditions' => [
                'paymentTermLabel' => 'Vorkasse',
                'paymentTermDuration' => 7,
            ],
            'title' => 'Rechnung',
            'introduction' => 'Vorkasse-Rechnung für Credits.',
            'remark' => 'Credits werden nach Zahlungseingang gutgeschrieben.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildExactCreditTopupInvoicePayload(
        Kunde $customer,
        int $requestedCredits,
        float $priceNet,
        float $priceGross,
        float $taxRate,
        ?string $contactId,
    ): array {
        $customerName = trim(sprintf('%s %s', (string) $customer->getVorname(), (string) $customer->getNachname()));
        $company = trim((string) $customer->getFirmenname());
        $name = '' !== $company ? $company : $customerName;

        $street = trim((string) $customer->getStrasse());
        $houseNumber = trim((string) $customer->getHausnummer());
        $streetLine = trim($street . ' ' . $houseNumber);
        $zip = trim((string) $customer->getPlz());
        $city = trim((string) $customer->getStadt());
        $countryCode = $this->resolveCountryCode($customer->getLand());

        if ('' === $name && null === $contactId) {
            throw new \RuntimeException('Kunde hat keinen Namen für die Rechnung.');
        }

        if ('' === $streetLine || '' === $zip || '' === $city) {
            throw new \RuntimeException('Kundenadresse ist unvollständig (Straße, PLZ, Stadt).');
        }

        $address = [
            'street' => $streetLine,
            'zip' => $zip,
            'city' => $city,
            'countryCode' => $countryCode,
        ];
        if ('' !== $name) {
            $address['name'] = $name;
        }
        if (null !== $contactId) {
            $address['contactId'] = $contactId;
        }

        $voucherDate = (new \DateTimeImmutable('today'))->format('Y-m-d\\T00:00:00.000P');

        return [
            'archived' => false,
            'voucherDate' => $voucherDate,
            'address' => $address,
            'lineItems' => [[
                'type' => 'custom',
                'name' => sprintf('Credit-Kauf (%d Credits)', $requestedCredits),
                'quantity' => 1.0,
                'unitName' => 'Kauf',
                'unitPrice' => [
                    'currency' => 'EUR',
                    'taxRatePercentage' => round($taxRate, 2),
                    'netAmount' => round($priceNet, 4),
                    'grossAmount' => round($priceGross, 4),
                ],
            ]],
            'totalPrice' => [
                'currency' => 'EUR',
            ],
            'taxConditions' => [
                'taxType' => 'net',
            ],
            'shippingConditions' => [
                'shippingType' => 'none',
            ],
            'paymentConditions' => [
                'paymentTermLabel' => 'Bezahlt via PayPal',
                'paymentTermDuration' => 0,
            ],
            'title' => 'Rechnung',
            'introduction' => 'Vielen Dank für deinen Credit-Kauf.',
            'remark' => 'PayPal-Zahlung erfolgreich verbucht.',
        ];
    }

    private function ensureLexwareContact(Kunde $customer): ?string
    {
        $existing = trim((string) $customer->getLexwareContactId());
        if ('' !== $existing) {
            try {
                $this->lexwareService->contacts()->get($existing);

                return $existing;
            } catch (LexwareApiException $exception) {
                if (404 !== $exception->getStatusCode() && 406 !== $exception->getStatusCode()) {
                    throw $exception;
                }

                $customer->setLexwareContactId(null);
                $this->entityManager->persist($customer);
                $this->entityManager->flush();
            }
        }

        $email = trim((string) $customer->getEmail());
        $name = trim((string) ($customer->getFirmenname() ?: trim(sprintf('%s %s', (string) $customer->getVorname(), (string) $customer->getNachname()))));

        $query = ['customer' => 'true'];
        if ('' !== $email) {
            $query['email'] = $email;
        } elseif ('' !== $name) {
            $query['name'] = $name;
        }

        if (isset($query['email']) || isset($query['name'])) {
            $existingContacts = $this->lexwareService->contacts()->list($query);
            $content = $existingContacts['content'] ?? $existingContacts['items'] ?? $existingContacts['data'] ?? [];
            if (is_array($content) && [] !== $content) {
                $contactId = trim((string) ($content[0]['id'] ?? ''));
                if ('' !== $contactId) {
                    $customer->setLexwareContactId($contactId);
                    $this->entityManager->persist($customer);
                    $this->entityManager->flush();

                    return $contactId;
                }
            }
        }

        $payload = $this->buildContactPayload($customer);
        $created = $this->lexwareService->contacts()->create($payload);
        $contactId = trim((string) ($created['id'] ?? ''));
        if ('' === $contactId) {
            throw new \RuntimeException('Lexoffice Kontakt konnte nicht erstellt werden.');
        }

        $customer->setLexwareContactId($contactId);
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $contactId;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContactPayload(Kunde $customer): array
    {
        $company = trim((string) $customer->getFirmenname());
        $firstName = trim((string) $customer->getVorname());
        $lastName = trim((string) $customer->getNachname());
        $email = trim((string) $customer->getEmail());
        $phone = trim((string) $customer->getTelefon());

        $street = trim((string) $customer->getStrasse());
        $houseNumber = trim((string) $customer->getHausnummer());
        $streetLine = trim($street . ' ' . $houseNumber);
        $zip = trim((string) $customer->getPlz());
        $city = trim((string) $customer->getStadt());
        $countryCode = $this->resolveCountryCode($customer->getLand());

        $payload = [
            'version' => 0,
            'roles' => [
                'customer' => new \stdClass(),
            ],
        ];

        if ('' !== $company) {
            $payload['company'] = ['name' => $company];
        } else {
            if ('' === $lastName && '' === $firstName) {
                throw new \RuntimeException('Kunde hat keinen Namen für Lexoffice Kontakt.');
            }
            $payload['person'] = [
                'firstName' => $firstName,
                'lastName' => '' !== $lastName ? $lastName : $firstName,
            ];
        }

        if ('' !== $email) {
            $payload['emailAddresses'] = [
                'business' => [$email],
            ];
        }

        if ('' !== $phone) {
            $payload['phoneNumbers'] = [
                'business' => [$phone],
            ];
        }

        if ('' !== $streetLine && '' !== $zip && '' !== $city) {
            $payload['addresses'] = [
                'billing' => [[
                    'street' => $streetLine,
                    'zip' => $zip,
                    'city' => $city,
                    'countryCode' => $countryCode,
                ]],
            ];
        }

        return $payload;
    }

    private function resolveCountryCode(?string $country): string
    {
        $normalized = strtolower(trim((string) $country));
        if ('' === $normalized) {
            return 'DE';
        }
        if (strlen($normalized) === 2) {
            return strtoupper($normalized);
        }
        if (str_contains($normalized, 'deutsch')) {
            return 'DE';
        }
        if (str_contains($normalized, 'österreich') || str_contains($normalized, 'austria')) {
            return 'AT';
        }
        if (str_contains($normalized, 'schweiz') || str_contains($normalized, 'switzerland')) {
            return 'CH';
        }
        if (str_contains($normalized, 'niederlande') || str_contains($normalized, 'netherlands')) {
            return 'NL';
        }

        return 'DE';
    }

    private function downloadInvoicePdf(string $invoiceId): ?string
    {
        $file = null;

        try {
            $file = $this->lexwareService->invoices()->downloadFile($invoiceId);
        } catch (LexwareApiException $exception) {
            if (406 === $exception->getStatusCode()) {
                $this->lexwareService->invoices()->renderDocument($invoiceId);
                $file = $this->lexwareService->invoices()->downloadFile($invoiceId);
            } else {
                throw $exception;
            }
        }

        if (!is_array($file) || '' === ((string) ($file['content'] ?? ''))) {
            return null;
        }

        $relativeDir = 'uploads/lexoffice-invoices/' . date('Y/m');
        $absoluteDir = rtrim($this->projectDir, '/') . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new \RuntimeException('Lexoffice Invoice-Verzeichnis konnte nicht erstellt werden.');
        }

        $filename = (string) ($file['filename'] ?? sprintf('lexoffice-invoice-%s.pdf', $invoiceId));
        $filename = (string) preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
        $absolutePath = $absoluteDir . '/' . $filename;

        if (false === file_put_contents($absolutePath, (string) $file['content'])) {
            throw new \RuntimeException('Lexoffice Invoice-PDF konnte nicht gespeichert werden.');
        }

        return $relativeDir . '/' . $filename;
    }

    private function safeDownloadInvoicePdf(string $invoiceId): ?string
    {
        try {
            return $this->downloadInvoicePdf($invoiceId);
        } catch (\Throwable $exception) {
            $this->logger->warning('Lexoffice invoice PDF could not be downloaded.', [
                'invoiceId' => $invoiceId,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function persistInvoiceRecord(
        Kunde $customer,
        string $invoiceId,
        ?string $voucherNumber,
        string $status,
        ?string $filePath,
    ): LexwareInvoice {
        $record = $this->entityManager->getRepository(LexwareInvoice::class)->findOneBy([
            'lexwareInvoiceId' => $invoiceId,
        ]);

        if (!$record instanceof LexwareInvoice) {
            $record = (new LexwareInvoice())
                ->setKunde($customer)
                ->setLexwareInvoiceId($invoiceId);
        }

        $record
            ->setKunde($customer)
            ->setVoucherNumber(null !== $voucherNumber && '' !== trim($voucherNumber) ? $voucherNumber : null)
            ->setStatus($status)
            ->setFilePath($filePath);

        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return $record;
    }
}
