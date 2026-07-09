<?php

declare(strict_types=1);

namespace App\Portals\No1tuningfiles\Controller;

use App\Brand\Common\Context\BrandContextResolver;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\CreditPackage;
use App\Entity\Portal;
use App\Entity\Kunde;
use App\Portals\No1tuningfiles\Security\No1tuningfilesPortalCustomerUser;
use App\Portals\No1tuningfiles\Service\PaypalOrderService;
use App\Portals\No1tuningfiles\Service\PortalCreditTopupService;
use App\Portals\No1tuningfiles\Service\PortalCreditInvoiceService;
use App\Portals\No1tuningfiles\Service\StripePaymentService;
use App\Repository\CreditPackageRepository;
use App\Repository\KundeCreditTransactionRepository;
use App\Entity\KundeCreditTransaction;
use App\Service\CreditPackagePricingCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CreditsController extends AbstractController
{
    #[Route('/credits', name: 'no1_portal_credits', methods: ['GET'])]
    public function index(
        Request $request,
        CreditPackageRepository $creditPackageRepository,
        KundeCreditTransactionRepository $transactionRepository,
        CreditPackagePricingCalculator $pricingCalculator,
        PaypalOrderService $paypalOrderService,
        StripePaymentService $stripePaymentService,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        BrandContextResolver $brandContextResolver,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        $portalConfig = $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        $packages = array_values(array_filter(
            $creditPackageRepository->findBy([], ['credits' => 'ASC']),
            static fn (CreditPackage $package): bool => $package->getCredits() > 0 && (float) $package->getPrice() > 0
        ));

        $packageCards = array_map(
            fn (CreditPackage $package): array => [
                'entity' => $package,
                'pricing' => $pricingCalculator->calculate($package),
            ],
            $packages,
        );
        $transactions = $transactionRepository->findByKunde($customer, 0);
        $creditsPageBootstrap = $this->buildCreditsPageBootstrap(
            $packages,
            $transactions,
            $pricingCalculator,
            $paypalOrderService,
            $stripePaymentService,
            $urlGenerator,
            $csrfTokenManager,
            $portalConfig,
        );

        return $this->render('portal/no1tuningfiles/credits/index.html.twig', [
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
            'customer' => $customer,
            'reactBootstrap' => [
                'session' => [
                    'customerId' => $customer->getId(),
                    'credits' => (int) $customer->getCredits(),
                ],
                'portalDesign' => $portalConfig->getResolvedDesign(),
                'creditsPage' => $creditsPageBootstrap,
            ],
            'packageCards' => $packageCards,
            'transactions' => $transactions,
            'paypalConfigured' => $paypalOrderService->isConfigured(),
            'paypalClientId' => $paypalOrderService->getClientId(),
            'paypalCsrfToken' => $csrfTokenManager->getToken('no1_portal_credit_paypal')->getValue(),
        ]);
    }

    #[Route('/credits/bootstrap', name: 'no1_portal_credits_bootstrap', methods: ['GET'])]
    public function bootstrap(
        Request $request,
        CreditPackageRepository $creditPackageRepository,
        KundeCreditTransactionRepository $transactionRepository,
        CreditPackagePricingCalculator $pricingCalculator,
        PaypalOrderService $paypalOrderService,
        StripePaymentService $stripePaymentService,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): JsonResponse {
        $portalConfig = $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        $packages = array_values(array_filter(
            $creditPackageRepository->findBy([], ['credits' => 'ASC']),
            static fn (CreditPackage $package): bool => $package->getCredits() > 0 && (float) $package->getPrice() > 0
        ));
        $transactions = $transactionRepository->findByKunde($customer, 0);

        return $this->json($this->buildCreditsPageBootstrap(
            $packages,
            $transactions,
            $pricingCalculator,
            $paypalOrderService,
            $stripePaymentService,
            $urlGenerator,
            $csrfTokenManager,
            $portalConfig,
        ));
    }

    #[Route('/credits/checkout/{packageId}', name: 'no1_portal_credits_checkout', methods: ['GET'])]
    public function checkout(
        Request $request,
        int $packageId,
        CreditPackageRepository $creditPackageRepository,
        CreditPackagePricingCalculator $pricingCalculator,
        StripePaymentService $stripePaymentService,
        PaypalOrderService $paypalOrderService,
        CsrfTokenManagerInterface $csrfTokenManager,
        BrandContextResolver $brandContextResolver,
        PortalRuntimeResolver $portalRuntimeResolver,
        UrlGeneratorInterface $urlGenerator,
    ): Response {
        $portalConfig = $portalRuntimeResolver->requirePortalSystem($request);

        $package = $creditPackageRepository->find($packageId);
        if (!$package instanceof CreditPackage || $package->getCredits() <= 0 || (float) $package->getPrice() <= 0) {
            throw $this->createNotFoundException('Das gewählte Credit-Paket konnte nicht gefunden werden.');
        }

        $pricing = $pricingCalculator->calculate($package);

        return $this->render('portal/no1tuningfiles/credits/checkout.html.twig', [
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
            'package' => $package,
            'pricing' => $pricing,
            'stripeConfigured' => $stripePaymentService->isConfigured(),
            'stripeStartUrl' => $urlGenerator->generate('no1_portal_credits_stripe_start', ['packageId' => $packageId]),
            'paypal' => [
                'configured' => $paypalOrderService->isConfigured(),
                'clientId' => $paypalOrderService->getClientId(),
                'csrfToken' => $csrfTokenManager->getToken('no1_portal_credit_paypal')->getValue(),
            ],
            'prepaymentCsrf' => $csrfTokenManager->getToken('no1_portal_credit_prepayment')->getValue(),
        ]);
    }

    #[Route('/credits/stripe/start/{packageId}', name: 'no1_portal_credits_stripe_start', methods: ['GET'])]
    public function stripeStart(
        Request $request,
        int $packageId,
        CreditPackageRepository $creditPackageRepository,
        CreditPackagePricingCalculator $pricingCalculator,
        StripePaymentService $stripePaymentService,
        PortalRuntimeResolver $portalRuntimeResolver,
        UrlGeneratorInterface $urlGenerator,
    ): Response {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        $package = $creditPackageRepository->find($packageId);
        if (!$package instanceof CreditPackage || $package->getCredits() <= 0 || (float) $package->getPrice() <= 0) {
            throw $this->createNotFoundException('Das gewählte Credit-Paket konnte nicht gefunden werden.');
        }

        if (!$stripePaymentService->isConfigured()) {
            throw $this->createNotFoundException('Stripe ist aktuell nicht verfügbar.');
        }

        $pricing = $pricingCalculator->calculate($package);
        $session = $stripePaymentService->createCheckoutSession(
            $customer,
            $pricing['priceGross'],
            $package->getCredits(),
            $urlGenerator->generate('no1_portal_credits_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            $urlGenerator->generate('no1_portal_credits_checkout', ['packageId' => $packageId], UrlGeneratorInterface::ABSOLUTE_URL),
            [
                'packageId' => (string) $package->getId(),
                'customerId' => (string) $customer->getId(),
            ],
        );

        if (empty($session['url'])) {
            throw new \RuntimeException('Stripe Checkout konnte nicht gestartet werden.');
        }

        return $this->redirect($session['url']);
    }

    #[Route('/credits/stripe/success', name: 'no1_portal_credits_stripe_success', methods: ['GET'])]
    public function stripeSuccess(
        Request $request,
        CreditPackageRepository $creditPackageRepository,
        CreditPackagePricingCalculator $pricingCalculator,
        StripePaymentService $stripePaymentService,
        PortalCreditTopupService $portalCreditTopupService,
        BrandContextResolver $brandContextResolver,
        PortalRuntimeResolver $portalRuntimeResolver,
        LoggerInterface $logger,
    ): Response {
        $portalConfig = $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        if (!$stripePaymentService->isConfigured()) {
            throw $this->createNotFoundException('Stripe ist aktuell nicht verfügbar.');
        }

        $sessionId = trim((string) $request->query->get('session_id', ''));
        $transaction = null;
        $package = null;
        $pricing = null;
        $errorMessage = null;

        if ('' === $sessionId) {
            $errorMessage = 'Die Stripe-Zahlung konnte nicht verifiziert werden.';
        } else {
            try {
                $session = $stripePaymentService->retrieveCheckoutSession($sessionId);
                if (($session['payment_status'] ?? '') !== 'paid') {
                    throw new \RuntimeException('Die Zahlung wurde noch nicht abgeschlossen.');
                }

                $metadata = $session['metadata'] ?? [];
                $resolvedCustomerId = (int) ($metadata['customerId'] ?? 0);
                $resolvedPackageId = (int) ($metadata['packageId'] ?? 0);

                if ($resolvedCustomerId !== (int) ($customer->getId() ?? 0)) {
                    throw new \RuntimeException('Die Stripe-Zahlung gehört nicht zu deinem Konto.');
                }

                $package = $creditPackageRepository->find($resolvedPackageId);
                if (!$package instanceof CreditPackage || $package->getCredits() <= 0 || (float) $package->getPrice() <= 0) {
                    throw new \RuntimeException('Das gewählte Credit-Paket ist ungültig.');
                }

                $pricing = $pricingCalculator->calculate($package);
                $intent = ['id' => $session['payment_intent'] ?? $sessionId, 'metadata' => $metadata];
                $transaction = $portalCreditTopupService->bookCapturedStripePayment(
                    $customer,
                    $package,
                    $pricing,
                    $intent,
                );
            } catch (\Throwable $exception) {
                $logger->error('Stripe success handling failed.', [
                    'customerId' => $customer->getId(),
                    'sessionId' => $sessionId,
                    'message' => $exception->getMessage(),
                ]);
                $errorMessage = $exception->getMessage() ?: 'Die Stripe-Zahlung konnte nicht verbucht werden.';
            }
        }

        return $this->render('portal/no1tuningfiles/credits/stripe_success.html.twig', [
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
            'transaction' => $transaction,
            'package' => $package,
            'pricing' => $pricing,
            'success' => null !== $transaction,
            'errorMessage' => $errorMessage,
            'stripeIntentId' => $sessionId,
        ]);
    }

    #[Route('/credits/prepayment/invoice', name: 'no1_portal_credits_prepayment_invoice', methods: ['POST'])]
    public function createPrepaymentInvoice(
        Request $request,
        CreditPackageRepository $creditPackageRepository,
        PortalCreditInvoiceService $portalCreditInvoiceService,
        EntityManagerInterface $entityManager,
        PortalRuntimeResolver $portalRuntimeResolver,
        LoggerInterface $logger,
    ): JsonResponse {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomerForApi();
        if (!$customer instanceof Kunde) {
            return $customer;
        }

        $payload = $this->decodePayload($request);
        if (!$this->isCsrfTokenValid('no1_portal_credit_prepayment', (string) ($payload['_token'] ?? ''))) {
            return $this->json(['message' => 'Ungueltige Anfrage.'], Response::HTTP_BAD_REQUEST);
        }

        $requestedCredits = (int) ($payload['credits'] ?? 0);
        if ($requestedCredits < 10) {
            return $this->json(['message' => 'Mindestens 10 Credits erforderlich.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $price = $this->calculatePrepaymentPrice($creditPackageRepository, $requestedCredits);
        if ($price['net'] <= 0.0 || $price['gross'] <= 0.0) {
            return $this->json(['message' => 'Preis konnte nicht berechnet werden.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $invoice = $portalCreditInvoiceService->createInvoiceForPrepayment(
                $customer,
                $requestedCredits,
                $price['net'],
                $price['gross'],
                $price['taxRate'],
            );
        } catch (\Throwable $exception) {
            $logger->error('Prepayment invoice creation failed.', [
                'customerId' => $customer->getId(),
                'requestedCredits' => $requestedCredits,
                'message' => $exception->getMessage(),
            ]);

            return $this->json(['message' => 'Rechnung konnte nicht erstellt werden.'], Response::HTTP_BAD_REQUEST);
        }

        $invoiceId = trim((string) $invoice->getLexwareInvoiceId());
        $voucherNumber = trim((string) ($invoice->getVoucherNumber() ?? ''));
        $filePath = trim((string) ($invoice->getFilePath() ?? ''));
        $pdfUrl = '' !== $filePath ? (str_starts_with($filePath, 'http') ? $filePath : '/' . ltrim($filePath, '/')) : null;

        $descriptionLines = [
            sprintf('Vorkasse Anfrage: %d Credits', $requestedCredits),
            sprintf('Status: pending'),
            sprintf('Lexoffice Invoice: %s', $invoiceId),
        ];
        if ('' !== $voucherNumber) {
            $descriptionLines[] = sprintf('Lexoffice Voucher: %s', $voucherNumber);
        }
        if (null !== $pdfUrl) {
            $descriptionLines[] = sprintf('PDF: %s', $pdfUrl);
        }

        $balance = (int) $customer->getCredits();
        $transaction = (new KundeCreditTransaction())
            ->setKunde($customer)
            ->setAmount(0)
            ->setTransactionType(KundeCreditTransaction::TYPE_MANUAL_ADJUSTMENT)
            ->setDescription(implode(PHP_EOL, $descriptionLines))
            ->setPaymentMethod('vorkasse')
            ->setPriceNet(number_format($price['net'], 2, '.', ''))
            ->setPriceGross(number_format($price['gross'], 2, '.', ''))
            ->setTaxRate(number_format($price['taxRate'], 2, '.', ''))
            ->setPaymentFee(null)
            ->setBalanceBefore($balance)
            ->setBalanceAfter($balance);

        $entityManager->persist($transaction);
        $entityManager->flush();

        return $this->json([
            'message' => 'Vorkasse-Rechnung wurde erstellt.',
            'invoice' => [
                'id' => '' !== $voucherNumber ? $voucherNumber : $invoiceId,
                'downloadUrl' => $pdfUrl,
            ],
            'transactionId' => $transaction->getId(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/credits/paypal/order', name: 'no1_portal_credits_paypal_order', methods: ['POST'])]
    public function createPaypalOrder(
        Request $request,
        CreditPackageRepository $creditPackageRepository,
        CreditPackagePricingCalculator $pricingCalculator,
        PaypalOrderService $paypalOrderService,
        PortalRuntimeResolver $portalRuntimeResolver,
        LoggerInterface $logger,
    ): JsonResponse {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomerForApi();
        if (!$customer instanceof Kunde) {
            $logger->warning('PayPal order creation blocked: portal session missing.', [
                'path' => $request->getPathInfo(),
            ]);

            return $customer;
        }

        if (!$paypalOrderService->isConfigured()) {
            return $this->json(['message' => 'PayPal ist aktuell nicht konfiguriert.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $payload = $this->decodePayload($request);
        if (!$this->isCsrfTokenValid('no1_portal_credit_paypal', (string) ($payload['_token'] ?? ''))) {
            $logger->warning('PayPal order creation blocked: invalid CSRF token.', [
                'customerId' => $customer->getId(),
            ]);

            return $this->json(['message' => 'Ungueltige Anfrage.'], Response::HTTP_BAD_REQUEST);
        }

        $packageId = (int) ($payload['packageId'] ?? 0);
        $package = $creditPackageRepository->find($packageId);
        if (!$package instanceof CreditPackage || $package->getCredits() <= 0 || (float) $package->getPrice() <= 0) {
            return $this->json(['message' => 'Das ausgewaehlte Credit-Paket ist ungueltig.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $pricing = $pricingCalculator->calculate($package);
            $orderId = $paypalOrderService->createOrder($package, $customer, $pricing);
        } catch (\RuntimeException $exception) {
            $logger->error('PayPal order creation failed.', [
                'customerId' => $customer->getId(),
                'packageId' => $package->getId(),
                'message' => $exception->getMessage(),
            ]);

            return $this->json(['message' => $exception->getMessage()], Response::HTTP_BAD_GATEWAY);
        }

        return $this->json(['id' => $orderId]);
    }

    #[Route('/credits/paypal/capture', name: 'no1_portal_credits_paypal_capture', methods: ['POST'])]
    public function capturePaypalOrder(
        Request $request,
        PaypalOrderService $paypalOrderService,
        PortalCreditTopupService $portalCreditTopupService,
        PortalCreditInvoiceService $portalCreditInvoiceService,
        KundeCreditTransactionRepository $transactionRepository,
        PortalRuntimeResolver $portalRuntimeResolver,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
    ): JsonResponse {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomerForApi();
        if (!$customer instanceof Kunde) {
            $logger->warning('PayPal capture blocked: portal session missing.', [
                'path' => $request->getPathInfo(),
            ]);

            return $customer;
        }
        $payload = $this->decodePayload($request);

        if (!$this->isCsrfTokenValid('no1_portal_credit_paypal', (string) ($payload['_token'] ?? ''))) {
            $logger->warning('PayPal capture blocked: invalid CSRF token.', [
                'customerId' => $customer->getId(),
            ]);

            return $this->json(['message' => 'Ungueltige Anfrage.'], Response::HTTP_BAD_REQUEST);
        }

        $orderId = trim((string) ($payload['orderId'] ?? ''));
        if ('' === $orderId) {
            return $this->json(['message' => 'PayPal Order-ID fehlt.'], Response::HTTP_BAD_REQUEST);
        }

        if ($transactionRepository->hasTransactionWithPaypalOrderId($orderId)) {
            return $this->json([
                'message' => 'Die Zahlung wurde bereits verbucht.',
                'credits' => $customer->getCredits(),
            ]);
        }

        try {
            $captureData = $paypalOrderService->captureOrder($orderId);
            $result = $portalCreditTopupService->bookCapturedPaypalOrder($customer, $captureData);
        } catch (\RuntimeException $exception) {
            $logger->error('PayPal capture failed.', [
                'customerId' => $customer->getId(),
                'orderId' => $orderId,
                'message' => $exception->getMessage(),
            ]);

            return $this->json(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $invoiceMeta = null;
        try {
            $invoice = $portalCreditInvoiceService->createInvoiceForCreditTopup(
                $customer,
                $result['package'],
                $result['pricing'],
            );

            $transaction = $result['transaction'];
            $description = trim((string) $transaction->getDescription());
            $descriptionParts = [];
            if ('' !== $description) {
                $descriptionParts[] = $description;
            }
            $descriptionParts[] = 'Lexoffice Invoice: ' . $invoice->getLexwareInvoiceId();
            if (null !== $invoice->getVoucherNumber() && '' !== trim((string) $invoice->getVoucherNumber())) {
                $descriptionParts[] = 'Lexoffice Voucher: ' . trim((string) $invoice->getVoucherNumber());
            }
            $transaction->setDescription(implode(PHP_EOL, $descriptionParts));
            $entityManager->flush();

            $invoiceMeta = [
                'id' => $invoice->getVoucherNumber() ?: $invoice->getLexwareInvoiceId(),
                'status' => $invoice->getStatus(),
            ];
        } catch (\Throwable $exception) {
            $logger->error('Lexoffice invoice creation failed after PayPal capture.', [
                'customerId' => $customer->getId(),
                'orderId' => $orderId,
                'message' => $exception->getMessage(),
            ]);
        }

        return $this->json([
            'message' => sprintf(
                '%d Credits wurden erfolgreich aufgeladen.',
                $result['transaction']->getAmount(),
            ),
            'credits' => $result['transaction']->getBalanceAfter(),
            'transactionId' => $result['transaction']->getId(),
            'transaction' => $this->mapTransactionForReact($result['transaction']),
            'invoice' => $invoiceMeta,
        ]);
    }

    private function getCurrentCustomer(): Kunde
    {
        $user = $this->getUser();
        if (!$user instanceof No1tuningfilesPortalCustomerUser) {
            throw $this->createAccessDeniedException();
        }

        return $user->getCustomer();
    }

    private function getCurrentCustomerForApi(): Kunde|JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof No1tuningfilesPortalCustomerUser) {
            return $this->json([
                'message' => 'Deine Portal-Sitzung ist abgelaufen. Bitte logge dich erneut ein.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $user->getCustomer();
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(Request $request): array
    {
        $content = trim($request->getContent());
        if ('' === $content) {
            return $request->request->all();
        }

        $payload = json_decode($content, true);

        return is_array($payload) ? $payload : [];
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
    private function mapPackageForReact(CreditPackage $package, array $pricing): array
    {
        return [
            'id' => $package->getId(),
            'title' => (string) ($package->getTitle() ?? (string) $package),
            'credits' => $package->getCredits(),
            'priceNet' => $pricing['priceNet'],
            'priceGross' => $pricing['priceGross'],
            'taxRate' => $pricing['taxRate'],
            'pricePerFile' => (float) $package->getPricePerFile(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTransactionForReact(KundeCreditTransaction $transaction): array
    {
        return [
            'id' => $transaction->getId(),
            'amount' => $transaction->getAmount(),
            'transactionType' => $transaction->getTransactionType(),
            'description' => $transaction->getDescription(),
            'paymentMethod' => $transaction->getPaymentMethod(),
            'priceNet' => $this->toNullableFloat($transaction->getPriceNet()),
            'priceGross' => $this->toNullableFloat($transaction->getPriceGross()),
            'taxRate' => $this->toNullableFloat($transaction->getTaxRate()),
            'paymentFee' => $this->toNullableFloat($transaction->getPaymentFee()),
            'balanceBefore' => $transaction->getBalanceBefore(),
            'balanceAfter' => $transaction->getBalanceAfter(),
            'createdAt' => $transaction->getCreatedAt()?->format(\DATE_ATOM),
        ];
    }

    private function toNullableFloat(?string $value): ?float
    {
        if (null === $value) {
            return null;
        }

        $normalized = trim($value);
        if ('' === $normalized || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    /**
     * @param list<CreditPackage> $packages
     * @param list<KundeCreditTransaction> $transactions
     *
     * @return array<string, mixed>
     */
    private function buildCreditsPageBootstrap(
        array $packages,
        array $transactions,
        CreditPackagePricingCalculator $pricingCalculator,
        PaypalOrderService $paypalOrderService,
        StripePaymentService $stripePaymentService,
        UrlGeneratorInterface $urlGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        Portal $portalConfig,
    ): array {
        $enabled = $this->resolveEnabledPaymentMethods($portalConfig);

        return [
            'packages' => array_map(
                fn (CreditPackage $package): array => $this->mapPackageForReact(
                    $package,
                    $pricingCalculator->calculate($package),
                ),
                $packages
            ),
            'transactions' => array_map(
                fn (KundeCreditTransaction $transaction): array => $this->mapTransactionForReact($transaction),
                $transactions
            ),
            'enabledPaymentMethods' => $enabled,
            'paypal' => [
                'configured' => $enabled['paypal'] && $paypalOrderService->isConfigured(),
                'clientId' => $paypalOrderService->getClientId(),
                'csrfToken' => $csrfTokenManager->getToken('no1_portal_credit_paypal')->getValue(),
            ],
            'prepayment' => [
                'enabled' => $enabled['prepayment'],
                'csrfToken' => $csrfTokenManager->getToken('no1_portal_credit_prepayment')->getValue(),
            ],
            'stripe' => [
                'configured' => $enabled['stripe'] && $stripePaymentService->isConfigured(),
                'checkoutUrlTemplate' => $urlGenerator->generate(
                    'no1_portal_credits_checkout',
                    ['packageId' => '__PACKAGE_ID__'],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            ],
        ];
    }

    /**
     * @return array{paypal: bool, stripe: bool, prepayment: bool}
     */
    private function resolveEnabledPaymentMethods(Portal $portalConfig): array
    {
        $zahlungsarten = $portalConfig->getZahlungsarten();

        if ($zahlungsarten->isEmpty()) {
            return ['paypal' => true, 'stripe' => true, 'prepayment' => true];
        }

        $names = array_map(
            static fn ($z) => strtolower(trim((string) $z->getName())),
            $zahlungsarten->toArray()
        );

        return [
            'paypal'     => in_array('paypal', $names, true),
            'stripe'     => in_array('stripe', $names, true),
            'prepayment' => true,
        ];
    }

    /**
     * @return array{net:float,gross:float,taxRate:float}
     */
    private function calculatePrepaymentPrice(CreditPackageRepository $creditPackageRepository, int $requestedCredits): array
    {
        $packages = array_values(array_filter(
            $creditPackageRepository->findBy([], ['credits' => 'ASC']),
            static fn (CreditPackage $package): bool => $package->getCredits() > 0 && (float) $package->getPrice() > 0
        ));

        if ([] === $packages) {
            return ['net' => 0.0, 'gross' => 0.0, 'taxRate' => 19.0];
        }

        usort($packages, static fn (CreditPackage $a, CreditPackage $b): int => $b->getCredits() <=> $a->getCredits());
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

        return ['net' => $net, 'gross' => $gross, 'taxRate' => 19.0];
    }
}
