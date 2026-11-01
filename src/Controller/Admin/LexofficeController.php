<?php

namespace App\Controller\Admin;

use App\Entity\CreditPackage;
use App\Entity\Firma;
use App\Entity\Kunde;
use App\Repository\CreditPackageRepository;
use App\Repository\KundeRepository;
use App\Service\Lexware\LexwareApiException;
use App\Service\Lexware\LexwareService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class LexofficeController extends AbstractController
{
    public function __construct(
        private readonly LexwareService $lexwareService,
        private readonly EntityManagerInterface $entityManager,
        private readonly KundeRepository $kundeRepository,
        private readonly CreditPackageRepository $creditPackageRepository,
    ) {
    }

    #[AdminRoute(path: '/lexoffice', name: 'lexoffice', options: ['methods' => ['GET']])]
    public function index(): Response
    {
        $firmen = $this->entityManager->getRepository(Firma::class)->findBy([], ['name' => 'ASC']);
        $creditPackages = $this->creditPackageRepository->findBy([], ['id' => 'ASC']);

        return $this->render('admin/lexoffice/index.html.twig', [
            'firmen'         => $firmen,
            'creditPackages' => $creditPackages,
        ]);
    }

    #[Route('/admin/lexoffice/profile', name: 'admin_lexoffice_profile', methods: ['POST'])]
    public function profile(): JsonResponse
    {
        try {
            $data = $this->lexwareService->getProfile();

            return $this->json(['success' => true, 'data' => $data]);
        } catch (LexwareApiException $e) {
            return $this->lexError($e);
        }
    }

    #[Route('/admin/lexoffice/contacts', name: 'admin_lexoffice_contacts', methods: ['POST'])]
    public function contacts(Request $request): JsonResponse
    {
        try {
            $data = $this->lexwareService->contacts()->list($this->extractQuery($request));

            return $this->json(['success' => true, 'data' => $data]);
        } catch (LexwareApiException $e) {
            return $this->lexError($e);
        }
    }

    #[Route('/admin/lexoffice/products', name: 'admin_lexoffice_products', methods: ['POST'])]
    public function products(Request $request): JsonResponse
    {
        try {
            $data = $this->lexwareService->products()->list($this->extractQuery($request));

            return $this->json(['success' => true, 'data' => $data]);
        } catch (LexwareApiException $e) {
            return $this->lexError($e);
        }
    }

    #[Route('/admin/lexoffice/vouchers', name: 'admin_lexoffice_vouchers', methods: ['POST'])]
    public function vouchers(Request $request): JsonResponse
    {
        try {
            $query = $this->extractQuery($request);
            $query['voucherType'] = $query['voucherType'] ?? 'any';
            $query['voucherStatus'] = $query['voucherStatus'] ?? 'any';
            $data = $this->lexwareService->vouchers()->listVoucherList($query);

            return $this->json(['success' => true, 'data' => $data]);
        } catch (LexwareApiException $e) {
            return $this->lexError($e);
        }
    }

    #[Route('/admin/lexoffice/invoices', name: 'admin_lexoffice_invoices', methods: ['POST'])]
    public function invoices(Request $request): JsonResponse
    {
        try {
            $query = $this->extractQuery($request);
            $query['voucherType'] = 'salesinvoice';
            $query['voucherStatus'] = $query['voucherStatus'] ?? 'any';
            $data = $this->lexwareService->vouchers()->listVoucherList($query);

            return $this->json(['success' => true, 'data' => $data]);
        } catch (LexwareApiException $e) {
            return $this->lexError($e);
        }
    }

    #[Route('/admin/lexoffice/invoices/{id}/file', name: 'admin_lexoffice_invoice_file', methods: ['GET'])]
    public function invoiceFile(string $id): Response
    {
        $id = trim($id);
        if ('' === $id) {
            return new Response('Missing invoice id.', Response::HTTP_BAD_REQUEST);
        }

        try {
            $file = $this->lexwareService->invoices()->downloadFile($id, 'application/pdf');
        } catch (LexwareApiException $e) {
            return new Response(
                sprintf('Lexoffice Fehler (%d): %s', $e->getStatusCode(), $e->getMessage()),
                Response::HTTP_BAD_GATEWAY
            );
        }

        $content = $file['content'] ?? '';
        if ('' === $content) {
            return new Response('Leere Datei.', Response::HTTP_NO_CONTENT);
        }

        $contentType = $file['contentType'] ?? 'application/pdf';
        $filename = $file['filename'] ?? sprintf('lexoffice-invoice-%s.pdf', $id);

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => $contentType,
            'Content-Disposition' => (new ResponseHeaderBag())->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            ),
        ]);
    }

    /**
     * Prüft für eine Firma, welche Kunden noch KEINEN Lexoffice-Kontakt haben
     * und versucht sie per E-Mail-Suche in Lexoffice zu finden.
     */
    #[Route('/admin/lexoffice/check-firma-contacts', name: 'admin_lexoffice_check_firma_contacts', methods: ['POST'])]
    public function checkFirmaContacts(Request $request): JsonResponse
    {
        $firmaId = (int) $request->request->get('firmaId', 0);
        if ($firmaId <= 0) {
            return $this->json(['success' => false, 'message' => 'Bitte eine Firma auswählen.'], 400);
        }

        $firma = $this->entityManager->getRepository(Firma::class)->find($firmaId);
        if (!$firma instanceof Firma) {
            return $this->json(['success' => false, 'message' => 'Firma nicht gefunden.'], 404);
        }

        $kunden = $this->kundeRepository->findBy(['firma' => $firma]);
        if ([] === $kunden) {
            return $this->json(['success' => true, 'data' => [], 'summary' => ['total' => 0, 'found' => 0, 'missing' => 0, 'linked' => 0]]);
        }

        $results = [];
        $foundCount = 0;
        $missingCount = 0;
        $linkedCount = 0;

        foreach ($kunden as $kunde) {
            if (!$kunde instanceof Kunde) {
                continue;
            }

            $existingId = trim((string) $kunde->getLexwareContactId());
            $email = trim((string) $kunde->getEmail());
            $name = trim(sprintf('%s %s', $kunde->getVorname() ?? '', $kunde->getNachname() ?? ''));
            $company = trim((string) $kunde->getFirmenname());

            $row = [
                'kundeId' => $kunde->getId(),
                'kundennummer' => $kunde->getKundennummer(),
                'name' => '' !== $company ? $company : $name,
                'email' => $email,
                'lexwareContactId' => '' !== $existingId ? $existingId : null,
                'status' => null,
                'lexNumber' => null,
                'lexName' => null,
                'lexEmail' => null,
            ];

            if ('' !== $existingId) {
                $row['status'] = 'linked';
                ++$linkedCount;
                try {
                    $contact = $this->lexwareService->contacts()->get($existingId);
                    $row['lexNumber'] = $this->extractLexNumber($contact);
                    $row['lexName'] = $this->extractLexName($contact);
                    $row['lexEmail'] = $this->extractLexEmail($contact);
                } catch (LexwareApiException) {
                }
                $results[] = $row;
                continue;
            }

            $foundId = null;
            $foundLexNumber = null;
            $foundLexName = null;
            $foundLexEmail = null;

            try {
                if ('' !== $email) {
                    $resp = $this->lexwareService->contacts()->list(['customer' => 'true', 'email' => $email]);
                    $content = $resp['content'] ?? $resp['items'] ?? $resp['data'] ?? [];
                    if (is_array($content) && [] !== $content) {
                        $first = $content[0];
                        $foundId = trim((string) ($first['id'] ?? ''));
                        $foundLexNumber = $this->extractLexNumber($first);
                        $foundLexName = $this->extractLexName($first);
                        $foundLexEmail = $this->extractLexEmail($first);
                    }
                }

                if (null === $foundId && ('' !== $company || '' !== $name)) {
                    $searchName = '' !== $company ? $company : $name;
                    $resp = $this->lexwareService->contacts()->list(['customer' => 'true', 'name' => $searchName]);
                    $content = $resp['content'] ?? $resp['items'] ?? $resp['data'] ?? [];
                    if (is_array($content) && [] !== $content) {
                        $first = $content[0];
                        $foundId = trim((string) ($first['id'] ?? ''));
                        $foundLexNumber = $this->extractLexNumber($first);
                        $foundLexName = $this->extractLexName($first);
                        $foundLexEmail = $this->extractLexEmail($first);
                    }
                }
            } catch (LexwareApiException) {
                $row['status'] = 'error';
                $results[] = $row;
                continue;
            }

            if (null !== $foundId && '' !== $foundId) {
                $row['status'] = 'found';
                $row['lexwareContactId'] = $foundId;
                $row['lexNumber'] = $foundLexNumber;
                $row['lexName'] = $foundLexName;
                $row['lexEmail'] = $foundLexEmail;
                ++$foundCount;
            } else {
                $row['status'] = 'missing';
                ++$missingCount;
            }

            $results[] = $row;
        }

        return $this->json([
            'success' => true,
            'firma' => (string) $firma,
            'data' => $results,
            'summary' => [
                'total' => count($kunden),
                'linked' => $linkedCount,
                'found' => $foundCount,
                'missing' => $missingCount,
            ],
        ]);
    }

    /**
     * Legt einen fehlenden Kunden als neuen Lexoffice-Kontakt an.
     * Die Kundennummer wird ins Notiz-Feld geschrieben.
     */
    #[Route('/admin/lexoffice/sync-contact', name: 'admin_lexoffice_sync_contact', methods: ['POST'])]
    public function syncContact(Request $request): JsonResponse
    {
        $kundeId = (int) $request->request->get('kundeId', 0);
        if ($kundeId <= 0) {
            return $this->json(['success' => false, 'message' => 'Fehlende Kunden-ID.'], 400);
        }

        $kunde = $this->kundeRepository->find($kundeId);
        if (!$kunde instanceof Kunde) {
            return $this->json(['success' => false, 'message' => 'Kunde nicht gefunden.'], 404);
        }

        try {
            $payload = $this->buildSyncContactPayload($kunde);
            $result = $this->lexwareService->contacts()->create($payload);
            $lexId = trim((string) ($result['id'] ?? ''));

            if ('' === $lexId) {
                return $this->json(['success' => false, 'message' => 'Lexoffice hat keine ID zurückgegeben.'], 500);
            }

            $kunde->setLexwareContactId($lexId);
            $this->entityManager->persist($kunde);
            $this->entityManager->flush();

            $lexNumber = null;
            try {
                $contact = $this->lexwareService->contacts()->get($lexId);
                $lexNumber = $this->extractLexNumber($contact);
            } catch (LexwareApiException) {
            }

            return $this->json([
                'success' => true,
                'lexwareContactId' => $lexId,
                'lexNumber' => $lexNumber,
                'message' => sprintf('Kontakt angelegt und verknüpft (Lex-Nr.: %s).', $lexNumber ?? $lexId),
            ]);
        } catch (LexwareApiException $e) {
            return $this->lexError($e);
        } catch (\RuntimeException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Aktualisiert einen bestehenden Lexoffice-Kontakt mit den CRM-Daten.
     * Vergleicht Felder und übernimmt nur fehlende/geänderte Werte.
     * Ansprechpartner wird als contactPerson eingetragen wenn Vor-/Nachname vorhanden.
     */
    #[Route('/admin/lexoffice/update-contact', name: 'admin_lexoffice_update_contact', methods: ['POST'])]
    public function updateContact(Request $request): JsonResponse
    {
        $kundeId = (int) $request->request->get('kundeId', 0);
        if ($kundeId <= 0) {
            return $this->json(['success' => false, 'message' => 'Fehlende Kunden-ID.'], 400);
        }

        $kunde = $this->kundeRepository->find($kundeId);
        if (!$kunde instanceof Kunde) {
            return $this->json(['success' => false, 'message' => 'Kunde nicht gefunden.'], 404);
        }

        $lexId = trim((string) $kunde->getLexwareContactId());
        if ('' === $lexId) {
            return $this->json(['success' => false, 'message' => 'Kein Lexoffice-Kontakt verknüpft.'], 422);
        }

        try {
            $existing = $this->lexwareService->contacts()->get($lexId);
            $merged   = $this->mergeContactPayload($existing, $kunde);
            $changes  = $merged['_changes'] ?? [];
            unset($merged['_changes']);
            $this->lexwareService->contacts()->update($lexId, $merged);

            // PUT-Response enthält keine contactPersons — nochmal GET für aktuellen Stand
            $updated   = $this->lexwareService->contacts()->get($lexId);
            $lexNumber = $this->extractLexNumber($updated);

            return $this->json([
                'success'   => true,
                'lexNumber' => $lexNumber,
                'changes'   => $changes,
                'message'   => [] !== $changes
                    ? sprintf('Aktualisiert: %s.', implode(', ', $changes))
                    : 'Keine Änderungen notwendig.',
            ]);
        } catch (LexwareApiException $e) {
            return $this->lexError($e);
        } catch (\RuntimeException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    #[Route('/admin/lexoffice/link-contact', name: 'admin_lexoffice_link_contact', methods: ['POST'])]
    public function linkContact(Request $request): JsonResponse
    {
        $kundeId = (int) $request->request->get('kundeId', 0);
        $lexId = trim((string) $request->request->get('lexwareContactId', ''));

        if ($kundeId <= 0 || '' === $lexId) {
            return $this->json(['success' => false, 'message' => 'Fehlende Parameter.'], 400);
        }

        $kunde = $this->kundeRepository->find($kundeId);
        if (!$kunde instanceof Kunde) {
            return $this->json(['success' => false, 'message' => 'Kunde nicht gefunden.'], 404);
        }

        $kunde->setLexwareContactId($lexId);
        $this->entityManager->persist($kunde);
        $this->entityManager->flush();

        return $this->json(['success' => true, 'message' => sprintf('Kunde %s mit Lexoffice-Kontakt %s verknüpft.', (string) $kunde, $lexId)]);
    }

    #[Route('/admin/lexoffice/link-all-contacts', name: 'admin_lexoffice_link_all_contacts', methods: ['POST'])]
    public function linkAllContacts(Request $request): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = $request->request->all();
        }

        $items = $payload['items'] ?? [];
        if (!is_array($items) || [] === $items) {
            return $this->json(['success' => false, 'message' => 'Keine Einträge übermittelt.'], 400);
        }

        $linked = 0;
        $errors = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $kundeId = (int) ($item['kundeId'] ?? 0);
            $lexId = trim((string) ($item['lexwareContactId'] ?? ''));

            if ($kundeId <= 0 || '' === $lexId) {
                continue;
            }

            $kunde = $this->kundeRepository->find($kundeId);
            if (!$kunde instanceof Kunde) {
                $errors[] = "Kunde #{$kundeId} nicht gefunden.";
                continue;
            }

            $kunde->setLexwareContactId($lexId);
            $this->entityManager->persist($kunde);
            ++$linked;
        }

        if ($linked > 0) {
            $this->entityManager->flush();
        }

        return $this->json([
            'success' => true,
            'linked' => $linked,
            'errors' => $errors,
            'message' => sprintf('%d Kunden wurden verknüpft.', $linked),
        ]);
    }

    /**
     * Sync einzelnes Credit-Paket → Lexoffice Artikel (create oder update).
     */
    #[Route('/admin/lexoffice/sync-product', name: 'admin_lexoffice_sync_product', methods: ['POST'])]
    public function syncProduct(Request $request): JsonResponse
    {
        $packageId = (int) $request->request->get('packageId', 0);
        if ($packageId <= 0) {
            return $this->json(['success' => false, 'message' => 'Fehlende Paket-ID.'], 400);
        }

        $package = $this->creditPackageRepository->find($packageId);
        if (!$package instanceof CreditPackage) {
            return $this->json(['success' => false, 'message' => 'Paket nicht gefunden.'], 404);
        }

        try {
            [$articleId, $action] = $this->syncCreditPackageToLexoffice($package);

            return $this->json([
                'success'   => true,
                'articleId' => $articleId,
                'action'    => $action,
                'message'   => 'created' === $action ? 'Artikel angelegt.' : 'Artikel aktualisiert.',
            ]);
        } catch (LexwareApiException $e) {
            return $this->lexError($e);
        }
    }

    /**
     * Sync alle Credit-Pakete → Lexoffice Artikel.
     */
    #[Route('/admin/lexoffice/sync-all-products', name: 'admin_lexoffice_sync_all_products', methods: ['POST'])]
    public function syncAllProducts(): JsonResponse
    {
        $packages = $this->creditPackageRepository->findAll();
        $results  = [];

        foreach ($packages as $package) {
            if (!$package instanceof CreditPackage) {
                continue;
            }

            try {
                [$articleId, $action] = $this->syncCreditPackageToLexoffice($package);
                $results[] = [
                    'id'        => $package->getId(),
                    'title'     => $package->getTitle(),
                    'articleId' => $articleId,
                    'action'    => $action,
                    'success'   => true,
                ];
            } catch (LexwareApiException $e) {
                $results[] = [
                    'id'      => $package->getId(),
                    'title'   => $package->getTitle(),
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $created = count(array_filter($results, fn ($r) => ($r['action'] ?? '') === 'created'));
        $updated = count(array_filter($results, fn ($r) => ($r['action'] ?? '') === 'updated'));

        return $this->json([
            'success' => true,
            'results' => $results,
            'message' => sprintf('%d angelegt, %d aktualisiert.', $created, $updated),
        ]);
    }

    /**
     * @return array{string, string} [$articleId, 'created'|'updated']
     */
    private function syncCreditPackageToLexoffice(CreditPackage $package): array
    {
        $netPrice  = round((float) $package->getPrice(), 2);
        $grossPrice = round($netPrice * 1.19, 2);
        $category  = $package->getCategory();
        $firmas    = $package->getFirmas();
        $description = $category ? $category->getTitle() : 'Credits';
        $articleNumber = sprintf('CRD-%s-%04d', $category ? strtoupper(substr($category->getTitle(), 0, 3)) : 'PKG', $package->getCredits());

        $firmaNames = $firmas->map(function(Firma $f) { return $f->getName(); })->toArray();
        $note = implode(', ', $firmaNames);

        $payload = [
            'type'          => 'SERVICE',
            'title'         => (string) $package->getTitle(),
            'description'   => $description,
            'articleNumber' => $articleNumber,
            'unitName'      => 'Stück',
            'note'          => $note,
            'price'         => [
                'leadingPrice' => 'NET',
                'netPrice'     => $netPrice,
                'grossPrice'   => $grossPrice,
                'taxRate'      => 19,
            ],
        ];

        $existingId = trim((string) $package->getLexwareArticleId());

        if ('' !== $existingId) {
            // Update: version aus GET holen
            $existing = $this->lexwareService->products()->get($existingId);
            $payload['version'] = (int) ($existing['version'] ?? 0);
            $this->lexwareService->products()->update($existingId, $payload);

            return [$existingId, 'updated'];
        }

        // Create
        $result    = $this->lexwareService->products()->create($payload);
        $articleId = trim((string) ($result['id'] ?? ''));

        if ('' === $articleId) {
            throw new \RuntimeException('Lexoffice hat keine Artikel-ID zurückgegeben.');
        }

        $package->setLexwareArticleId($articleId);
        $this->entityManager->persist($package);
        $this->entityManager->flush();

        return [$articleId, 'created'];
    }

    private function extractQuery(Request $request): array
    {
        $raw = $request->request->all();
        if ([] === $raw) {
            $decoded = json_decode((string) $request->getContent(), true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        $query = [];

        if (isset($raw['page'])) {
            $page = filter_var((string) $raw['page'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if (false !== $page) {
                $query['page'] = $page;
            }
        }

        $size = filter_var((string) ($raw['size'] ?? '25'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 200]]);
        $query['size'] = false !== $size ? max(25, $size) : 25;

        foreach (['voucherType', 'voucherStatus'] as $key) {
            $val = trim((string) ($raw[$key] ?? ''));
            if ('' !== $val) {
                $query[$key] = $val;
            }
        }

        return $query;
    }

    private function lexError(LexwareApiException $e): JsonResponse
    {
        return $this->json([
            'success' => false,
            'message' => $e->getMessage(),
            'status' => $e->getStatusCode(),
            'url' => $e->getRequestUrl(),
            'details' => $e->getResponseBody(),
        ], 500);
    }

    /**
     * Merged CRM-Daten in einen bestehenden Lexoffice-Kontakt.
     * Vorhandene Lexoffice-Werte werden nicht überschrieben, nur fehlende ergänzt.
     * Ausnahme: note (Kundennummer) und contactPersons werden immer gesetzt/aktualisiert.
     */
    private function mergeContactPayload(array $existing, Kunde $kunde): array
    {
        $changes = [];
        $payload = $existing;

        // version wird für PUT benötigt
        $payload['version'] = (int) ($existing['version'] ?? 0);

        // Interne Felder die Lexoffice nicht im PUT akzeptiert entfernen
        foreach (['organizationId', 'createdDate', 'updatedDate', 'archived'] as $key) {
            unset($payload[$key]);
        }

        $kundennummer = trim((string) $kunde->getKundennummer());
        $firstName    = trim((string) $kunde->getVorname());
        $lastName     = trim((string) $kunde->getNachname());
        $company      = trim((string) $kunde->getFirmenname());
        $email        = trim((string) $kunde->getEmail());
        $phone        = trim((string) $kunde->getTelefon());
        $street       = trim(trim((string) $kunde->getStrasse()) . ' ' . trim((string) $kunde->getHausnummer()));
        $zip          = trim((string) $kunde->getPlz());
        $city         = trim((string) $kunde->getStadt());
        $country      = $this->resolveCountryCode($kunde->getLand());

        // note: Kundennummer immer setzen/aktualisieren
        if ('' !== $kundennummer) {
            $existingNote = trim((string) ($existing['note'] ?? ''));
            if ($existingNote !== $kundennummer) {
                $payload['note'] = $kundennummer;
                $changes[] = 'note';
            }
        }

        // tag: immer mitsenden — GET gibt Tags nicht zurück, Lexoffice ignoriert Duplikate
        $tag = $this->resolveLexTag($kunde);
        if (null !== $tag) {
            $payload['tags'] = [$tag];
        }

        // company/person: immer aktualisieren
        $existingCompany = isset($existing['company']['name']) ? trim((string) $existing['company']['name']) : '';
        $existingLastName = isset($existing['person']['lastName']) ? trim((string) $existing['person']['lastName']) : '';

        if ('' !== $company) {
            if ($existingCompany !== $company) {
                $payload['company'] = ['name' => $company];
                $changes[] = 'company';
            }
        } elseif ('' !== $lastName || '' !== $firstName) {
            $newLastName = '' !== $lastName ? $lastName : $firstName;
            if ($existingLastName !== $newLastName) {
                $payload['person'] = [
                    'firstName' => $firstName,
                    'lastName'  => $newLastName,
                ];
                $changes[] = 'person';
            }
        }

        // Ansprechpartner: bei Firmenkontakten unter company.contactPersons
        $isCompanyContact = isset($payload['company']);
        if ($isCompanyContact && '' !== $firstName && '' !== $lastName) {
            $existingPersons = $existing['company']['contactPersons'] ?? [];
            $alreadyExists   = false;
            foreach ($existingPersons as $cp) {
                $cpFirst = strtolower(trim((string) ($cp['firstName'] ?? '')));
                $cpLast  = strtolower(trim((string) ($cp['lastName'] ?? '')));
                if ($cpFirst === strtolower($firstName) && $cpLast === strtolower($lastName)) {
                    $alreadyExists = true;
                    break;
                }
            }

            if (!$alreadyExists) {
                $newPerson = [
                    'salutation' => 'Herr',
                    'firstName'  => $firstName,
                    'lastName'   => $lastName,
                ];
                if ('' !== $email) {
                    $newPerson['emailAddress'] = $email;
                }
                $payload['company']['contactPersons'] = array_merge($existingPersons, [$newPerson]);
                $changes[] = 'contactPersons';
            }
        }

        // E-Mail: ergänzen wenn komplett fehlt
        $existingEmails = $existing['emailAddresses'] ?? [];
        $hasEmail = false;
        foreach ($existingEmails as $list) {
            if (is_array($list) && [] !== $list) {
                $hasEmail = true;
                break;
            }
        }
        if (!$hasEmail && '' !== $email) {
            $payload['emailAddresses'] = ['business' => [$email]];
            $changes[] = 'emailAddresses';
        }

        // Telefon: ergänzen wenn komplett fehlt
        $existingPhones = $existing['phoneNumbers'] ?? [];
        $hasPhone = false;
        foreach ($existingPhones as $list) {
            if (is_array($list) && [] !== $list) {
                $hasPhone = true;
                break;
            }
        }
        if (!$hasPhone && '' !== $phone) {
            $payload['phoneNumbers'] = ['business' => [$phone]];
            $changes[] = 'phoneNumbers';
        }

        // Adresse: ergänzen wenn noch keine billing-Adresse vorhanden
        $existingBilling = $existing['addresses']['billing'] ?? [];
        if ([] === $existingBilling && '' !== $street && '' !== $zip && '' !== $city) {
            $payload['addresses'] = array_merge($existing['addresses'] ?? [], [
                'billing' => [[
                    'street'      => $street,
                    'zip'         => $zip,
                    'city'        => $city,
                    'countryCode' => $country,
                ]],
            ]);
            $changes[] = 'addresses';
        }

        $payload['_changes'] = $changes;

        return $payload;
    }

    private function buildSyncContactPayload(Kunde $kunde): array
    {
        $company  = trim((string) $kunde->getFirmenname());
        $firstName = trim((string) $kunde->getVorname());
        $lastName  = trim((string) $kunde->getNachname());
        $email     = trim((string) $kunde->getEmail());
        $phone     = trim((string) $kunde->getTelefon());
        $street    = trim(trim((string) $kunde->getStrasse()) . ' ' . trim((string) $kunde->getHausnummer()));
        $zip       = trim((string) $kunde->getPlz());
        $city      = trim((string) $kunde->getStadt());
        $country   = $this->resolveCountryCode($kunde->getLand());
        $kundennummer = trim((string) $kunde->getKundennummer());

        $payload = [
            'version' => 0,
            'roles' => ['customer' => new \stdClass()],
        ];

        if ('' !== $kundennummer) {
            $payload['note'] = $kundennummer;
        }

        $tag = $this->resolveLexTag($kunde);
        if (null !== $tag) {
            $payload['tags'] = [$tag];
        }

        if ('' !== $company) {
            $companyPayload = ['name' => $company];
            if ('' !== $firstName && '' !== $lastName) {
                $contactPerson = [
                    'salutation' => 'Herr',
                    'firstName'  => $firstName,
                    'lastName'   => $lastName,
                ];
                if ('' !== $email) {
                    $contactPerson['emailAddress'] = $email;
                }
                $companyPayload['contactPersons'] = [$contactPerson];
            }
            $payload['company'] = $companyPayload;
        } else {
            if ('' === $lastName && '' === $firstName) {
                throw new \RuntimeException('Kunde hat keinen Namen für Lexoffice Kontakt.');
            }
            $payload['person'] = [
                'firstName' => $firstName,
                'lastName'  => '' !== $lastName ? $lastName : $firstName,
            ];
        }

        if ('' !== $email) {
            $payload['emailAddresses'] = ['business' => [$email]];
        }

        if ('' !== $phone) {
            $payload['phoneNumbers'] = ['business' => [$phone]];
        }

        if ('' !== $street && '' !== $zip && '' !== $city) {
            $payload['addresses'] = [
                'billing' => [[
                    'street'      => $street,
                    'zip'         => $zip,
                    'city'        => $city,
                    'countryCode' => $country,
                ]],
            ];
        }

        return $payload;
    }

    private function resolveLexTag(Kunde $kunde): ?string
    {
        $firma = $kunde->getFirma();
        if (!$firma instanceof Firma) {
            return null;
        }

        $map = [
            'NO'  => 'no1',
            'CPT' => 'cpt',
            'TFD' => 'tfd',
        ];

        $kuerzel = strtoupper(trim((string) $firma->getKuerzel()));

        return $map[$kuerzel] ?? null;
    }

    private function resolveCountryCode(?string $country): string
    {
        $normalized = strtolower(trim((string) $country));
        if ('' === $normalized) {
            return 'DE';
        }
        if (2 === strlen($normalized)) {
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

    private function extractLexNumber(array $contact): ?string
    {
        $number = $contact['roles']['customer']['number'] ?? null;

        return null !== $number ? (string) $number : null;
    }

    private function extractLexName(array $contact): ?string
    {
        $company = $contact['company']['name'] ?? null;
        if (null !== $company) {
            return (string) $company;
        }

        $person = $contact['person'] ?? [];
        $first = trim((string) ($person['firstName'] ?? ''));
        $last = trim((string) ($person['lastName'] ?? ''));
        $full = trim($first . ' ' . $last);

        return '' !== $full ? $full : null;
    }

    private function extractLexEmail(array $contact): ?string
    {
        $emails = $contact['emailAddresses'] ?? [];
        foreach (['business', 'office', 'private', 'other'] as $type) {
            $list = $emails[$type] ?? [];
            if (is_array($list) && [] !== $list) {
                return (string) $list[0];
            }
        }

        return null;
    }
}
