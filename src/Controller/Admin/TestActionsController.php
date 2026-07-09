<?php

namespace App\Controller\Admin;

use App\Entity\Auftrag;
use App\Entity\CreditPackage;
use App\Entity\Firma;
use App\Entity\Kunde;
use App\Entity\KundeCreditTransaction;
use App\Repository\AuftragRepository;
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

#[Route('/admin/test-actions')]
final class TestActionsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AuftragRepository $auftragRepository,
        private readonly LexwareService $lexwareService,
    ) {
    }

    #[AdminRoute(path: '/test-actions', name: 'test_actions', options: ['methods' => ['GET']])]
    public function index(): Response
    {
        $statistics = $this->getStatistics();
        $kunden = $this->entityManager->getRepository(Kunde::class)->findBy([], ['nachname' => 'ASC', 'vorname' => 'ASC'], 200);
        $creditPackages = $this->entityManager->getRepository(CreditPackage::class)->findBy([], ['credits' => 'ASC']);
        $firmen = $this->entityManager->getRepository(Firma::class)->findBy([], ['name' => 'ASC']);

        return $this->render('admin/test_actions/index.html.twig', [
            'statistics' => $statistics,
            'kunden' => $kunden,
            'creditPackages' => $creditPackages,
            'firmen' => $firmen,
        ]);
    }

    private function getStatistics(): array
    {
        $today = new \DateTimeImmutable('00:00:00');
        $yesterday = $today->modify('-1 day');
        $weekAgo = $today->modify('-7 days');

        $auftragsTodayCount = $this->auftragRepository->count([]);
        $auftrags = $this->auftragRepository->findAll();

        $todayCount = 0;
        $yesterdayCount = 0;
        $weekCount = 0;

        foreach ($auftrags as $auftrag) {
            $createdAt = $auftrag->getCreatedAt();
            if (!$createdAt) {
                continue;
            }

            $createdDate = $createdAt->format('Y-m-d');
            $todayDate = $today->format('Y-m-d');
            $yesterdayDate = $yesterday->format('Y-m-d');
            $weekDate = $weekAgo->format('Y-m-d');

            if ($createdDate === $todayDate) {
                $todayCount++;
            }
            if ($createdDate === $yesterdayDate) {
                $yesterdayCount++;
            }
            if ($createdDate >= $weekDate && $createdDate <= $todayDate) {
                $weekCount++;
            }
        }

        // Status counts for today
        $statusCounts = [
            'received' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'rejected' => 0,
        ];

        foreach ($auftrags as $auftrag) {
            $createdAt = $auftrag->getCreatedAt();
            if (!$createdAt || $createdAt->format('Y-m-d') !== $today->format('Y-m-d')) {
                continue;
            }

            match ($auftrag->getStatus()) {
                Auftrag::STATUS_RECEIVED => $statusCounts['received']++,
                Auftrag::STATUS_IN_PROGRESS => $statusCounts['in_progress']++,
                Auftrag::STATUS_COMPLETED => $statusCounts['completed']++,
                Auftrag::STATUS_REJECTED => $statusCounts['rejected']++,
                default => null,
            };
        }

        return [
            'today' => $todayCount,
            'yesterday' => $yesterdayCount,
            'week' => $weekCount,
            'statusToday' => $statusCounts,
        ];
    }

    #[Route('/simulate-order', name: 'admin_test_simulate_order', methods: ['POST'])]
    public function simulateOrder(Request $request): Response
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = $request->request->all();
        }

        $firmaId = (int) ($payload['firmaId'] ?? $payload['firma_id'] ?? 0);
        $kundeRepository = $this->entityManager->getRepository(Kunde::class);
        $firma = null;

        if ($firmaId > 0) {
            $firma = $this->entityManager->getRepository(Firma::class)->find($firmaId);
            if (!$firma instanceof Firma) {
                return $this->json(['success' => false, 'message' => 'Firma nicht gefunden.'], Response::HTTP_NOT_FOUND);
            }
            $kunden = $kundeRepository->findBy(['firma' => $firma]);
        } else {
            $kunden = $kundeRepository->findAll();
        }

        if (empty($kunden)) {
            return $this->json(['success' => false, 'message' => 'Keine Kunden fuer diese Firma vorhanden.']);
        }

        $randomKunde = $kunden[array_rand($kunden)];

        $auftrag = new Auftrag();
        $auftrag->setKunde($randomKunde);
        $auftrag->setCustomerEmail($randomKunde->getEmail());
        $auftrag->setFileName('simulated_' . uniqid() . '.slave');
        $auftrag->setFileData(base64_encode(random_bytes(1024)));
        $auftrag->setFileSize(1024);
        $auftrag->setVehicleType('PKW');
        $auftrag->setManufacturer('Audi');
        $auftrag->setModel('RS6');
        $auftrag->setBuilt('2020');
        $auftrag->setEngine('4.0 TFSI');
        $auftrag->setVehicleDisplay('Audi RS6 2020 4.0 TFSI');
        $auftrag->setTuningTool('Autotuner');
        $auftrag->setTuningMethod('OBD');
        $auftrag->setSlaveOrMaster('Slave');
        $auftrag->setStageLabel('Stage 1');
        $auftrag->setStatus(Auftrag::STATUS_RECEIVED);

        $this->entityManager->persist($auftrag);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => sprintf('Auftrag simuliert für Kunde: %s', $randomKunde->getEmail()),
            'auftragId' => $auftrag->getId(),
            'tuningSetup' => 'Autotuner → OBD → Slave, Stage 1',
            'firma' => $firma ? (string) $firma : null,
        ]);
    }

    #[Route('/simulate-credit-topup', name: 'admin_test_simulate_credit_topup', methods: ['POST'])]
    public function simulateCreditTopup(Request $request): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = $request->request->all();
        }

        $normalizeDecimal = static function (mixed $value): ?float {
            if (null === $value) {
                return null;
            }

            $raw = trim((string) $value);
            if ('' === $raw) {
                return null;
            }

            $normalized = str_replace(',', '.', $raw);
            if (!is_numeric($normalized)) {
                return null;
            }

            return (float) $normalized;
        };

        $amountRaw = $payload['amount'] ?? $payload['credits'] ?? null;
        $amountProvided = null !== $amountRaw && '' !== (string) $amountRaw;
        $amount = $amountProvided && is_numeric($amountRaw) ? (int) $amountRaw : 0;
        $creditPackageId = (int) ($payload['creditPackageId'] ?? $payload['packageId'] ?? 0);
        $creditPackage = null;
        if ($creditPackageId > 0) {
            $creditPackage = $this->entityManager->getRepository(CreditPackage::class)->find($creditPackageId);
            if (!$creditPackage instanceof CreditPackage) {
                return $this->json(['success' => false, 'message' => 'Credit-Paket nicht gefunden.'], Response::HTTP_NOT_FOUND);
            }
            $amount = $creditPackage->getCredits();
        }

        if (!$amountProvided && $amount <= 0) {
            $amount = 100;
        }
        if ($amount <= 0) {
            return $this->json(['success' => false, 'message' => 'Bitte eine positive Credit-Anzahl angeben.'], Response::HTTP_BAD_REQUEST);
        }

        $paymentMethod = strtolower(trim((string) ($payload['paymentMethod'] ?? 'paypal')));
        if ('' === $paymentMethod) {
            $paymentMethod = 'paypal';
        }

        $createdAt = null;
        $createdAtRaw = $payload['createdAt'] ?? $payload['date'] ?? null;
        if (null !== $createdAtRaw && '' !== trim((string) $createdAtRaw)) {
            try {
                $createdAt = new \DateTimeImmutable((string) $createdAtRaw);
            } catch (\Throwable) {
                return $this->json([
                    'success' => false,
                    'message' => 'Buchungsdatum ist ungueltig. Bitte ein gueltiges Datum angeben.',
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $taxRateRaw = $payload['taxRate'] ?? $payload['vatRate'] ?? null;
        $taxRate = $normalizeDecimal($taxRateRaw);
        if (null === $taxRate || $taxRate < 0) {
            $taxRate = 19.0;
        }

        $resolvedPackage = $creditPackage;
        if (!$resolvedPackage instanceof CreditPackage && $amount > 0) {
            $resolvedPackage = $this->entityManager->getRepository(CreditPackage::class)->findOneBy(['credits' => $amount]);
        }

        if (!$resolvedPackage instanceof CreditPackage) {
            return $this->json([
                'success' => false,
                'message' => 'Bitte ein Credit-Paket auswählen (Preis wird aus dem Paketpreis genommen).',
            ], Response::HTTP_BAD_REQUEST);
        }

        $priceNet = $normalizeDecimal($resolvedPackage->getPrice());
        if (null === $priceNet || $priceNet <= 0) {
            return $this->json([
                'success' => false,
                'message' => 'Paketpreis fehlt oder ist 0. Bitte Preis im Credit-Paket hinterlegen.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $taxRateForStorage = null;
        $priceGross = null;
        if (null !== $priceNet) {
            $divider = 1 + ($taxRate / 100);
            $priceGross = $divider > 0 ? $priceNet * $divider : $priceNet;
            $taxRateForStorage = number_format($taxRate, 2, '.', '');
        }

        $paypalFee = null;
        if ('paypal' === $paymentMethod && null !== $priceGross) {
            $paypalFee = ($priceGross * 0.0179) + 0.35;
        }

        $kundeId = (int) ($payload['kundeId'] ?? $payload['kunde_id'] ?? 0);
        $kundeRepository = $this->entityManager->getRepository(Kunde::class);

        if ($kundeId > 0) {
            $kunde = $kundeRepository->find($kundeId);
        } else {
            $kunden = $kundeRepository->findAll();
            $kunde = !empty($kunden) ? $kunden[array_rand($kunden)] : null;
        }

        if (!$kunde instanceof Kunde) {
            return $this->json(['success' => false, 'message' => 'Kein Kunde gefunden.'], Response::HTTP_NOT_FOUND);
        }

        $balanceBefore = $kunde->getCredits();
        $balanceAfter = $balanceBefore + $amount;
        $paypalRef = 'PAYPAL-TEST-' . strtoupper(bin2hex(random_bytes(4)));
        $descriptionParts = ['PayPal Zahlung (Test)'];
        if ($resolvedPackage instanceof CreditPackage) {
            $packageTitle = $resolvedPackage->getTitle() ?? ('Paket #' . $resolvedPackage->getId());
            $descriptionParts[] = sprintf('Paket: %s (%d Credits)', $packageTitle, $resolvedPackage->getCredits());
        }
        $descriptionParts[] = sprintf('Ref: %s', $paypalRef);

        $transaction = (new KundeCreditTransaction())
            ->setKunde($kunde)
            ->setAmount($amount)
            ->setTransactionType(KundeCreditTransaction::TYPE_CUSTOMER_TOPUP)
            ->setDescription(implode(' | ', $descriptionParts))
            ->setPaymentMethod($paymentMethod)
            ->setPriceNet(null !== $priceNet ? number_format($priceNet, 2, '.', '') : null)
            ->setPriceGross(null !== $priceGross ? number_format($priceGross, 2, '.', '') : null)
            ->setTaxRate($taxRateForStorage)
            ->setPaymentFee(null !== $paypalFee ? number_format($paypalFee, 2, '.', '') : null)
            ->setBalanceBefore($balanceBefore)
            ->setBalanceAfter($balanceAfter);
        if ($createdAt instanceof \DateTimeImmutable) {
            $transaction->setCreatedAt($createdAt);
        }

        $kunde->setCredits($balanceAfter);

        $this->entityManager->persist($transaction);
        $this->entityManager->persist($kunde);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => sprintf('%d Credits wurden via PayPal fuer %s aufgeladen.', $amount, (string) $kunde),
            'kundeId' => $kunde->getId(),
            'customerEmail' => $kunde->getEmail(),
            'amount' => $amount,
            'balanceBefore' => $balanceBefore,
            'balanceAfter' => $balanceAfter,
            'transactionId' => $transaction->getId(),
            'paypalRef' => $paypalRef,
            'creditPackageId' => $resolvedPackage?->getId(),
            'priceGross' => $transaction->getPriceGross(),
            'priceNet' => $transaction->getPriceNet(),
            'taxRate' => $transaction->getTaxRate(),
            'paymentFee' => $transaction->getPaymentFee(),
        ]);
    }

    #[Route('/create-kunde', name: 'admin_test_create_kunde', methods: ['POST'])]
    public function createKunde(Request $request): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = $request->request->all();
        }

        $firmaId = (int) ($payload['firmaId'] ?? $payload['firma_id'] ?? 0);
        if ($firmaId <= 0) {
            return $this->json(['success' => false, 'message' => 'Bitte eine Firma auswaehlen.'], Response::HTTP_BAD_REQUEST);
        }

        $firma = $this->entityManager->getRepository(Firma::class)->find($firmaId);
        if (!$firma instanceof Firma) {
            return $this->json(['success' => false, 'message' => 'Firma nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        $vorname = trim((string) ($payload['vorname'] ?? ''));
        $nachname = trim((string) ($payload['nachname'] ?? ''));
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $kundennummer = trim((string) ($payload['kundennummer'] ?? ''));
        $firmenname = trim((string) ($payload['firmenname'] ?? ''));
        $telefon = trim((string) ($payload['telefon'] ?? ''));

        if ('' === $vorname) {
            $vorname = 'Test';
        }
        if ('' === $nachname) {
            $nachname = 'Kunde';
        }
        if ('' === $email) {
            $email = sprintf('test-kunde-%s@example.com', bin2hex(random_bytes(3)));
        }

        $kundeRepository = $this->entityManager->getRepository(Kunde::class);
        if ($kundeRepository->findOneBy(['email' => $email])) {
            return $this->json(['success' => false, 'message' => 'E-Mail existiert bereits. Bitte eine andere verwenden.'], Response::HTTP_CONFLICT);
        }

        if ('' !== $kundennummer) {
            if ($kundeRepository->findOneBy(['kundennummer' => $kundennummer])) {
                return $this->json(['success' => false, 'message' => 'Kundennummer existiert bereits.'], Response::HTTP_CONFLICT);
            }
        } else {
            $kundennummer = null;
        }

        $kunde = (new Kunde())
            ->setFirma($firma)
            ->setVorname($vorname)
            ->setNachname($nachname)
            ->setEmail($email);

        if (null !== $kundennummer) {
            $kunde->setKundennummer($kundennummer);
        }

        if ('' !== $firmenname) {
            $kunde->setFirmenname($firmenname);
        }

        if ('' !== $telefon) {
            $kunde->setTelefon($telefon);
        }

        $this->entityManager->persist($kunde);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => sprintf('Kunde %s wurde angelegt.', (string) $kunde),
            'kundeId' => $kunde->getId(),
            'email' => $kunde->getEmail(),
            'firma' => (string) $firma,
            'kundennummer' => $kunde->getKundennummer(),
        ]);
    }

    #[Route('/lexware/profile', name: 'admin_test_lexware_profile', methods: ['POST'])]
    public function testLexwareProfile(): JsonResponse
    {
        try {
            $data = $this->lexwareService->getProfile();

            return $this->json(['success' => true, 'data' => $data]);
        } catch (LexwareApiException $exception) {
            return $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => $exception->getStatusCode(),
                'url' => $exception->getRequestUrl(),
                'details' => $exception->getResponseBody(),
            ], 500);
        }
    }

    #[Route('/lexware/contacts', name: 'admin_test_lexware_contacts', methods: ['POST'])]
    public function testLexwareContacts(Request $request): JsonResponse
    {
        return $this->lexwareListResponse('contacts', $request);
    }

    #[Route('/lexware/products', name: 'admin_test_lexware_products', methods: ['POST'])]
    public function testLexwareProducts(Request $request): JsonResponse
    {
        return $this->lexwareListResponse('products', $request);
    }

    #[Route('/lexware/vouchers', name: 'admin_test_lexware_vouchers', methods: ['POST'])]
    public function testLexwareVouchers(Request $request): JsonResponse
    {
        return $this->lexwareListResponse('vouchers', $request);
    }

    #[Route('/lexware/invoices', name: 'admin_test_lexware_invoices', methods: ['POST'])]
    public function testLexwareInvoices(Request $request): JsonResponse
    {
        return $this->lexwareListResponse('invoices', $request);
    }

    #[Route('/lexware/invoices/{id}/file', name: 'admin_test_lexware_invoice_file', methods: ['GET'])]
    public function downloadLexwareInvoiceFile(string $id): Response
    {
        $invoiceId = trim($id);
        if ('' === $invoiceId) {
            return new Response('Missing invoice id.', Response::HTTP_BAD_REQUEST);
        }

        try {
            $file = $this->lexwareService->invoices()->downloadFile($invoiceId, 'application/pdf');
        } catch (LexwareApiException $exception) {
            return new Response(
                sprintf('Lexoffice error (%d): %s', $exception->getStatusCode(), $exception->getMessage()),
                Response::HTTP_BAD_GATEWAY
            );
        }

        $content = $file['content'] ?? '';
        if ('' === $content) {
            return new Response('Empty file response.', Response::HTTP_NO_CONTENT);
        }

        $contentType = $file['contentType'] ?? 'application/pdf';
        $filename = $file['filename'] ?? sprintf('lexoffice-invoice-%s.pdf', $invoiceId);

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => $contentType,
            'Content-Disposition' => (new ResponseHeaderBag())->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            ),
        ]);
    }

    private function lexwareListResponse(string $resource, Request $request): JsonResponse
    {
        $query = $this->extractLexwareQuery($request);
        if ('vouchers' === $resource && !isset($query['voucherType'])) {
            $query['voucherType'] = 'any';
        }
        if ('vouchers' === $resource && !isset($query['voucherStatus'])) {
            $query['voucherStatus'] = 'any';
        }
        if ('invoices' === $resource) {
            $query['voucherType'] = 'salesinvoice';
            if (!isset($query['voucherStatus'])) {
                $query['voucherStatus'] = 'any';
            }
        }
        if (!in_array($resource, ['vouchers', 'invoices'], true)) {
            unset($query['voucherType'], $query['voucherStatus']);
        }

        try {
            $data = match ($resource) {
                'contacts' => $this->lexwareService->contacts()->list($query),
                'products' => $this->lexwareService->products()->list($query),
                'vouchers' => $this->lexwareService->vouchers()->listVoucherList($query),
                'invoices' => $this->lexwareService->vouchers()->listVoucherList($query),
                default => [],
            };

            return $this->json(['success' => true, 'data' => $data]);
        } catch (LexwareApiException $exception) {
            return $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => $exception->getStatusCode(),
                'details' => $exception->getResponseBody(),
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function extractLexwareQuery(Request $request): array
    {
        $payload = [];
        $rawPage = $request->request->get('page');
        $rawSize = $request->request->get('size');
        $rawVoucherType = $request->request->get('voucherType');
        $rawVoucherStatus = $request->request->get('voucherStatus');

        if (null === $rawPage && null === $rawSize) {
            $content = trim((string) $request->getContent());
            if ('' !== $content) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    $rawPage = $decoded['page'] ?? null;
                    $rawSize = $decoded['size'] ?? null;
                    $rawVoucherType = $decoded['voucherType'] ?? null;
                    $rawVoucherStatus = $decoded['voucherStatus'] ?? null;
                }
            }
        }

        if (is_scalar($rawPage)) {
            $page = filter_var((string) $rawPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if (false !== $page) {
                $payload['page'] = $page;
            }
        }

        if (is_scalar($rawSize)) {
            $size = filter_var((string) $rawSize, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 200]]);
            if (false !== $size) {
                $payload['size'] = max(25, (int) $size);
            }
        }

        if (!isset($payload['size'])) {
            $payload['size'] = 25;
        }

        if (is_scalar($rawVoucherType)) {
            $voucherType = trim((string) $rawVoucherType);
            if ('' !== $voucherType) {
                $payload['voucherType'] = $voucherType;
            }
        }

        if (is_scalar($rawVoucherStatus)) {
            $voucherStatus = trim((string) $rawVoucherStatus);
            if ('' !== $voucherStatus) {
                $payload['voucherStatus'] = $voucherStatus;
            }
        }

        return $payload;
    }
}
