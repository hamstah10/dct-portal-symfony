<?php

namespace App\Controller\Admin;

use App\Entity\Kunde;
use App\Entity\LexwareInvoice;
use App\Portals\Chiptuningfile\Service\PortalInvoiceStatusService;
use App\Repository\KundeRepository;
use App\Repository\LexwareInvoiceRepository;
use App\Service\Lexware\LexwareApiException;
use App\Service\Lexware\LexwareService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LexwareInvoiceController extends AbstractController
{
    public function __construct(
        private readonly KundeRepository $kundeRepository,
        private readonly LexwareInvoiceRepository $lexwareInvoiceRepository,
        private readonly LexwareService $lexwareService,
        private readonly PortalInvoiceStatusService $portalInvoiceStatusService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[AdminRoute(path: '/lexware/invoice', name: 'lexware_invoice', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        $customers = $this->kundeRepository->findBy([], ['nachname' => 'ASC']);
        $recentInvoices = $this->lexwareInvoiceRepository->findBy([], ['createdAt' => 'DESC'], 20);
        $recentInvoiceViews = $this->portalInvoiceStatusService->buildInvoiceViews($recentInvoices);

        $formData = [
            'kunde' => '',
            'lineItems' => [
                [
                    'article' => '',
                    'quantity' => '1',
                ],
            ],
            'paymentNote' => '',
            'paymentTermDays' => '0',
            'finalize' => true,
        ];

        $createdInvoice = null;
        $error = null;
        $errorDetails = null;
        $debugPayload = null;

        if ($request->isMethod('POST')) {
            $formData['kunde'] = (string) $request->request->get('kunde', '');
            $formData['lineItems'] = $this->normalizeLineItemsInput($request->request->all('lineItems'));
            $formData['paymentNote'] = (string) $request->request->get('paymentNote', '');
            $formData['paymentTermDays'] = (string) $request->request->get('paymentTermDays', '0');
            $formData['finalize'] = '1' === (string) $request->request->get('finalize', '1');

            if (empty($formData['lineItems'])) {
                $legacyArticle = (string) $request->request->get('article', '');
                $legacyQuantity = (string) $request->request->get('quantity', '1');
                if ('' !== trim($legacyArticle)) {
                    $formData['lineItems'] = [
                        [
                            'article' => $legacyArticle,
                            'quantity' => $legacyQuantity,
                        ],
                    ];
                }
            }

            try {
                $createdInvoice = $this->handleCreateInvoice($formData, $debugPayload);
                $this->addFlash('success', 'Rechnung wurde erstellt.');
            } catch (LexwareApiException $exception) {
                $error = sprintf('Lexoffice Fehler (%d): %s', $exception->getStatusCode(), $exception->getMessage());
                $errorDetails = [
                    'url' => $exception->getRequestUrl(),
                    'response' => $exception->getResponseBody(),
                ];
                $detailsText = $exception->getResponseBody();
                if (null !== $detailsText && '' !== trim($detailsText)) {
                    $error .= ' | Details: ' . trim($detailsText);
                }
                $requestUrl = $exception->getRequestUrl();
                if (null !== $requestUrl && '' !== trim($requestUrl)) {
                    $error .= ' | URL: ' . trim($requestUrl);
                }
            } catch (\RuntimeException $exception) {
                $error = $exception->getMessage();
            }
        }

        return $this->render('admin/lexware/invoice.html.twig', [
            'customers' => $customers,
            'recentInvoiceViews' => $recentInvoiceViews,
            'createdInvoice' => $createdInvoice,
            'createdInvoiceView' => $createdInvoice instanceof LexwareInvoice ? $this->portalInvoiceStatusService->buildInvoiceView($createdInvoice) : null,
            'error' => $error,
            'errorDetails' => $errorDetails,
            'debugPayload' => $debugPayload,
            'formData' => $formData,
        ]);
    }

    #[AdminRoute(path: '/lexware/invoice/articles', name: 'lexware_invoice_articles', options: ['methods' => ['GET']])]
    public function listArticles(Request $request): JsonResponse
    {
        $size = (int) $request->query->get('size', 50);
        $size = max(25, min(200, $size));

        try {
            $data = $this->lexwareService->products()->list(['page' => 0, 'size' => $size]);
        } catch (LexwareApiException $exception) {
            return $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'details' => $exception->getResponseBody(),
            ], 500);
        }

        $items = $data['content'] ?? $data['items'] ?? $data['data'] ?? [];

        return $this->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * @param array<string, mixed> $formData
     */
    private function handleCreateInvoice(array $formData, ?array &$debugPayload = null): LexwareInvoice
    {
        $kundeId = (int) ($formData['kunde'] ?? 0);
        $lineItems = $formData['lineItems'] ?? [];
        $finalize = (bool) ($formData['finalize'] ?? true);
        $paymentNote = trim((string) ($formData['paymentNote'] ?? ''));
        $paymentTermDays = (int) ($formData['paymentTermDays'] ?? 0);

        if ($kundeId <= 0) {
            throw new \RuntimeException('Bitte einen Kunden auswählen.');
        }
        if (!is_array($lineItems) || [] === $lineItems) {
            throw new \RuntimeException('Bitte mindestens ein Lexoffice Produkt auswählen.');
        }

        $kunde = $this->kundeRepository->find($kundeId);
        if (!$kunde instanceof Kunde) {
            throw new \RuntimeException('Kunde nicht gefunden.');
        }

        if (null === $debugPayload) {
            $debugPayload = [];
        }

        $contactId = $this->ensureLexwareContact($kunde, $debugPayload);

        $articleCache = [];
        $resolvedItems = [];
        foreach ($lineItems as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $articleId = trim((string) ($item['article'] ?? ''));
            if ('' === $articleId) {
                throw new \RuntimeException(sprintf('Position %d: Bitte ein Lexoffice Produkt auswählen.', $index + 1));
            }

            $quantity = (float) str_replace(',', '.', (string) ($item['quantity'] ?? '1'));
            if ($quantity <= 0) {
                throw new \RuntimeException(sprintf('Position %d: Menge muss größer als 0 sein.', $index + 1));
            }

            $snapshotArticle = null;
            if (isset($item['snapshot']) && is_string($item['snapshot'])) {
                $decodedSnapshot = json_decode($item['snapshot'], true);
                if (is_array($decodedSnapshot)) {
                    $snapshotArticle = $decodedSnapshot;
                }
            }

            if (null !== $snapshotArticle && !empty($snapshotArticle)) {
                $articleCache[$articleId] = $snapshotArticle;
            }

            if (!array_key_exists($articleId, $articleCache) || empty($articleCache[$articleId]['price'])) {
                $articleCache[$articleId] = $this->lexwareService->products()->get($articleId);
            }

            $article = $articleCache[$articleId] ?? [];
            if (empty($article)) {
                throw new \RuntimeException(sprintf('Position %d: Produkt konnte nicht geladen werden.', $index + 1));
            }

            $resolvedItems[] = [
                'article' => $article,
                'quantity' => $quantity,
            ];
        }

        if ([] === $resolvedItems) {
            throw new \RuntimeException('Bitte mindestens ein Lexoffice Produkt auswählen.');
        }

        $payload = $this->buildInvoicePayload($kunde, $resolvedItems, $contactId, $paymentNote, $paymentTermDays);
        $debugPayload['invoice'] = $payload;
        $response = $this->lexwareService->invoices()->create($payload, $finalize);
        $invoiceId = (string) ($response['id'] ?? '');
        if ('' === $invoiceId) {
            throw new \RuntimeException('Keine Invoice-ID von Lexoffice erhalten.');
        }

        $details = $this->lexwareService->invoices()->get($invoiceId);
        $voucherNumber = isset($details['voucherNumber']) ? (string) $details['voucherNumber'] : null;
        $status = isset($details['status']) ? (string) $details['status'] : ($finalize ? 'open' : 'draft');

        $filePath = null;
        if ($finalize) {
            $filePath = $this->downloadInvoicePdf($invoiceId);
        }

        $record = (new LexwareInvoice())
            ->setKunde($kunde)
            ->setLexwareInvoiceId($invoiceId)
            ->setVoucherNumber($voucherNumber)
            ->setStatus($status)
            ->setFilePath($filePath);

        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return $record;
    }

    /**
     * @param array<array<string, mixed>> $items
     *
     * @return array<string, mixed>
     */
    private function buildInvoicePayload(Kunde $kunde, array $resolvedItems, ?string $contactId, string $paymentNote, int $paymentTermDays): array
    {
        $customerName = trim(sprintf('%s %s', $kunde->getVorname() ?? '', $kunde->getNachname() ?? ''));
        $company = trim((string) $kunde->getFirmenname());
        $name = '' !== $company ? $company : $customerName;

        $street = trim((string) $kunde->getStrasse());
        $houseNumber = trim((string) $kunde->getHausnummer());
        $streetLine = trim($street . ' ' . $houseNumber);

        $zip = trim((string) $kunde->getPlz());
        $city = trim((string) $kunde->getStadt());
        $countryCode = $this->resolveCountryCode($kunde->getLand());

        if ('' === $name && null === $contactId) {
            throw new \RuntimeException('Kunde hat keinen Namen.');
        }
        if ('' === $streetLine || '' === $zip || '' === $city) {
            throw new \RuntimeException('Kundenadresse ist unvollständig (Straße, PLZ, Stadt).');
        }

        $lineItems = [];
        $taxType = null;
        $currency = null;
        foreach ($resolvedItems as $item) {
            if (!is_array($item) || !isset($item['article'], $item['quantity'])) {
                continue;
            }

            $result = $this->buildLineItemFromArticle((array) $item['article'], (float) $item['quantity']);
            $lineItems[] = $result['lineItem'];

            if (null === $taxType) {
                $taxType = $result['taxType'];
            } elseif ($taxType !== $result['taxType']) {
                throw new \RuntimeException('Alle Positionen müssen das gleiche Preismodell (Netto/Brutto) verwenden.');
            }

            if (null === $currency) {
                $currency = $result['currency'];
            } elseif ($currency !== $result['currency']) {
                throw new \RuntimeException('Alle Positionen müssen die gleiche Währung verwenden.');
            }
        }

        if ([] === $lineItems) {
            throw new \RuntimeException('Bitte mindestens eine Position hinzufügen.');
        }

        $voucherDate = (new \DateTimeImmutable('today'))->format('Y-m-d\\T00:00:00.000P');

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

        $payload = [
            'archived' => false,
            'voucherDate' => $voucherDate,
            'address' => $address,
            'lineItems' => $lineItems,
            'totalPrice' => [
                'currency' => $currency ?? 'EUR',
            ],
            'taxConditions' => [
                'taxType' => $taxType ?? 'net',
            ],
            'shippingConditions' => [
                'shippingType' => 'none',
            ],
        ];

        if (null !== $contactId) {
            $payload['buyerReference'] = '';
        }

        if ('' !== $paymentNote) {
            $payload['paymentConditions'] = [
                'paymentTermLabel' => $paymentNote,
                'paymentTermDuration' => max(0, $paymentTermDays),
            ];
        }

        return $payload;
    }

    /**
     * @param mixed $input
     *
     * @return array<int, array{article:string, quantity:string}>
     */
    private function normalizeLineItemsInput(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }

        $items = [];
        foreach ($input as $row) {
            if (!is_array($row)) {
                continue;
            }

            $article = trim((string) ($row['article'] ?? ''));
            $quantity = (string) ($row['quantity'] ?? '1');
            $snapshot = $row['snapshot'] ?? null;

            if ('' === $article) {
                continue;
            }

            $item = [
                'article' => $article,
                'quantity' => $quantity,
            ];

            if (is_string($snapshot) && '' !== trim($snapshot)) {
                $item['snapshot'] = $snapshot;
            }

            $items[] = $item;
        }

        return $items;
    }

    private function ensureLexwareContact(Kunde $kunde, ?array &$debugPayload = null): ?string
    {
        $existing = trim((string) $kunde->getLexwareContactId());
        if ('' !== $existing) {
            return $existing;
        }

        $email = trim((string) $kunde->getEmail());
        $name = trim((string) ($kunde->getFirmenname() ?: trim(sprintf('%s %s', $kunde->getVorname() ?? '', $kunde->getNachname() ?? ''))));

        $query = ['customer' => 'true'];
        if ('' !== $email) {
            $query['email'] = $email;
        } elseif ('' !== $name) {
            $query['name'] = $name;
        }

        if (isset($query['email']) || isset($query['name'])) {
            if (is_array($debugPayload)) {
                $debugPayload['contactLookup'] = $query;
            }
            $existingContacts = $this->lexwareService->contacts()->list($query);
            $content = $existingContacts['content'] ?? $existingContacts['items'] ?? $existingContacts['data'] ?? [];
            if (is_array($content) && [] !== $content) {
                $contactId = trim((string) ($content[0]['id'] ?? ''));
                if ('' !== $contactId) {
                    $kunde->setLexwareContactId($contactId);
                    $this->entityManager->persist($kunde);
                    $this->entityManager->flush();

                    return $contactId;
                }
            }
        }

        $payload = $this->buildContactPayload($kunde);
        if (is_array($debugPayload)) {
            $debugPayload['contactPayload'] = $payload;
        }
        $created = $this->lexwareService->contacts()->create($payload);
        $contactId = trim((string) ($created['id'] ?? ''));
        if ('' === $contactId) {
            throw new \RuntimeException('Lexoffice Kontakt konnte nicht erstellt werden.');
        }

        $kunde->setLexwareContactId($contactId);
        $this->entityManager->persist($kunde);
        $this->entityManager->flush();

        return $contactId;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContactPayload(Kunde $kunde): array
    {
        $company = trim((string) $kunde->getFirmenname());
        $firstName = trim((string) $kunde->getVorname());
        $lastName = trim((string) $kunde->getNachname());
        $email = trim((string) $kunde->getEmail());
        $phone = trim((string) $kunde->getTelefon());

        $street = trim((string) $kunde->getStrasse());
        $houseNumber = trim((string) $kunde->getHausnummer());
        $streetLine = trim($street . ' ' . $houseNumber);
        $zip = trim((string) $kunde->getPlz());
        $city = trim((string) $kunde->getStadt());
        $countryCode = $this->resolveCountryCode($kunde->getLand());

        $payload = [
            'version' => 0,
            'roles' => [
                'customer' => new \stdClass(),
            ],
        ];

        if ('' !== $company) {
            $payload['company'] = [
                'name' => $company,
            ];
        } else {
            if ('' === $lastName && '' === $firstName) {
                throw new \RuntimeException('Kunde hat keinen Namen für Lexoffice Kontakt.');
            }

            $payload['person'] = [
                'firstName' => $firstName,
                'lastName' => $lastName !== '' ? $lastName : $firstName,
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
                'billing' => [
                    [
                        'street' => $streetLine,
                        'zip' => $zip,
                        'city' => $city,
                        'countryCode' => $countryCode,
                    ],
                ],
            ];
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $article
     *
     * @return array{lineItem: array<string, mixed>, taxType: string, currency: string}
     */
    private function buildLineItemFromArticle(array $article, float $quantity): array
    {
        $articleName = (string) ($article['title'] ?? $article['name'] ?? 'Artikel');
        $unitName = (string) ($article['unitName'] ?? 'Stk');
        $price = is_array($article['price'] ?? null) ? $article['price'] : [];

        $leadingPrice = strtoupper((string) ($price['leadingPrice'] ?? 'NET'));
        $taxRate = (float) ($price['taxRate'] ?? $price['taxRatePercentage'] ?? 19);
        $taxType = 'GROSS' === $leadingPrice ? 'gross' : 'net';
        $currency = (string) ($price['currency'] ?? 'EUR');

        $grossValue = $price['grossPrice'] ?? $price['grossAmount'] ?? null;
        $netValue = $price['netPrice'] ?? $price['netAmount'] ?? null;
        $rateFactor = 1 + ($taxRate / 100);

        if (null === $netValue && null !== $grossValue) {
            $netValue = $taxRate > 0 ? ((float) $grossValue / $rateFactor) : (float) $grossValue;
        }
        if (null === $grossValue && null !== $netValue) {
            $grossValue = (float) $netValue * $rateFactor;
        }

        $unitPrice = [
            'currency' => $currency,
            'taxRatePercentage' => $taxRate,
            'netAmount' => round((float) ($netValue ?? 0.0), 4),
            'grossAmount' => round((float) ($grossValue ?? 0.0), 4),
        ];

        return [
            'lineItem' => [
                'type' => 'custom',
                'name' => $articleName,
                'quantity' => $quantity,
                'unitName' => $unitName,
                'unitPrice' => $unitPrice,
            ],
            'taxType' => $taxType,
            'currency' => $currency,
        ];
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

        if (!is_array($file) || '' === ($file['content'] ?? '')) {
            return null;
        }

        $projectDir = $this->getParameter('kernel.project_dir');
        $relativeDir = 'uploads/lexoffice-invoices/' . date('Y/m');
        $absoluteDir = $projectDir . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0775, true);
        }

        $filename = $file['filename'] ?? sprintf('lexoffice-invoice-%s.pdf', $invoiceId);
        $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', (string) $filename);
        $path = $absoluteDir . '/' . $filename;
        file_put_contents($path, (string) $file['content']);

        return $relativeDir . '/' . $filename;
    }
}
