<?php

declare(strict_types=1);

namespace App\Portals\No1tuningfiles\Controller;

use App\Brand\Common\Context\BrandContextResolver;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Auftrag;
use App\Entity\Kunde;
use App\Entity\KundeCreditTransaction;
use App\Entity\Portal;
use App\Entity\PortalPriceList;
use App\Entity\PortalPriceListItem;
use App\Entity\WinolsConfiguration;
use App\Portals\No1tuningfiles\Form\OrderDummyUploadType;
use App\Portals\No1tuningfiles\Security\No1tuningfilesPortalCustomerUser;
use App\Portals\No1tuningfiles\Service\PaypalOrderService;
use App\Portals\No1tuningfiles\Service\PortalCreditInvoiceService;
use App\Portals\No1tuningfiles\Service\PortalCreditTopupService;
use App\Portals\No1tuningfiles\Service\PortalDashboardBuilder;
use App\Repository\AuftragRepository;
use App\Repository\CreditPackageRepository;
use App\Repository\KundeCreditTransactionRepository;
use App\Repository\PortalPriceListRepository;
use App\Repository\WinolsConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    public function __construct(
        private readonly AuftragRepository $auftragRepository,
        private readonly PortalPriceListRepository $portalPriceListRepository,
        private readonly CreditPackageRepository $creditPackageRepository,
        private readonly BrandContextResolver $brandContextResolver,
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly PaypalOrderService $paypalOrderService,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly PortalCreditTopupService $portalCreditTopupService,
        private readonly PortalCreditInvoiceService $portalCreditInvoiceService,
        private readonly KundeCreditTransactionRepository $transactionRepository,
        private readonly PortalDashboardBuilder $portalDashboardBuilder,
        private readonly WinolsConfigurationRepository $winolsConfigurationRepository,
    ) {
    }

    #[Route('/auftrag', name: 'no1_portal_order', methods: ['GET', 'POST'])]
    public function index(Request $request): Response {
        $portalConfig = $this->portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);
        $customer = $this->getCurrentCustomer();
        $steps = $this->buildSteps();
        $currentStepNumber = max(1, min((int) $request->query->get('step', 1), count($steps)));
        $currentStepIndex = $currentStepNumber - 1;

        foreach ($steps as $index => &$step) {
            $step['state'] = $index < $currentStepIndex ? 'completed' : ($index === $currentStepIndex ? 'current' : 'upcoming');
            $step['url'] = $this->generateUrl('no1_portal_order', ['step' => $index + 1]);
        }
        unset($step);

        $currentStep = $steps[$currentStepIndex];
        $previousStepUrl = $currentStepIndex > 0 ? $steps[$currentStepIndex - 1]['url'] : null;
        $nextStepUrl = $currentStepIndex < count($steps) - 1 ? $steps[$currentStepIndex + 1]['url'] : null;
        $progressPercent = (int) round((($currentStepIndex + 1) / count($steps)) * 100);
        $uploadForm = null;

        if ($currentStepNumber === 1) {
            $uploadForm = $this->createForm(OrderDummyUploadType::class, null, [
                'action' => $this->generateUrl('no1_portal_order', ['step' => 1]),
            ]);
            $uploadForm->handleRequest($request);
        }

        return $this->render('portal/no1tuningfiles/order/index.html.twig', [
            'brandContext' => $this->brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
            'reactBootstrap' => [
                'session' => [
                    'customerId' => $customer->getId(),
                    'credits' => (int) $customer->getCredits(),
                ],
                'portalDesign' => $portalConfig->getResolvedDesign(),
                'profile' => [
                    'tuningTools' => $customer->getTuningTools(),
                ],
                'priceList' => $this->buildPriceListBootstrap(),
                'orderFlowCreditTopup' => [
                    'configured' => $this->paypalOrderService->isConfigured(),
                    'clientId' => $this->paypalOrderService->getClientId(),
                    'csrfToken' => $this->csrfTokenManager->getToken('no1_portal_order_credit_paypal')->getValue(),
                ],
                'parkedOrders' => $this->portalDashboardBuilder->buildParkedOrders($customer),
            ],
            'wizardSteps' => $steps,
            'currentStep' => $currentStep,
            'currentStepNumber' => $currentStepNumber,
            'progressPercent' => $progressPercent,
            'previousStepUrl' => $previousStepUrl,
            'nextStepUrl' => $nextStepUrl,
            'orderUploadForm' => $uploadForm?->createView(),
        ]);
    }

    #[Route('/auftrag/park', name: 'no1_portal_order_park', methods: ['POST'])]
    public function park(Request $request): JsonResponse {
        try {
            $portalConfig = $this->portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);
            $winolsConfiguration = $portalConfig->isWinolsEnabled() ? $this->resolveWinolsConfiguration($portalConfig) : null;
            $user = $this->getUser();
            if (!$user instanceof No1tuningfilesPortalCustomerUser) {
                return $this->json(['error' => 'Nicht eingeloggt.'], Response::HTTP_UNAUTHORIZED);
            }

            $payload = $request->toArray();
            $orderInput = $this->parseOrderPayload($payload);
            $quote = $this->calculateOrderCreditQuote($user->getCustomer(), $orderInput);
            $existingParkedOrder = $this->resolveParkedOrderForCustomer($user->getCustomer(), (int) ($orderInput['parkedOrderId'] ?? 0));
            $parkedOrder = $this->hydrateOrderEntity($existingParkedOrder ?? new Auftrag(), $user->getCustomer(), $orderInput, $quote, $winolsConfiguration, [
                'context' => 'parked',
                'state' => 'parked',
                'updatedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ], true, Auftrag::STATUS_PARKED);
            $this->entityManager->persist($parkedOrder);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Auftrag wurde geparkt.',
                'parkedOrderId' => $parkedOrder->getId(),
                'orderNumber' => $parkedOrder->getOrderNumber(),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $exception) {
            $this->logger->error('Order park failed.', ['exception' => $exception]);
            return $this->json(['error' => 'Auftrag konnte nicht geparkt werden.'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/auftrag/parked', name: 'no1_portal_order_parked_list', methods: ['GET'])]
    public function listParkedOrders(Request $request): JsonResponse
    {
        $this->portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);
        $user = $this->getUser();
        if (!$user instanceof No1tuningfilesPortalCustomerUser) {
            return $this->json(['items' => [], 'message' => 'Nicht eingeloggt.'], Response::HTTP_UNAUTHORIZED);
        }

        $parkedItems = $this->portalDashboardBuilder->buildParkedOrders($user->getCustomer(), 10);

        return $this->json(['items' => $parkedItems]);
    }

    #[Route('/auftrag/parked/{id}', name: 'no1_portal_order_parked_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getParkedOrderDetail(int $id, Request $request): JsonResponse
    {
        $this->portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);
        $user = $this->getUser();
        if (!$user instanceof No1tuningfilesPortalCustomerUser) {
            return $this->json(['error' => 'Nicht eingeloggt.'], Response::HTTP_UNAUTHORIZED);
        }

        $order = $this->resolveParkedOrderForCustomer($user->getCustomer(), $id);
        if (!$order instanceof Auftrag) {
            return $this->json(['error' => 'Geparkter Auftrag nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->buildParkedOrderDetail($order));
    }

    #[Route('/auftrag/submit', name: 'no1_portal_order_submit', methods: ['POST'])]
    public function submit(Request $request): JsonResponse {
        try {
            $portalConfig = $this->portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);
            $winolsConfiguration = $portalConfig->isWinolsEnabled() ? $this->resolveWinolsConfiguration($portalConfig) : null;

            $user = $this->getUser();
            if (!$user instanceof No1tuningfilesPortalCustomerUser) {
                return $this->json(['error' => 'Nicht eingeloggt.'], Response::HTTP_UNAUTHORIZED);
            }

            /** @var array<string, mixed> $payload */
            $payload = $request->toArray();
            $orderInput = $this->parseOrderPayload($payload);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\JsonException) {
            return $this->json(['error' => 'Ungueltiges JSON-Payload.'], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $exception) {
            $logger->error('Order submit payload parsing failed.', [
                'exception' => $exception,
                'route' => 'no1_portal_order_submit',
            ]);

            return $this->json(['error' => 'Auftrag konnte nicht verarbeitet werden.'], Response::HTTP_BAD_REQUEST);
        }

        $customer = $user->getCustomer();
        $quote = $this->calculateOrderCreditQuote($customer, $orderInput);
        if ($quote['missingCredits'] > 0) {
            return $this->json([
                'error' => sprintf(
                    'Nicht genug Credits. Verfuegbar: %d, benoetigt: %d.',
                    $quote['currentCredits'],
                    $quote['requiredCredits']
                ),
                'credits' => $quote['currentCredits'],
                'requiredCredits' => $quote['requiredCredits'],
                'missingCredits' => $quote['missingCredits'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $existingParkedOrder = $this->resolveParkedOrderForCustomer($customer, (int) ($orderInput['parkedOrderId'] ?? 0));
        $order = $this->hydrateOrderEntity($existingParkedOrder ?? new Auftrag(), $customer, $orderInput, $quote, $winolsConfiguration, null, false, null);
        $balanceBefore = (int) $customer->getCredits();

        try {
            $this->entityManager->beginTransaction();

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $balanceAfter = $this->finalizeOrderCredits($order, $customer, $quote, $this->entityManager);

            if ($orderInput['autoReady']) {
                $this->ensureAutoReadyFinishedFile($order);
                $this->entityManager->flush();
            }

            $this->entityManager->commit();
        } catch (\Throwable $exception) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }

            $this->logger->error('Order submit persist failed.', [
                'exception' => $exception,
                'customer_id' => $customer->getId(),
                'file_name' => $fileName,
                'file_size' => $fileSize,
            ]);

            return $this->json(['error' => 'Auftrag konnte serverseitig nicht gespeichert werden.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'id' => $order->getId(),
            'orderNumber' => $order->getOrderNumber(),
            'status' => $order->getStatus(),
            'credits' => (int) $customer->getCredits(),
            'deductedCredits' => $quote['requiredCredits'],
            'autoModEnabled' => $order->isAutoModEnabled(),
            'autoModStatus' => $order->getAutoModStatus(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/auftrag/quote', name: 'no1_portal_order_quote', methods: ['POST'])]
    public function quote(Request $request): JsonResponse {
        try {
            $this->portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);
            $customer = $this->getCurrentCustomer();
            /** @var array<string, mixed> $payload */
            $payload = $request->toArray();
            $quote = $this->calculateOrderCreditQuote($customer, $this->parseOrderPayload($payload));

            return $this->json($quote);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $exception) {
            $this->logger->error('Order quote failed.', ['exception' => $exception]);

            return $this->json(['error' => 'Credits konnten nicht berechnet werden.'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/auftrag/paypal/order', name: 'no1_portal_order_paypal_order', methods: ['POST'])]
    public function createOrderFlowPaypalOrder(Request $request): JsonResponse {
        try {
            $portalConfig = $this->portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);
            $winolsConfiguration = $portalConfig->isWinolsEnabled() ? $this->resolveWinolsConfiguration($portalConfig) : null;
            $customer = $this->getCurrentCustomer();
            /** @var array<string, mixed> $payload */
            $payload = $request->toArray();
            if (!$this->isCsrfTokenValid('no1_portal_order_credit_paypal', (string) ($payload['_token'] ?? ''))) {
                return $this->json(['message' => 'Ungueltige Anfrage.'], Response::HTTP_BAD_REQUEST);
            }

            $orderInput = $this->parseOrderPayload($payload);
            $quote = $this->calculateOrderCreditQuote($customer, $orderInput);
            if ($quote['missingCredits'] <= 0) {
                return $this->json(['message' => 'Fuer diesen Auftrag werden keine zusaetzlichen Credits benoetigt.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $pendingOrder = $this->upsertPendingCreditTopupOrder($customer, $orderInput, $quote, $this->entityManager, $winolsConfiguration);
            $paypalOrderId = $this->paypalOrderService->createCustomCreditsOrder($quote['missingCredits'], $customer, $quote['topupPricing'], [
                'context' => 'order_flow_credit_topup',
                'pendingOrderId' => $pendingOrder->getId(),
                'requiredCredits' => $quote['requiredCredits'],
            ]);

            $vehicleData = $pendingOrder->getVehicleData();
            $vehicleData['orderFlow']['paypalOrderId'] = $paypalOrderId;
            $pendingOrder->setVehicleData($vehicleData);
            $this->entityManager->persist($pendingOrder);
            $this->entityManager->flush();

            return $this->json([
                'id' => $paypalOrderId,
                'pendingOrderId' => $pendingOrder->getId(),
                'requiredCredits' => $quote['requiredCredits'],
                'missingCredits' => $quote['missingCredits'],
                'topupCredits' => $quote['missingCredits'],
                'topupPricing' => $quote['topupPricing'],
            ]);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $exception) {
            $this->logger->error('Order flow PayPal order failed.', ['exception' => $exception]);

            return $this->json(['message' => 'PayPal Checkout konnte nicht gestartet werden.'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/auftrag/paypal/capture', name: 'no1_portal_order_paypal_capture', methods: ['POST'])]
    public function captureOrderFlowPaypalOrder(Request $request): JsonResponse {
        try {
            $this->portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);
            $customer = $this->getCurrentCustomer();
            /** @var array<string, mixed> $payload */
            $payload = $request->toArray();
            if (!$this->isCsrfTokenValid('no1_portal_order_credit_paypal', (string) ($payload['_token'] ?? ''))) {
                return $this->json(['message' => 'Ungueltige Anfrage.'], Response::HTTP_BAD_REQUEST);
            }

            $pendingOrderId = $this->readPositiveInt($payload, 'pendingOrderId');
            $paypalOrderId = $this->readString($payload, 'orderId');
            $pendingOrder = $this->findPendingCreditTopupOrder($customer, $pendingOrderId);
            if (!$pendingOrder instanceof Auftrag) {
                return $this->json(['message' => 'Offener Auftrag nicht gefunden.'], Response::HTTP_NOT_FOUND);
            }

            $vehicleData = $pendingOrder->getVehicleData();
            $storedPaypalOrderId = trim((string) ($vehicleData['orderFlow']['paypalOrderId'] ?? ''));
            if ('' !== $storedPaypalOrderId && $storedPaypalOrderId !== $paypalOrderId) {
                return $this->json(['message' => 'PayPal-Zahlung passt nicht zum offenen Auftrag.'], Response::HTTP_BAD_REQUEST);
            }

            $quote = $this->calculateOrderCreditQuote($customer, $this->extractOrderInputFromVehicleData($pendingOrder));
            $captureData = $this->paypalOrderService->captureOrder($paypalOrderId);
            if (!$this->transactionRepository->hasTransactionWithPaypalOrderId($paypalOrderId)) {
                $transaction = $this->portalCreditTopupService->bookCapturedPaypalCredits(
                    $customer,
                    $captureData,
                    $quote['missingCredits'],
                    $quote['topupPricing'],
                    sprintf('Credit-Kauf fuer Auftrag %s', $pendingOrder->getOrderNumber())
                );

                try {
                    $invoice = $this->portalCreditInvoiceService->createInvoiceForExactCreditTopup(
                        $customer,
                        $quote['missingCredits'],
                        $quote['topupPricing']['priceNet'],
                        $quote['topupPricing']['priceGross'],
                        $quote['topupPricing']['taxRate'],
                    );
                    $transaction->setDescription(trim($transaction->getDescription() . PHP_EOL . 'Lexoffice Invoice: ' . $invoice->getLexwareInvoiceId()));
                    $this->entityManager->flush();
                } catch (\Throwable $exception) {
                    $this->logger->error('Order flow invoice creation failed.', ['exception' => $exception, 'orderId' => $pendingOrder->getId()]);
                }
            }

            $refreshedQuote = $this->calculateOrderCreditQuote($customer, $this->extractOrderInputFromVehicleData($pendingOrder), false);
            if ((int) $customer->getCredits() < $refreshedQuote['requiredCredits']) {
                return $this->json(['message' => 'Nach der Zahlung stehen noch nicht genug Credits zur Verfuegung.'], Response::HTTP_CONFLICT);
            }

            $portalConfig = $this->portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);
            $winolsConfiguration = $portalConfig->isWinolsEnabled() ? $this->resolveWinolsConfiguration($portalConfig) : null;
            $this->finalizePendingCreditTopupOrder($pendingOrder, $customer, $refreshedQuote, $this->entityManager, $winolsConfiguration);

            return $this->json([
                'message' => 'Credits gekauft und Auftrag erfolgreich platziert.',
                'orderId' => $pendingOrder->getId(),
                'orderNumber' => $pendingOrder->getOrderNumber(),
                'status' => $pendingOrder->getStatus(),
                'credits' => (int) $customer->getCredits(),
                'deductedCredits' => $refreshedQuote['requiredCredits'],
            ]);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $exception) {
            $this->logger->error('Order flow PayPal capture failed.', ['exception' => $exception]);

            return $this->json(['message' => 'PayPal-Zahlung konnte nicht verbucht werden.'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/auftrag/{orderNumber}/auto-ready', name: 'no1_portal_order_auto_ready', methods: ['POST'])]
    public function markAutoReady(string $orderNumber, Request $request): JsonResponse {
        $this->portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);

        $user = $this->getUser();
        if (!$user instanceof No1tuningfilesPortalCustomerUser) {
            return $this->json(['error' => 'Nicht eingeloggt.'], Response::HTTP_UNAUTHORIZED);
        }

        $order = $this->resolveOrderForCustomer($user, $orderNumber);
        if (!$order instanceof Auftrag) {
            return $this->json(['error' => 'Auftrag nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        if ($order->getStatus() !== Auftrag::STATUS_COMPLETED) {
            $order->setStatus(Auftrag::STATUS_COMPLETED);
        }

        if (null === $order->getFinishedFileName() || '' === trim((string) $order->getFinishedFileName())) {
            $order->setFinishedFileName($this->buildFinishedFileName((string) $order->getFileName()));
        }

        $this->ensureAutoReadyFinishedFile($order);

        if (null === $order->getFinishedFileUploadedAt()) {
            $order->setFinishedFileUploadedAt(new \DateTimeImmutable());
        }

        if (null === $order->getFinishedFileSize() && null !== $order->getFileSize()) {
            $order->setFinishedFileSize($order->getFileSize());
        }

        $this->entityManager->flush();

        return $this->json([
            'orderNumber' => $order->getOrderNumber(),
            'status' => $order->getStatus(),
            'finishedFileName' => $order->getFinishedFileName(),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function parseOrderPayload(array $payload): array
    {
        $fileName = $this->readString($payload, 'fileName');
        $fileSize = $this->readPositiveInt($payload, 'fileSize');
        $fileData = $this->readString($payload, 'fileData', false);
        $manufacturer = $this->readString($payload, 'manufacturer', false);
        $series = $this->readString($payload, 'series', false);
        $model = $this->readString($payload, 'model', false);
        $engine = $this->readString($payload, 'engine', false);
        $built = $this->readString($payload, 'built', false);
        $ecu = $this->readString($payload, 'ecu', false);
        $vehicleType = $this->readString($payload, 'vehicleType', false) ?: 'PKW';
        $vehicleDisplay = trim(implode(' ', array_filter([$manufacturer, $series, $model, $engine], static fn (string $v): bool => '' !== trim($v))));
        $tuningTool = $this->readString($payload, 'readingDevice', false);
        $method = $this->readString($payload, 'readingMethod', false);
        $readingType = $this->readString($payload, 'readingType', false);
        $masterSlave = $this->readString($payload, 'masterSlave', false);
        $priority = $this->readString($payload, 'priority', false);
        $comment = $this->readString($payload, 'comment', false);
        $stageLabel = $this->readString($payload, 'stageLabel', false);
        $selectedStage = $this->readString($payload, 'selectedStage', false);
        $selectedStageKey = $this->readString($payload, 'selectedStageKey', false);
        if ('' === $selectedStageKey) {
            $selectedStageKey = strtolower(trim($selectedStage));
        }
        $selectedGearboxStage = $this->readString($payload, 'selectedGearboxStage', false);
        $autoReady = $this->readBoolean($payload, 'autoReady', false);
        $simulateZeroCredits = $this->readBoolean($payload, 'simulateZeroCredits', false);

        $selectedOptions = $payload['selectedOptions'] ?? [];
        if (!is_array($selectedOptions)) {
            $selectedOptions = [];
        }

        $selectedOptionCodes = $payload['selectedOptionCodes'] ?? [];
        if (!is_array($selectedOptionCodes)) {
            $selectedOptionCodes = [];
        }

        $stagePerformance = $payload['stagePerformance'] ?? null;
        if (!is_array($stagePerformance)) {
            $stagePerformance = null;
        }

        return [
            'fileName' => $fileName,
            'fileSize' => $fileSize,
            'fileData' => $fileData,
            'manufacturer' => $manufacturer,
            'series' => $series,
            'model' => $model,
            'engine' => $engine,
            'built' => $built,
            'ecu' => $ecu,
            'vehicleType' => $vehicleType,
            'vehicleDisplay' => $vehicleDisplay,
            'readingDevice' => $tuningTool,
            'readingMethod' => $method,
            'readingType' => $readingType,
            'masterSlave' => $masterSlave,
            'priority' => $priority,
            'comment' => $comment,
            'stageLabel' => $stageLabel,
            'selectedStage' => $selectedStage,
            'selectedStageKey' => $selectedStageKey,
            'selectedGearboxStage' => $selectedGearboxStage,
            'selectedOptions' => array_values(array_filter(array_map(static fn (mixed $item): string => is_string($item) ? trim($item) : '', $selectedOptions), static fn (string $item): bool => '' !== $item)),
            'selectedOptionCodes' => array_values(array_filter(array_map(static fn (mixed $item): string => is_string($item) ? trim($item) : '', $selectedOptionCodes), static fn (string $item): bool => '' !== $item)),
        'stagePerformance' => $this->normalizeStagePerformance($stagePerformance),
        'autoReady' => $autoReady,
        'simulateZeroCredits' => $simulateZeroCredits,
        'pendingOrderId' => $this->readNonNegativeInt($payload, 'pendingOrderId', 0),
        'parkedOrderId' => $this->readNonNegativeInt($payload, 'parkedOrderId', 0),
    ];
}

    /**
     * @param array<string, mixed> $orderInput
     * @return array<string, mixed>
     */
    private function calculateOrderCreditQuote(Kunde $customer, array $orderInput, ?bool $simulateZeroCredits = null): array
    {
        $section = $this->resolvePriceListSection((string) ($orderInput['vehicleType'] ?? 'PKW'));
        $selectedStage = strtolower(trim((string) ($orderInput['selectedStage'] ?? '')));
        $selectedStageKey = strtolower(trim((string) ($orderInput['selectedStageKey'] ?? '')));
        $selectedGearboxStage = strtolower(trim((string) ($orderInput['selectedGearboxStage'] ?? '')));
        $selectedOptionCodes = array_values(array_unique(array_map(static fn (string $value): string => strtolower(trim($value)), $orderInput['selectedOptionCodes'] ?? [])));
        $priceList = $this->portalPriceListRepository->findOneBy(['active' => true], ['id' => 'ASC']);

        $stageCredits = 0;
        $optionCredits = 0;

        if ($priceList instanceof PortalPriceList) {
            if ('gearbox' === $selectedStage) {
                $gearboxItem = $this->findPriceListItem($priceList, $section, PortalPriceListItem::CATEGORY_GEARBOX, $selectedGearboxStage);
                $stageCredits = $gearboxItem?->getCredits() ?? 0;
            } elseif ('' !== $selectedStageKey && 'optionsonly' !== $selectedStageKey) {
                $stageItem = $this->findPriceListItem($priceList, $section, PortalPriceListItem::CATEGORY_STAGE, $selectedStageKey);
                $stageCredits = $stageItem?->getCredits() ?? 0;
            }

            foreach ($selectedOptionCodes as $code) {
                if ('' === $code) {
                    continue;
                }

                $category = 'optionsonly' === $selectedStageKey
                    ? PortalPriceListItem::CATEGORY_OPTIONS_ONLY
                    : PortalPriceListItem::CATEGORY_OPTION;
                $optionItem = $this->findPriceListOptionItem($priceList, $section, $category, $code, $selectedStageKey);
                $optionCredits += $optionItem?->getCredits() ?? 0;
            }
        }

        $requiredCredits = max(0, $stageCredits + $optionCredits);
        $effectiveCredits = true === ($simulateZeroCredits ?? ($orderInput['simulateZeroCredits'] ?? false))
            ? 0
            : (int) $customer->getCredits();
        $missingCredits = max(0, $requiredCredits - $effectiveCredits);

        return [
            'currentCredits' => $effectiveCredits,
            'requiredCredits' => $requiredCredits,
            'missingCredits' => $missingCredits,
            'stageCredits' => $stageCredits,
            'optionCredits' => $optionCredits,
            'topupPricing' => $missingCredits > 0 ? $this->calculateExactCreditsTopupPricing($missingCredits) : null,
        ];
    }

    /**
     * @param array<string, mixed> $orderInput
     * @param array<string, mixed> $quote
     * @param array<string, mixed>|null $orderFlow
     */
    private function hydrateOrderEntity(Auftrag $order, Kunde $customer, array $orderInput, array $quote, ?WinolsConfiguration $winolsConfiguration, ?array $orderFlow, bool $isPending, ?string $forceStatus = null): Auftrag
    {
        $vehicleData = [
            'manufacturer' => $orderInput['manufacturer'],
            'series' => $orderInput['series'],
            'vehicleSeries' => $orderInput['series'],
            'vehicleModel' => $orderInput['model'],
            'engine' => $orderInput['engine'],
            'ecu' => $orderInput['ecu'],
            'ecuVal' => $orderInput['ecu'],
            'readingDevice' => $orderInput['readingDevice'],
            'readingMethod' => $orderInput['readingMethod'],
            'readingType' => $orderInput['readingType'],
            'masterSlave' => $orderInput['masterSlave'],
            'priority' => $orderInput['priority'],
            'comment' => $orderInput['comment'],
            'selectedStage' => $orderInput['selectedStage'],
            'selectedStageKey' => $orderInput['selectedStageKey'],
            'selectedGearboxStage' => $orderInput['selectedGearboxStage'],
            'stageCredits' => $quote['stageCredits'],
            'optionCredits' => $quote['optionCredits'],
            'totalCredits' => $quote['requiredCredits'],
            'selectedOptions' => $orderInput['selectedOptions'],
            'selectedOptionCodes' => $orderInput['selectedOptionCodes'],
            'autoReady' => (bool) ($orderInput['autoReady'] ?? false),
            'simulateZeroCredits' => (bool) ($orderInput['simulateZeroCredits'] ?? false),
        ];

        if (null !== $orderInput['stagePerformance']) {
            $vehicleData['stagePerformance'] = $orderInput['stagePerformance'];
            $vehicleData['stageOriginalPs'] = $orderInput['stagePerformance']['originalPs'];
            $vehicleData['stageOriginalNm'] = $orderInput['stagePerformance']['originalNm'];
            $vehicleData['stageTunedPs'] = $orderInput['stagePerformance']['tunedPs'];
            $vehicleData['stageTunedNm'] = $orderInput['stagePerformance']['tunedNm'];
            $vehicleData['stageGainPs'] = $orderInput['stagePerformance']['gainPs'];
            $vehicleData['stageGainNm'] = $orderInput['stagePerformance']['gainNm'];
        }

        if (null !== $orderFlow) {
            $vehicleData['orderFlow'] = $orderFlow;
        }

        $order
            ->setKunde($customer)
            ->setCustomerEmail((string) ($customer->getEmail() ?? ''))
            ->setFileName((string) $orderInput['fileName'])
            ->setFileData($this->normalizeStoredFileData((string) $orderInput['fileData'], (string) $orderInput['fileName']))
            ->setFileSize((int) $orderInput['fileSize'])
            ->setVehicleType((string) $orderInput['vehicleType'])
            ->setManufacturer('' !== trim((string) $orderInput['manufacturer']) ? (string) $orderInput['manufacturer'] : null)
            ->setModel('' !== trim((string) $orderInput['model']) ? (string) $orderInput['model'] : null)
            ->setBuilt('' !== trim((string) $orderInput['built']) ? (string) $orderInput['built'] : null)
            ->setEngine('' !== trim((string) $orderInput['engine']) ? (string) $orderInput['engine'] : null)
            ->setVehicleDisplay('' !== trim((string) $orderInput['vehicleDisplay']) ? (string) $orderInput['vehicleDisplay'] : null)
            ->setTuningTool('' !== trim((string) $orderInput['readingDevice']) ? (string) $orderInput['readingDevice'] : null)
            ->setTuningMethod('' !== trim((string) $orderInput['readingMethod']) ? (string) $orderInput['readingMethod'] : null)
            ->setSlaveOrMaster('' !== trim((string) $orderInput['masterSlave']) ? (string) $orderInput['masterSlave'] : null)
            ->setStageLabel('' !== trim((string) $orderInput['stageLabel']) ? (string) $orderInput['stageLabel'] : null)
            ->setDeviceName('' !== trim((string) $orderInput['readingDevice']) ? (string) $orderInput['readingDevice'] : null)
        ->setStatus($forceStatus ?? ($isPending ? Auftrag::STATUS_PARKED : ((bool) $orderInput['autoReady'] ? Auftrag::STATUS_COMPLETED : Auftrag::STATUS_RECEIVED)))
            ->setVehicleData($vehicleData);

        if (!$isPending && (bool) $orderInput['autoReady']) {
            $order
                ->setFinishedFileName($this->buildFinishedFileName((string) $order->getFileName()))
                ->setFinishedFileUploadedAt(new \DateTimeImmutable())
                ->setFinishedFileSize($order->getFileSize());
        }

        if (!$isPending) {
            $this->applyAutoModEligibility($order, $orderInput, $winolsConfiguration);
        }

        return $order;
    }

    /**
     * @param array<string, mixed> $orderInput
     */
    private function applyAutoModEligibility(Auftrag $order, array $orderInput, ?WinolsConfiguration $winolsConfiguration): void
    {
        $eligible = !((bool) ($orderInput['autoReady'] ?? false))
            && $this->isStorableFileData((string) ($orderInput['fileData'] ?? ''))
            && $winolsConfiguration instanceof WinolsConfiguration
            && $winolsConfiguration->matchesOrder($order);

        if (!$eligible) {
            return;
        }

        $order->setAutoModEnabled(true);
        if (null === $order->getUlid()) {
            $order->setUlid((string) new \Symfony\Component\Uid\Ulid());
        }

        // Kein echter Entschluesselungsschritt existiert in dieser Codebase - die Datei
        // liegt bereits unverschluesselt in fileData. DECRYPTED wird daher direkt beim
        // Submit gesetzt, damit WinOLS den Auftrag sofort abholen kann.
        $order->setAutoModStatus(Auftrag::AUTOMOD_STATUS_DECRYPTED);
    }

    private function isStorableFileData(string $rawBase64): bool
    {
        $normalized = trim($rawBase64);
        if ('' === $normalized) {
            return false;
        }

        $decoded = base64_decode($normalized, true);

        return false !== $decoded && strlen($decoded) <= 1024 * 1024;
    }

    /**
     * @param array<string, mixed> $quote
     */
    private function finalizeOrderCredits(Auftrag $order, Kunde $customer, array $quote, EntityManagerInterface $entityManager): int
    {
        $requiredCredits = (int) ($quote['requiredCredits'] ?? 0);
        $balanceBefore = (int) $customer->getCredits();
        if ($requiredCredits <= 0) {
            return $balanceBefore;
        }

        if ($balanceBefore < $requiredCredits) {
            throw new \RuntimeException('Nicht genug Credits fuer die finale Auftragserstellung.');
        }

        $balanceAfter = max(0, $balanceBefore - $requiredCredits);
        $customer->setCredits($balanceAfter);

        $descriptionParts = array_filter([
            sprintf('Auftrag %s', $order->getOrderNumber()),
            trim((string) ($order->getVehicleDisplay() ?? '')) ?: null,
            trim((string) ($order->getStageLabel() ?? '')) ?: null,
            sprintf('%d Credits', $requiredCredits),
        ], static fn (?string $value): bool => null !== $value && '' !== trim($value));

        $creditTransaction = (new KundeCreditTransaction())
            ->setKunde($customer)
            ->setAmount(-$requiredCredits)
            ->setTransactionType(KundeCreditTransaction::TYPE_USAGE)
            ->setDescription(implode(' | ', $descriptionParts))
            ->setBalanceBefore($balanceBefore)
            ->setBalanceAfter($balanceAfter);

        $entityManager->persist($creditTransaction);
        $entityManager->persist($customer);
        $entityManager->flush();

        return $balanceAfter;
    }

    /**
     * @param array<string, mixed> $orderInput
     * @param array<string, mixed> $quote
     */
    private function upsertPendingCreditTopupOrder(Kunde $customer, array $orderInput, array $quote, EntityManagerInterface $entityManager, ?WinolsConfiguration $winolsConfiguration): Auftrag
    {
        $pendingOrderId = (int) ($orderInput['pendingOrderId'] ?? 0);
        $pendingOrder = $pendingOrderId > 0 ? $this->findPendingCreditTopupOrder($customer, $pendingOrderId) : null;

        $orderFlow = [
            'context' => 'order_flow_credit_topup',
            'state' => 'awaiting_credit_payment',
            'requiredCredits' => $quote['requiredCredits'],
            'missingCredits' => $quote['missingCredits'],
            'currentCredits' => $quote['currentCredits'],
            'simulateZeroCredits' => (bool) ($orderInput['simulateZeroCredits'] ?? false),
            'updatedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        $order = $this->hydrateOrderEntity($pendingOrder ?? new Auftrag(), $customer, $orderInput, $quote, $winolsConfiguration, $orderFlow, true, null);
        $entityManager->persist($order);
        $entityManager->flush();

        return $order;
    }

    private function findPendingCreditTopupOrder(Kunde $customer, int $pendingOrderId): ?Auftrag
    {
        if ($pendingOrderId <= 0) {
            return null;
        }

        $order = $this->auftragRepository->find($pendingOrderId);
        if (!$order instanceof Auftrag || $order->getKunde()?->getId() !== $customer->getId()) {
            return null;
        }

        $vehicleData = $order->getVehicleData();
        if (($vehicleData['orderFlow']['context'] ?? null) !== 'order_flow_credit_topup') {
            return null;
        }

        return $order;
    }

    /**
     * @param array<string, mixed> $quote
     */
    private function finalizePendingCreditTopupOrder(Auftrag $order, Kunde $customer, array $quote, EntityManagerInterface $entityManager, ?WinolsConfiguration $winolsConfiguration): void
    {
        $vehicleData = $order->getVehicleData();
        if (($vehicleData['orderFlow']['state'] ?? null) === 'finalized') {
            return;
        }

        $this->finalizeOrderCredits($order, $customer, $quote, $entityManager);
        $order->setStatus((bool) ($vehicleData['autoReady'] ?? false) ? Auftrag::STATUS_COMPLETED : Auftrag::STATUS_RECEIVED);
        $vehicleData['orderFlow']['state'] = 'finalized';
        $vehicleData['orderFlow']['finalizedAt'] = (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM);
        $order->setVehicleData($vehicleData);

        if ((bool) ($vehicleData['autoReady'] ?? false)) {
            $this->ensureAutoReadyFinishedFile($order);
        } else {
            $this->applyAutoModEligibility($order, ['autoReady' => false, 'fileData' => (string) $order->getFileData()], $winolsConfiguration);
        }

        $entityManager->persist($order);
        $entityManager->flush();
    }

    private function resolveWinolsConfiguration(Portal $portalConfig): ?WinolsConfiguration
    {
        return $this->winolsConfigurationRepository->findActiveForPortal($portalConfig);
    }

    private function resolvePriceListSection(string $vehicleType): string
    {
        $normalized = strtolower(trim($vehicleType));

        return preg_match('/(lkw|truck|agrar|tractor|bus|nfz)/i', $normalized) === 1
            ? PortalPriceListItem::SECTION_LKW
            : PortalPriceListItem::SECTION_PKW;
    }

    private function findPriceListItem(PortalPriceList $priceList, string $section, string $category, string $code): ?PortalPriceListItem
    {
        $normalizedCode = strtolower(trim($code));
        if ('' === $normalizedCode) {
            return null;
        }

        foreach ($priceList->getItems() as $item) {
            if (!$item instanceof PortalPriceListItem || !$item->isActive()) {
                continue;
            }

            if ($item->getSection() !== $section || $item->getCategory() !== $category) {
                continue;
            }

            if (strtolower(trim($item->getCode())) === $normalizedCode) {
                return $item;
            }
        }

        return null;
    }

    private function findPriceListOptionItem(PortalPriceList $priceList, string $section, string $category, string $code, string $stageCode): ?PortalPriceListItem
    {
        $normalizedCode = strtolower(trim($code));
        $normalizedStageCode = strtolower(trim($stageCode));
        $fallback = null;

        foreach ($priceList->getItems() as $item) {
            if (!$item instanceof PortalPriceListItem || !$item->isActive()) {
                continue;
            }

            if ($item->getSection() !== $section || $item->getCategory() !== $category) {
                continue;
            }

            if (strtolower(trim($item->getCode())) !== $normalizedCode) {
                continue;
            }

            $itemStageCode = strtolower(trim($item->getStageCode()));
            if ('' !== $normalizedStageCode && $itemStageCode === $normalizedStageCode) {
                return $item;
            }

            if ('' === $itemStageCode) {
                $fallback = $item;
            }
        }

        return $fallback;
    }

    /**
     * @return array{priceNet:float,priceGross:float,taxRate:float,paymentFee:float}
     */
    private function calculateExactCreditsTopupPricing(int $requestedCredits): array
    {
        $packages = array_values(array_filter(
            $this->creditPackageRepository->findBy([], ['credits' => 'ASC']),
            static fn (mixed $package): bool => $package instanceof \App\Entity\CreditPackage && $package->getCredits() > 0 && (float) $package->getPrice() > 0
        ));

        if ([] === $packages) {
            throw new \RuntimeException('Keine Credit-Pakete fuer die Preisberechnung gefunden.');
        }

        usort($packages, static fn (\App\Entity\CreditPackage $a, \App\Entity\CreditPackage $b): int => $b->getCredits() <=> $a->getCredits());
        $tier = $packages[array_key_last($packages)];
        foreach ($packages as $package) {
            if ($requestedCredits >= $package->getCredits()) {
                $tier = $package;
                break;
            }
        }

        $pricePerCredit = (float) $tier->getPrice() / max(1, $tier->getCredits());
        $net = round($requestedCredits * $pricePerCredit, 2);
        $gross = round($net * 1.19, 2);

        return [
            'priceNet' => $net,
            'priceGross' => $gross,
            'taxRate' => 19.0,
            'paymentFee' => 0.0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function extractOrderInputFromVehicleData(Auftrag $order): array
    {
        $vehicleData = $order->getVehicleData();

        return [
            'fileName' => (string) ($order->getFileName() ?? ''),
            'fileSize' => (int) ($order->getFileSize() ?? 0),
            'fileData' => (string) ($order->getFileData() ?? ''),
            'manufacturer' => (string) ($vehicleData['manufacturer'] ?? ''),
            'series' => (string) ($vehicleData['series'] ?? ''),
            'model' => (string) ($vehicleData['vehicleModel'] ?? ''),
            'engine' => (string) ($vehicleData['engine'] ?? ''),
            'built' => (string) ($order->getBuilt() ?? ''),
            'ecu' => (string) ($vehicleData['ecu'] ?? ''),
            'vehicleType' => (string) ($order->getVehicleType() ?? 'PKW'),
            'vehicleDisplay' => (string) ($order->getVehicleDisplay() ?? ''),
            'readingDevice' => (string) ($vehicleData['readingDevice'] ?? ''),
            'readingMethod' => (string) ($vehicleData['readingMethod'] ?? ''),
            'readingType' => (string) ($vehicleData['readingType'] ?? ''),
            'masterSlave' => (string) ($vehicleData['masterSlave'] ?? ''),
            'priority' => (string) ($vehicleData['priority'] ?? ''),
            'comment' => (string) ($vehicleData['comment'] ?? ''),
            'stageLabel' => (string) ($order->getStageLabel() ?? ''),
            'selectedStage' => (string) ($vehicleData['selectedStage'] ?? ''),
            'selectedStageKey' => (string) ($vehicleData['selectedStageKey'] ?? ''),
            'selectedGearboxStage' => (string) ($vehicleData['selectedGearboxStage'] ?? ''),
            'selectedOptions' => is_array($vehicleData['selectedOptions'] ?? null) ? $vehicleData['selectedOptions'] : [],
            'selectedOptionCodes' => is_array($vehicleData['selectedOptionCodes'] ?? null) ? $vehicleData['selectedOptionCodes'] : [],
            'stagePerformance' => is_array($vehicleData['stagePerformance'] ?? null) ? $vehicleData['stagePerformance'] : null,
            'autoReady' => false,
            'simulateZeroCredits' => (bool) ($vehicleData['simulateZeroCredits'] ?? false),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildSteps(): array
    {
        return [
            [
                'number' => '01',
                'title' => 'Datei',
                'description' => 'Platzhalter fuer Upload, Steuergeraet und Softwarestand.',
                'eyebrow' => 'Datei & ECU',
                'headline' => 'Schritt 1: Datei und Steuergeraet',
                'intro' => 'Diese Dummy-Stufe simuliert spaeter den Upload-Bereich. Hier koennen wir danach echte Datei-Uploads, Checks und ECU-Daten andocken.',
                'badge' => 'Upload Preview',
                'primaryAction' => 'Weiter zu Fahrzeug',
                'summary' => [
                    ['label' => 'Originaldatei', 'value' => 'bmw_f30_320d_ori.bin'],
                    ['label' => 'Dateigroesse', 'value' => '2.048 KB'],
                    ['label' => 'Steuergeraet', 'value' => 'Bosch EDC17C50'],
                    ['label' => 'Softwarestand', 'value' => '1037390908'],
                ],
                'checklist' => [
                    ['icon' => 'ti ti-file-code', 'title' => 'Datei pruefen', 'description' => 'Format, Vollstaendigkeit und Lesbarkeit als spaeterer Check.'],
                    ['icon' => 'ti ti-cpu', 'title' => 'ECU erkennen', 'description' => 'Steuergeraet und Calibration-ID automatisch zuordnen.'],
                    ['icon' => 'ti ti-cloud-upload', 'title' => 'An Tuning Queue uebergeben', 'description' => 'Dummy fuer internen Workflow und Bearbeitungsstatus.'],
                ],
                'fields' => [
                    ['type' => 'text', 'label' => 'Dateiname', 'value' => 'bmw_f30_320d_ori.bin'],
                    ['type' => 'text', 'label' => 'Dateityp', 'value' => 'BIN / Full Read'],
                    ['type' => 'text', 'label' => 'Steuergeraet', 'value' => 'Bosch EDC17C50'],
                    ['type' => 'text', 'label' => 'Calibration ID', 'value' => '1037390908'],
                    ['type' => 'text', 'label' => 'Werkzeug', 'value' => 'Autotuner Bench'],
                    ['type' => 'text', 'label' => 'Pruefsummen-Status', 'value' => 'Noch nicht geprueft'],
                    ['type' => 'textarea', 'label' => 'Upload-Notiz', 'value' => 'Datei wurde im Bench-Modus ausgelesen. Kunde meldet keine aktiven Fehler und kein Start-Stopp.'],
                ],
            ],
            [
                'number' => '02',
                'title' => 'Fahrzeug',
                'description' => 'Dummy-Auswahl fuer Hersteller, Modell und Motorisierung.',
                'eyebrow' => 'Basisdaten',
                'headline' => 'Schritt 2: Fahrzeugdaten',
                'intro' => 'Hier sammeln wir die grundlegenden Fahrzeuginfos fuer den Auftrag. Alles ist noch reiner Dummy, aber die Struktur steht schon fuer die spaetere Form.',
                'badge' => 'Aktiver Step',
                'primaryAction' => 'Weiter zu Konfiguration',
                'summary' => [
                    ['label' => 'Status', 'value' => 'Entwurf'],
                    ['label' => 'Paket', 'value' => 'Stage 1'],
                    ['label' => 'Fahrzeug', 'value' => 'BMW 320d F30'],
                    ['label' => 'Prioritaet', 'value' => 'Standard'],
                ],
                'checklist' => [
                    ['icon' => 'ti ti-adjustments', 'title' => 'Optionen waehlen', 'description' => 'Stage, Pops & Bangs, Vmax oder AGR als Platzhalter.'],
                    ['icon' => 'ti ti-clock-hour-4', 'title' => 'Ablauf festlegen', 'description' => 'Bearbeitungszeit und Lieferung kommen danach.'],
                    ['icon' => 'ti ti-checklist', 'title' => 'Auftrag pruefen', 'description' => 'Summary, Preis, Credits und Freigabe vor dem Versand.'],
                ],
                'fields' => [
                    ['type' => 'select', 'label' => 'Hersteller', 'value' => 'BMW', 'options' => ['BMW', 'Audi', 'Mercedes-Benz', 'Volkswagen']],
                    ['type' => 'select', 'label' => 'Modell', 'value' => '320d F30', 'options' => ['320d F30', 'Golf 7 GTI', 'C 220d W205']],
                    ['type' => 'text', 'label' => 'Motorisierung', 'value' => '2.0 Diesel 190 PS'],
                    ['type' => 'text', 'label' => 'Baujahr', 'value' => '2018'],
                    ['type' => 'select', 'label' => 'Getriebe', 'value' => 'Automatik', 'options' => ['Automatik', 'Manuell']],
                    ['type' => 'text', 'label' => 'Leistungsziel', 'value' => '220 PS / 460 Nm'],
                    ['type' => 'textarea', 'label' => 'Hinweise zum Auftrag', 'value' => 'Kunde moechte eine alltagstaugliche Stage 1 mit Fokus auf saubere Leistungsentfaltung und unveraendertem Kaltstart.'],
                ],
            ],
            [
                'number' => '03',
                'title' => 'Konfiguration',
                'description' => 'Optionen wie Stage, AGR, DPF oder Vmax als Dummy.',
                'eyebrow' => 'Setup',
                'headline' => 'Schritt 3: Tuning-Konfiguration',
                'intro' => 'Hier liegen die spaeteren Paket- und Funktionsoptionen. Fuer jetzt zeigen wir die komplette Struktur mit Dummy-Werten und vorbereiteten Auswahlfeldern.',
                'badge' => 'Options Dummy',
                'primaryAction' => 'Weiter zu Ablauf',
                'summary' => [
                    ['label' => 'Paket', 'value' => 'Stage 1'],
                    ['label' => 'Zusatzoptionen', 'value' => 'Vmax Off, AGR Off'],
                    ['label' => 'Fahrmodus', 'value' => 'Alltag / Touring'],
                    ['label' => 'Risiko-Level', 'value' => 'Konservativ'],
                ],
                'checklist' => [
                    ['icon' => 'ti ti-bolt', 'title' => 'Leistung definieren', 'description' => 'Stage-Level und Zielkennlinie als Platzhalter.'],
                    ['icon' => 'ti ti-gauge', 'title' => 'Begrenzungen festlegen', 'description' => 'Vmax, Drehmoment und Launch-Control spaeter dynamisch.'],
                    ['icon' => 'ti ti-settings-cog', 'title' => 'Zusatzfeatures sammeln', 'description' => 'AGR, DPF, AdBlue oder Start-Stopp als Dummy.'],
                ],
                'fields' => [
                    ['type' => 'select', 'label' => 'Tuning-Paket', 'value' => 'Stage 1', 'options' => ['Serie', 'Stage 1', 'Stage 2']],
                    ['type' => 'select', 'label' => 'Vmax', 'value' => 'Begrenzer entfernen', 'options' => ['Serie', 'Begrenzer anheben', 'Begrenzer entfernen']],
                    ['type' => 'select', 'label' => 'AGR', 'value' => 'Off', 'options' => ['Serie', 'On', 'Off']],
                    ['type' => 'select', 'label' => 'DPF', 'value' => 'Serie', 'options' => ['Serie', 'Vorbereitung', 'Off']],
                    ['type' => 'select', 'label' => 'AdBlue', 'value' => 'Serie', 'options' => ['Serie', 'Off']],
                    ['type' => 'text', 'label' => 'Zielwert Leistung', 'value' => '220 PS'],
                    ['type' => 'textarea', 'label' => 'Interne Bemerkung', 'value' => 'Bitte harmonische Gasannahme beibehalten, keine aggressive Momentenfreigabe im unteren Drehzahlbereich.'],
                ],
            ],
            [
                'number' => '04',
                'title' => 'Ablauf',
                'description' => 'Lieferzeit, Prioritaet und Kommunikation als Dummy.',
                'eyebrow' => 'Workflow',
                'headline' => 'Schritt 4: Ablauf und Lieferung',
                'intro' => 'Diese Dummy-Stufe simuliert alles rund um Bearbeitungszeit, Lieferkanal und Abstimmung. So koennen wir spaeter den kompletten Prozess sauber abbilden.',
                'badge' => 'Workflow Preview',
                'primaryAction' => 'Weiter zu Pruefung',
                'summary' => [
                    ['label' => 'Bearbeitung', 'value' => 'Same Day'],
                    ['label' => 'Lieferkanal', 'value' => 'Portal Download'],
                    ['label' => 'Rueckfragen', 'value' => 'Telefon + E-Mail'],
                    ['label' => 'Deadline', 'value' => 'Heute 18:00'],
                ],
                'checklist' => [
                    ['icon' => 'ti ti-clock-hour-4', 'title' => 'Bearbeitungszeit waehlen', 'description' => 'Standard, Express oder Nachtservice als spaeteres Modell.'],
                    ['icon' => 'ti ti-message-2', 'title' => 'Kommunikation festlegen', 'description' => 'Rueckfragen und Freigaben koennen hier vorbereitet werden.'],
                    ['icon' => 'ti ti-download', 'title' => 'Auslieferung bestimmen', 'description' => 'Portal, E-Mail oder API-Delivery als Dummy.'],
                ],
                'fields' => [
                    ['type' => 'select', 'label' => 'Bearbeitungszeit', 'value' => 'Same Day', 'options' => ['Standard 24h', 'Same Day', 'Express 1h']],
                    ['type' => 'select', 'label' => 'Prioritaet', 'value' => 'Standard', 'options' => ['Standard', 'Hoch', 'Express']],
                    ['type' => 'select', 'label' => 'Lieferkanal', 'value' => 'Portal Download', 'options' => ['Portal Download', 'E-Mail', 'WhatsApp']],
                    ['type' => 'text', 'label' => 'Gewuenschte Deadline', 'value' => '21.03.2026 18:00'],
                    ['type' => 'text', 'label' => 'Rueckfragen an', 'value' => '0172 1234567 / dummy.chip08@example.com'],
                    ['type' => 'text', 'label' => 'Fahrzeug steht bereit bis', 'value' => '22.03.2026 12:00'],
                    ['type' => 'textarea', 'label' => 'Ablauf-Hinweis', 'value' => 'Wenn moeglich Rueckmeldung vor Freigabe, da Kunde noch zwischen Stage 1 und Stage 1+ schwankt.'],
                ],
            ],
            [
                'number' => '05',
                'title' => 'Pruefung',
                'description' => 'Zusammenfassung, Preis und Freigabe vor dem Absenden.',
                'eyebrow' => 'Finale Kontrolle',
                'headline' => 'Schritt 5: Auftrag pruefen und absenden',
                'intro' => 'Zum Abschluss zeigen wir die spaetere Freigabe-Ansicht. Hier landen Summary, Credits, Preis und die finale Absende-Aktion als kompletter Dummy.',
                'badge' => 'Final Preview',
                'primaryAction' => 'Dummy-Auftrag absenden',
                'summary' => [
                    ['label' => 'Gesamtpaket', 'value' => 'Stage 1 + Vmax Off'],
                    ['label' => 'Preis', 'value' => '149,00 EUR'],
                    ['label' => 'Credits', 'value' => '15 Credits'],
                    ['label' => 'Freigabe', 'value' => 'Noch nicht bestaetigt'],
                ],
                'checklist' => [
                    ['icon' => 'ti ti-file-check', 'title' => 'Daten kontrollieren', 'description' => 'Fahrzeug, Datei und Optionen werden hier final geprueft.'],
                    ['icon' => 'ti ti-receipt-euro', 'title' => 'Preis bestaetigen', 'description' => 'Credits oder Einzelpreis koennen hier angezeigt werden.'],
                    ['icon' => 'ti ti-send', 'title' => 'Auftrag absenden', 'description' => 'Danach startet spaeter die echte Verarbeitung.'],
                ],
                'fields' => [
                    ['type' => 'summary-box', 'label' => 'Fahrzeug', 'value' => 'BMW 320d F30 / 2.0 Diesel / 2018'],
                    ['type' => 'summary-box', 'label' => 'Datei', 'value' => 'bmw_f30_320d_ori.bin / Bosch EDC17C50'],
                    ['type' => 'summary-box', 'label' => 'Konfiguration', 'value' => 'Stage 1, Vmax Off, AGR Off, konservatives Setup'],
                    ['type' => 'summary-box', 'label' => 'Ablauf', 'value' => 'Same Day / Portal Download / Rueckfrage vor Freigabe'],
                    ['type' => 'text', 'label' => 'Preisvorschau', 'value' => '149,00 EUR oder 15 Credits'],
                    ['type' => 'text', 'label' => 'Freigabe-Checkbox', 'value' => 'Dummy: Ich bestaetige alle Angaben'],
                    ['type' => 'textarea', 'label' => 'Letzte Anmerkung', 'value' => 'Bitte finalen Datensatz erst ausliefern, wenn die interne Checksum-Pruefung erfolgreich ist.'],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function readString(array $payload, string $key, bool $required = true): string
    {
        $value = $payload[$key] ?? null;
        if (!is_string($value)) {
            if ($required) {
                throw new \InvalidArgumentException(sprintf('%s fehlt oder ist ungueltig.', $key));
            }

            return '';
        }

        $value = trim($value);
        if ($required && '' === $value) {
            throw new \InvalidArgumentException(sprintf('%s darf nicht leer sein.', $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function readPositiveInt(array $payload, string $key): int
    {
        $value = $payload[$key] ?? null;
        if (!is_int($value) && !is_string($value)) {
            throw new \InvalidArgumentException(sprintf('%s fehlt oder ist ungueltig.', $key));
        }

        if (!is_numeric((string) $value)) {
            throw new \InvalidArgumentException(sprintf('%s ist ungueltig.', $key));
        }

        $normalized = (int) $value;
        if ($normalized <= 0) {
            throw new \InvalidArgumentException(sprintf('%s muss groesser als 0 sein.', $key));
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function readNonNegativeInt(array $payload, string $key, int $default = 0): int
    {
        $value = $payload[$key] ?? null;
        if (null === $value || '' === $value) {
            return max(0, $default);
        }

        if (!is_int($value) && !is_string($value) && !is_float($value)) {
            return max(0, $default);
        }

        if (!is_numeric((string) $value)) {
            return max(0, $default);
        }

        return max(0, (int) round((float) $value));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function readBoolean(array $payload, string $key, bool $default = false): bool
    {
        $value = $payload[$key] ?? null;
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value !== 0;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }
            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return $default;
    }

    private function getCurrentCustomer(): Kunde
    {
        $user = $this->getUser();
        if (!$user instanceof No1tuningfilesPortalCustomerUser) {
            throw $this->createAccessDeniedException();
        }

        return $user->getCustomer();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPriceListBootstrap(): array
    {
        $priceList = $this->portalPriceListRepository->findOneBy(['active' => true], ['id' => 'ASC']);
        if (!$priceList instanceof PortalPriceList) {
            return [
                'name' => null,
                'description' => null,
                'sections' => [
                    PortalPriceListItem::SECTION_PKW => [],
                    PortalPriceListItem::SECTION_LKW => [],
                ],
            ];
        }

        $sections = [
            PortalPriceListItem::SECTION_PKW => [],
            PortalPriceListItem::SECTION_LKW => [],
        ];

        foreach ($priceList->getItems() as $item) {
            if (!$item instanceof PortalPriceListItem || !$item->isActive()) {
                continue;
            }

            $section = $item->getSection();
            if (!array_key_exists($section, $sections)) {
                $sections[$section] = [];
            }

            $category = $item->getCategory();
            $sections[$section][$category] ??= [];
            $sections[$section][$category][] = [
                'id' => $item->getCode(),
                'code' => $item->getCode(),
                'stageCode' => $item->getStageCode(),
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'credits' => $item->getCredits(),
                'included' => $item->isIncluded(),
                'icon' => $this->resolvePriceListIcon($item),
            ];
        }

        return [
            'name' => $priceList->getName(),
            'description' => $priceList->getDescription(),
            'sections' => $sections,
        ];
    }

    private function resolvePriceListIcon(PortalPriceListItem $item): string
    {
        $iconByCode = [
            'adblue' => 'drop',
            'auspuffklappen' => 'fire',
            'bmw_mini_garantie_patch' => 'warning',
            'cat' => 'prohibit',
            'cvn_patch' => 'warning',
            'dpf' => 'drop',
            'drehmomentueberwachung_off' => 'gauge',
            'dtc' => 'warning',
            'egr' => 'fan',
            'hardcut_popcorn_limiter' => 'fire',
            'harter_begrenzer' => 'fire',
            'hotstart' => 'thermometer',
            'kaltstart_off' => 'power',
            'launch' => 'rocket',
            'launch_control_anti_lag_me7' => 'rocket',
            'launch_control_anti_lag_me9' => 'rocket',
            'launch_control_anti_lag_med17' => 'rocket',
            'luftmengenmesser_off' => 'fan',
            'nox_off' => 'prohibit',
            'oeldruck_fix' => 'thermometer',
            'opf_gpf_off' => 'prohibit',
            'opf_off' => 'prohibit',
            'pop_bang_sport' => 'fire',
            'pop_bang_tempomat_me9' => 'fire',
            'pops' => 'fire',
            'sekundaerluftpumpe_off' => 'fan',
            'sport_display' => 'gauge',
            'startstop' => 'power',
            'swirl' => 'fan',
            'tuning_auf_sport_button' => 'gauge',
            'vmax' => 'gauge',
            'vmax_limit_setzen' => 'gauge',
            'wegfahrsperre_immo_off' => 'prohibit',
            'zylinderabschaltung_off' => 'power',
            'dsg_furzen' => 'fire',
        ];
        $code = mb_strtolower(trim($item->getCode()));
        if ('' !== $code && isset($iconByCode[$code])) {
            return $iconByCode[$code];
        }

        $haystack = mb_strtolower(trim($item->getCode() . ' ' . $item->getName()));

        return match (true) {
            str_contains($haystack, 'dpf'), str_contains($haystack, 'adblue'), str_contains($haystack, 'scr') => 'drop',
            str_contains($haystack, 'egr'), str_contains($haystack, 'agr'), str_contains($haystack, 'swirl'), str_contains($haystack, 'drall'), str_contains($haystack, 'fan') => 'fan',
            str_contains($haystack, 'vmax'), str_contains($haystack, 'speed'), str_contains($haystack, 'gauge') => 'gauge',
            str_contains($haystack, 'dtc'), str_contains($haystack, 'error'), str_contains($haystack, 'warn') => 'warning',
            str_contains($haystack, 'start'), str_contains($haystack, 'power') => 'power',
            str_contains($haystack, 'pops'), str_contains($haystack, 'bang'), str_contains($haystack, 'fire') => 'fire',
            str_contains($haystack, 'launch'), str_contains($haystack, 'rocket') => 'rocket',
            str_contains($haystack, 'cat'), str_contains($haystack, 'nox'), str_contains($haystack, 'o2'), str_contains($haystack, 'lambda'), str_contains($haystack, 'opf'), str_contains($haystack, 'gpf') => 'prohibit',
            str_contains($haystack, 'hot'), str_contains($haystack, 'temp'), str_contains($haystack, 'thermo'), str_contains($haystack, 'oeldruck'), str_contains($haystack, 'öldruck'), str_contains($haystack, 'oil') => 'thermometer',
            default => 'gear',
        };
    }

    private function normalizeStoredFileData(string $rawBase64, string $fileName): string
    {
        $normalized = trim($rawBase64);
        if ($normalized !== '') {
            $decoded = base64_decode($normalized, true);
            if (false !== $decoded && strlen($decoded) <= 1024 * 1024) {
                return $normalized;
            }
        }

        return base64_encode(sprintf('portal-order-placeholder:%s:%s', $fileName, (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)));
    }

    private function resolveOrderForCustomer(No1tuningfilesPortalCustomerUser $user, string $orderNumber): ?Auftrag
    {
        $normalizedOrderNumber = trim($orderNumber);
        if ('' === $normalizedOrderNumber) {
            return null;
        }

        $customer = $user->getCustomer();
        $customerEmail = mb_strtolower(trim((string) $customer->getEmail()));

        $queryBuilder = $this->auftragRepository->createQueryBuilder('a')
            ->andWhere('a.orderNumber = :orderNumber')
            ->setParameter('orderNumber', $normalizedOrderNumber)
            ->setMaxResults(1);

        if ('' !== $customerEmail) {
            $queryBuilder
                ->andWhere('(a.kunde = :customer OR LOWER(a.customerEmail) = :customerEmail)')
                ->setParameter('customer', $customer)
                ->setParameter('customerEmail', $customerEmail);
        } else {
            $queryBuilder
                ->andWhere('a.kunde = :customer')
                ->setParameter('customer', $customer);
        }

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result instanceof Auftrag ? $result : null;
    }

    private function resolveParkedOrderForCustomer(Kunde $customer, int $parkedOrderId): ?Auftrag
    {
        if ($parkedOrderId <= 0) {
            return null;
        }

        $order = $this->auftragRepository->find($parkedOrderId);
        if (!$order instanceof Auftrag || $order->getKunde()?->getId() !== $customer->getId()) {
            return null;
        }

        if ($order->getStatus() !== Auftrag::STATUS_PARKED) {
            return null;
        }

        return $order;
    }

    private function buildParkedOrderDetail(Auftrag $order): array
    {
        $vehicleData = is_array($order->getVehicleData()) ? $order->getVehicleData() : [];
        $selectedOptions = is_array($vehicleData['selectedOptions'] ?? null) ? array_values($vehicleData['selectedOptions']) : [];
        $selectedOptionCodes = is_array($vehicleData['selectedOptionCodes'] ?? null) ? $vehicleData['selectedOptionCodes'] : [];

        return [
            'parkedOrderId' => $order->getId(),
            'orderNumber' => $order->getOrderNumber(),
            'status' => $order->getStatus(),
            'createdAt' => $order->getCreatedAt()?->format(\DATE_ATOM),
            'updatedAt' => $order->getUpdatedAt()?->format(\DATE_ATOM),
            'fileName' => (string) ($order->getFileName() ?? ''),
            'fileSize' => $order->getFileSize(),
            'fileData' => (string) ($order->getFileData() ?? ''),
            'vehicleType' => (string) ($order->getVehicleType() ?? 'PKW'),
            'manufacturer' => (string) ($order->getManufacturer() ?? ''),
            'series' => (string) ($order->getVehicleSeries() ?? ''),
            'model' => (string) ($order->getModel() ?? ''),
            'engine' => (string) ($order->getEngine() ?? ''),
            'ecu' => (string) ($order->getEcu() ?? ''),
            'readingDevice' => (string) ($vehicleData['readingDevice'] ?? ''),
            'readingMethod' => (string) ($vehicleData['readingMethod'] ?? ''),
            'readingType' => (string) ($vehicleData['readingType'] ?? ''),
            'masterSlave' => (string) ($vehicleData['masterSlave'] ?? ''),
            'priority' => (string) ($vehicleData['priority'] ?? ''),
            'comment' => (string) ($vehicleData['comment'] ?? ''),
            'stageLabel' => (string) ($order->getStageLabel() ?? ''),
            'selectedStage' => (string) ($vehicleData['selectedStage'] ?? ''),
            'selectedStageKey' => (string) ($vehicleData['selectedStageKey'] ?? ''),
            'selectedGearboxStage' => (string) ($vehicleData['selectedGearboxStage'] ?? ''),
            'selectedOptions' => $selectedOptions,
            'selectedOptionCodes' => $selectedOptionCodes,
            'stagePerformance' => is_array($vehicleData['stagePerformance'] ?? null) ? $vehicleData['stagePerformance'] : null,
            'stageCredits' => (int) ($vehicleData['stageCredits'] ?? 0),
            'optionCredits' => (int) ($vehicleData['optionCredits'] ?? 0),
            'totalCredits' => (int) ($vehicleData['totalCredits'] ?? 0),
            'autoReady' => (bool) ($vehicleData['autoReady'] ?? false),
            'simulateZeroCredits' => (bool) ($vehicleData['simulateZeroCredits'] ?? false),
        ];
    }

    private function buildFinishedFileName(string $originalFileName): string
    {
        $trimmed = trim($originalFileName);
        if ('' === $trimmed) {
            return 'mod_file.bin';
        }

        $extension = pathinfo($trimmed, \PATHINFO_EXTENSION);
        $base = pathinfo($trimmed, \PATHINFO_FILENAME);
        $base = '' !== trim((string) $base) ? (string) $base : $trimmed;

        if ('' === trim((string) $extension)) {
            return sprintf('%s_mod', $base);
        }

        return sprintf('%s_mod.%s', $base, $extension);
    }

    private function ensureAutoReadyFinishedFile(Auftrag $order): void
    {
        $orderId = $order->getId();
        if (null === $orderId) {
            return;
        }

        $existingPath = trim((string) $order->getFinishedFilePath());
        if ('' !== $existingPath) {
            return;
        }

        $targetName = trim((string) $order->getFinishedFileName());
        if ('' === $targetName) {
            $targetName = $this->buildFinishedFileName((string) $order->getFileName());
            $order->setFinishedFileName($targetName);
        }

        $relativeDir = sprintf('uploads/auftraege/fertig/%d', $orderId);
        $absoluteDir = rtrim((string) $this->getParameter('kernel.project_dir'), '/') . '/public/' . $relativeDir;

        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            return;
        }

        $targetName = str_replace("\0", '', $targetName);
        $targetName = str_replace('\\', '/', $targetName);
        $targetName = trim(basename($targetName));
        if ('' === $targetName) {
            $targetName = 'mod_file.bin';
        }

        $absolutePath = $absoluteDir . '/' . $targetName;
        if (is_file($absolutePath)) {
            $timestamp = (new \DateTimeImmutable())->format('Ymd_His');
            $base = pathinfo($targetName, \PATHINFO_FILENAME);
            $ext = pathinfo($targetName, \PATHINFO_EXTENSION);
            $targetName = '' !== $ext ? sprintf('%s_%s.%s', $base, $timestamp, $ext) : sprintf('%s_%s', $base, $timestamp);
            $absolutePath = $absoluteDir . '/' . $targetName;
        }

        $decoded = base64_decode((string) ($order->getFileData() ?? ''), true);
        if (false === $decoded) {
            $decoded = sprintf(
                'auto-ready placeholder for order %s at %s',
                (string) ($order->getOrderNumber() ?? ''),
                (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)
            );
        }

        if (@file_put_contents($absolutePath, $decoded) === false) {
            return;
        }

        $relativePath = $relativeDir . '/' . $targetName;
        $fileSize = @filesize($absolutePath);
        $uploadedAt = new \DateTimeImmutable();

        $order
            ->setFinishedFilePath($relativePath)
            ->setFinishedFileName($targetName)
            ->setFinishedFileSize(is_int($fileSize) ? $fileSize : null)
            ->setFinishedFileUploadedAt($uploadedAt)
            ->setStatus(Auftrag::STATUS_COMPLETED);

        $files = $order->getFinishedFiles();
        $files[] = [
            'path' => $relativePath,
            'name' => $targetName,
            'size' => is_int($fileSize) ? $fileSize : null,
            'uploadedAt' => $uploadedAt->format(\DateTimeInterface::ATOM),
        ];
        $order->setFinishedFiles($files);
    }

    /**
     * @param array<string, mixed>|null $payload
     * @return array{originalPs:int|null,originalNm:int|null,tunedPs:int|null,tunedNm:int|null,gainPs:int|null,gainNm:int|null}|null
     */
    private function normalizeStagePerformance(?array $payload): ?array
    {
        if (!is_array($payload) || [] === $payload) {
            return null;
        }

        $readInt = static function (mixed $value): ?int {
            if (is_int($value)) {
                return $value;
            }

            if (is_float($value) || is_string($value)) {
                if (!is_numeric((string) $value)) {
                    return null;
                }

                return (int) round((float) $value);
            }

            return null;
        };

        $normalized = [
            'originalPs' => $readInt($payload['originalPs'] ?? null),
            'originalNm' => $readInt($payload['originalNm'] ?? null),
            'tunedPs' => $readInt($payload['tunedPs'] ?? null),
            'tunedNm' => $readInt($payload['tunedNm'] ?? null),
            'gainPs' => $readInt($payload['gainPs'] ?? null),
            'gainNm' => $readInt($payload['gainNm'] ?? null),
        ];

        foreach ($normalized as $value) {
            if (null !== $value) {
                return $normalized;
            }
        }

        return null;
    }
}
