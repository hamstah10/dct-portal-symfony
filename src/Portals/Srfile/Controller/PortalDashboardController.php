<?php

namespace App\Portals\Srfile\Controller;

use App\Brand\Common\Context\BrandContextResolver;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Auftrag;
use App\Entity\Kunde;
use App\Entity\LexwareInvoice;
use App\Entity\Portal;
use App\Entity\SupportTicket;
use App\Portals\Srfile\Security\SrfilePortalCustomerUser;
use App\Portals\Chiptuningfile\Service\PortalDashboardBuilder;
use App\Portals\Chiptuningfile\Service\PortalInvoiceStatusService;
use App\Repository\AuftragRepository;
use App\Repository\LexwareInvoiceRepository;
use App\Repository\SupportTicketRepository;
use App\Repository\VehicleBrandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class PortalDashboardController extends AbstractController
{
    public function __construct(
        private readonly AuftragRepository $auftragRepository,
        private readonly SupportTicketRepository $supportTicketRepository,
        private readonly VehicleBrandRepository $vehicleBrandRepository,
        private readonly LexwareInvoiceRepository $lexwareInvoiceRepository,
        private readonly PortalInvoiceStatusService $portalInvoiceStatusService,
    ) {
    }

    #[Route('/', name: 'srf_portal_dashboard', methods: ['GET'])]
    public function index(
        PortalDashboardBuilder $dashboardBuilder,
        BrandContextResolver $brandContextResolver,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response
    {
        $customer = $this->getCurrentCustomer();
        $portalConfig = $portalRuntimeResolver->requirePortalModule(Portal::MODULE_DASHBOARD);
        $dashboard = $dashboardBuilder->build($customer);

        return $this->render('portal/srfile/dashboard/index.html.twig', [
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
            'dashboard' => $dashboard,
            'reactBootstrap' => [
                'session' => [
                    'customerId' => $customer->getId(),
                    'credits' => (int) $customer->getCredits(),
                ],
                'portalDesign' => $portalConfig->getResolvedDesign(),
                'dashboard' => $this->buildReactBootstrap($customer, $dashboard),
                'orders' => [
                    'items' => $this->buildOrdersBootstrap($customer),
                    'customerId' => $customer->getId(),
                    'parkedOrders' => $dashboard['parkedOrders'] ?? [],
                ],
                'invoices' => [
                    'items' => $this->buildInvoicesBootstrap($customer, $portalConfig),
                ],
            ],
        ]);
    }

    #[Route('/orders/{id}/download-original', name: 'srf_portal_dashboard_order_download_original', methods: ['GET'])]
    public function downloadOriginalOrderFile(
        string $id,
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        $portalRuntimeResolver->requirePortalSystem($request);

        $customer = $this->getCurrentCustomer();
        $order = $this->resolveCustomerOrderByIdentifier($customer, $id);
        if (!$order instanceof Auftrag) {
            throw $this->createNotFoundException('Auftrag nicht gefunden.');
        }

        $raw = trim((string) $order->getFileData());
        if ('' === $raw) {
            throw $this->createNotFoundException('Datei nicht gefunden.');
        }

        $mimeType = 'application/octet-stream';
        $payload = $raw;

        if (str_starts_with($raw, 'data:')) {
            $parts = explode(',', $raw, 2);
            if (2 === count($parts)) {
                $meta = $parts[0];
                $payload = $parts[1];
                if (preg_match('/^data:([^;]+);base64/i', $meta, $matches) === 1) {
                    $mimeType = $matches[1];
                }
            }
        }

        $decoded = base64_decode($payload, true);
        if (false === $decoded) {
            throw $this->createNotFoundException('Datei konnte nicht gelesen werden.');
        }

        $fileName = trim((string) $order->getFileName());
        if ('' === $fileName) {
            $fileName = 'original.bin';
        }

        $response = new Response($decoded);
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Disposition', (new ResponseHeaderBag())->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        ));
        $response->headers->set('Content-Length', (string) strlen($decoded));

        return $response;
    }

    #[Route('/orders/{id}/download-modified', name: 'srf_portal_dashboard_order_download_modified', methods: ['GET'])]
    public function downloadModifiedOrderFile(
        string $id,
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        $portalRuntimeResolver->requirePortalSystem($request);

        $customer = $this->getCurrentCustomer();
        $order = $this->resolveCustomerOrderByIdentifier($customer, $id);
        if (!$order instanceof Auftrag) {
            throw $this->createNotFoundException('Auftrag nicht gefunden.');
        }

        $relativePath = ltrim(trim((string) $order->getFinishedFilePath()), '/');
        if ('' === $relativePath || str_contains($relativePath, '..')) {
            throw $this->createNotFoundException('Datei nicht gefunden.');
        }

        $absolutePath = rtrim((string) $this->getParameter('kernel.project_dir'), '/') . '/public/' . $relativePath;
        if (!is_file($absolutePath)) {
            throw $this->createNotFoundException('Datei nicht gefunden.');
        }

        $fileName = trim((string) $order->getFinishedFileName());
        if ('' === $fileName) {
            $fileName = basename($absolutePath);
        }

        return $this->file($absolutePath, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    private function getCurrentCustomer(): Kunde
    {
        $user = $this->getUser();
        if (!$user instanceof SrfilePortalCustomerUser) {
            throw $this->createAccessDeniedException();
        }

        return $user->getCustomer();
    }

    /**
     * @param array<string, mixed> $dashboard
     * @return array<string, mixed>
     */
    private function buildReactBootstrap(Kunde $customer, array $dashboard): array
    {
        $recentJobs = $this->buildRecentOrdersBootstrap($customer);
        $currentJob = $this->resolveCurrentJob($recentJobs);
        $tickets = $this->buildTicketBootstrap($customer);
        $openJobs = $this->countOrdersByStatuses($customer, [Auftrag::STATUS_RECEIVED, Auftrag::STATUS_IN_PROGRESS, Auftrag::STATUS_PARKED]);
        $completedJobs = $this->countOrdersByStatuses($customer, [Auftrag::STATUS_COMPLETED]);
        $jobsThisMonth = $this->countOrdersSince($customer, new \DateTimeImmutable('first day of this month 00:00:00'));

        return [
            'customerName' => $this->resolveCustomerDisplayName($customer),
            'credits' => (int) $customer->getCredits(),
            'openJobs' => $openJobs,
            'completedJobs' => $completedJobs,
            'jobsThisMonth' => $jobsThisMonth,
            'currentJob' => $currentJob,
            'tickets' => $tickets,
            'recentJobs' => $recentJobs,
            'parkedOrders' => $dashboard['parkedOrders'] ?? [],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildInvoicesBootstrap(Kunde $customer, Portal $portal): array
    {
        /** @var list<LexwareInvoice> $invoices */
        $invoices = $this->lexwareInvoiceRepository->createQueryBuilder('i')
            ->andWhere('i.kunde = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('i.createdAt', 'DESC')
            ->addOrderBy('i.id', 'DESC')
            ->setMaxResults(250)
            ->getQuery()
            ->getResult();

        $issuer = $this->buildInvoiceIssuerPayload($portal);
        $customerPayload = $this->buildInvoiceCustomerPayload($customer);
        $invoiceViews = $this->portalInvoiceStatusService->buildInvoiceViews($invoices);

        return array_map(function (array $invoiceView) use ($issuer, $customerPayload): array {
            /** @var LexwareInvoice $invoice */
            $invoice = $invoiceView['invoice'];

            return [
                'id' => (string) ($invoice->getVoucherNumber() ?: $invoice->getLexwareInvoiceId()),
                'date' => $invoice->getCreatedAt()?->format(\DATE_ATOM),
                'status' => (string) $invoiceView['status'],
                'credits' => (int) $invoiceView['credits'],
                'amount' => (float) $invoiceView['amount'],
                'tax' => (float) $invoiceView['tax'],
                'total' => (float) $invoiceView['total'],
                'paymentMethod' => (string) $invoiceView['paymentMethod'],
                'downloadUrl' => $invoiceView['downloadUrl'],
                'issuer' => $issuer,
                'customer' => $customerPayload,
            ];
        }, $invoiceViews);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildOrdersBootstrap(Kunde $customer): array
    {
        $customerEmail = mb_strtolower(trim((string) $customer->getEmail()));

        /** @var list<Auftrag> $orders */
        $queryBuilder = $this->auftragRepository->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setMaxResults(250);

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

        $orders = $queryBuilder->getQuery()->getResult();

        return array_map(function (Auftrag $order): array {
            $vehicleData = $order->getVehicleData() ?? [];
            $selectedOptions = $vehicleData['selectedOptions'] ?? [];
            $credits = $this->resolveOrderCredits($vehicleData);
            $performance = $this->resolveOrderPerformance($vehicleData);
            $pricing = $this->resolveOrderPricing($order, $vehicleData, $credits);

            return [
                'id' => $order->getOrderNumber(),
                'createdAt' => $order->getCreatedAt()?->format(\DATE_ATOM),
                'updatedAt' => $order->getCreatedAt()?->format(\DATE_ATOM),
                'vehicle' => [
                    'manufacturer' => (string) ($order->getManufacturer() ?? ''),
                    'model' => (string) ($order->getModel() ?? ''),
                    'series' => (string) ($order->getVehicleSeries() ?? ''),
                    'year' => null,
                    'engine' => (string) ($order->getEngine() ?? ''),
                    'power' => '',
                    'ecu' => (string) ($order->getEcu() ?? ''),
                ],
                'tuning' => [
                    'type' => (string) ($order->getStageLabel() ?? ''),
                    'options' => is_array($selectedOptions) ? array_values(array_filter(array_map(
                        static fn (mixed $item): string => is_string($item) ? trim($item) : '',
                        $selectedOptions
                    ), static fn (string $item): bool => '' !== $item)) : [],
                    'performance' => $performance,
                    'pricing' => $pricing,
                ],
                'status' => $this->mapStatusForReact($order->getStatus()),
                'credits' => $credits,
                'files' => [
                    'original' => (string) ($order->getFileName() ?? ''),
                    'originalSize' => $order->getFileSize(),
                    'modified' => (string) ($order->getFinishedFileName() ?? ''),
                    'modifiedSize' => $order->getFinishedFileSize(),
                ],
                'notes' => (string) ($order->getOrderComment() ?? ''),
            ];
        }, $orders);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInvoiceIssuerPayload(Portal $portal): array
    {
        $firma = $portal->getFirma();

        return [
            'name' => trim((string) ($firma?->getName() ?? '')),
            'email' => trim((string) ($firma?->getEmail() ?? '')),
            'phone' => trim((string) ($firma?->getTelefon() ?? '')),
            'website' => trim((string) ($firma?->getWebsite() ?? '')),
            'vatId' => trim((string) ($firma?->getUstId() ?? '')),
            'taxNumber' => trim((string) ($firma?->getSteuernummer() ?? '')),
            'addressLine1' => trim(sprintf(
                '%s %s',
                (string) ($firma?->getStrasse() ?? ''),
                (string) ($firma?->getHausnummer() ?? '')
            )),
            'postalCode' => trim((string) ($firma?->getPlz() ?? '')),
            'city' => trim((string) ($firma?->getStadt() ?? '')),
            'country' => trim((string) ($firma?->getLand() ?? '')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInvoiceCustomerPayload(Kunde $customer): array
    {
        return [
            'id' => $customer->getId(),
            'name' => $this->resolveCustomerDisplayName($customer),
            'company' => trim((string) ($customer->getFirmenname() ?? '')),
            'email' => trim((string) ($customer->getEmail() ?? '')),
            'phone' => trim((string) ($customer->getTelefon() ?? '')),
            'city' => trim((string) ($customer->getStadt() ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $vehicleData
     * @return array{originalPs:int|null,originalNm:int|null,tunedPs:int|null,tunedNm:int|null,gainPs:int|null,gainNm:int|null}
     */
    private function resolveOrderPerformance(array $vehicleData): array
    {
        $stagePerformance = $vehicleData['stagePerformance'] ?? null;
        $source = is_array($stagePerformance) ? $stagePerformance : $vehicleData;

        return [
            'originalPs' => $this->readNullableInt($source['originalPs'] ?? $vehicleData['stageOriginalPs'] ?? null),
            'originalNm' => $this->readNullableInt($source['originalNm'] ?? $vehicleData['stageOriginalNm'] ?? null),
            'tunedPs' => $this->readNullableInt($source['tunedPs'] ?? $vehicleData['stageTunedPs'] ?? null),
            'tunedNm' => $this->readNullableInt($source['tunedNm'] ?? $vehicleData['stageTunedNm'] ?? null),
            'gainPs' => $this->readNullableInt($source['gainPs'] ?? $vehicleData['stageGainPs'] ?? null),
            'gainNm' => $this->readNullableInt($source['gainNm'] ?? $vehicleData['stageGainNm'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $vehicleData
     * @return array{stageCredits:int|null,optionCredits:int|null,totalCredits:int,stagePriceEuro:float|null,optionPriceEuro:float|null,totalPriceEuro:float|null}
     */
    private function resolveOrderPricing(Auftrag $order, array $vehicleData, int $totalCredits): array
    {
        $stageCredits = $this->readNullableInt($vehicleData['stageCredits'] ?? $vehicleData['stagePriceCredits'] ?? null);
        $optionCredits = $this->readNullableInt($vehicleData['optionCredits'] ?? $vehicleData['optionsCredits'] ?? $vehicleData['optionPriceCredits'] ?? null);
        $stageLabel = strtolower(trim((string) ($order->getStageLabel() ?? '')));
        $isStageOrder = '' !== $stageLabel && (str_contains($stageLabel, 'stage') || str_contains($stageLabel, 'eco'));

        if (null === $stageCredits && $isStageOrder && $totalCredits > 0) {
            $stageCredits = $totalCredits;
        }

        if (null !== $stageCredits && null === $optionCredits && $totalCredits >= $stageCredits) {
            $optionCredits = max(0, $totalCredits - $stageCredits);
        }

        return [
            'stageCredits' => $stageCredits,
            'optionCredits' => $optionCredits,
            'totalCredits' => $totalCredits,
            'stagePriceEuro' => $this->readNullableFloat($vehicleData['stagePriceEuro'] ?? $vehicleData['stagePrice'] ?? null),
            'optionPriceEuro' => $this->readNullableFloat($vehicleData['optionPriceEuro'] ?? $vehicleData['optionsPriceEuro'] ?? $vehicleData['optionPrice'] ?? null),
            'totalPriceEuro' => $this->readNullableFloat($vehicleData['totalPriceEuro'] ?? $vehicleData['priceTotalEuro'] ?? null),
        ];
    }

    private function readNullableInt(mixed $value): ?int
    {
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
    }

    private function readNullableFloat(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function resolveCustomerDisplayName(Kunde $customer): string
    {
        $fullName = trim(sprintf('%s %s', $customer->getVorname() ?? '', $customer->getNachname() ?? ''));
        if ($fullName !== '') {
            return $fullName;
        }

        if (($customer->getFirmenname() ?? '') !== '') {
            return (string) $customer->getFirmenname();
        }

        return 'Portal Kunde';
    }

    private function mapStatusForReact(string $status): string
    {
        return match ($status) {
            Auftrag::STATUS_IN_PROGRESS => 'processing',
            Auftrag::STATUS_PARKED => 'parked',
            Auftrag::STATUS_COMPLETED => 'completed',
            Auftrag::STATUS_REJECTED => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * @param list<array<string, mixed>> $recentJobs
     *
     * @return array<string, mixed>|null
     */
    private function resolveCurrentJob(array $recentJobs): ?array
    {
        foreach ($recentJobs as $job) {
            $status = (string) ($job['status'] ?? 'pending');
            if (\in_array($status, ['pending', 'processing'], true)) {
                return $this->normalizeCurrentJob($job);
            }
        }

        if ([] === $recentJobs) {
            return null;
        }

        return $this->normalizeCurrentJob($recentJobs[0]);
    }

    /**
     * @param array<string, mixed> $job
     *
     * @return array<string, mixed>
     */
    private function normalizeCurrentJob(array $job): array
    {
        return [
            'id' => (string) ($job['id'] ?? ''),
            'filename' => (string) ($job['filename'] ?? ''),
            'vehicleLabel' => (string) ($job['vehicleLabel'] ?? ''),
            'status' => (string) ($job['status'] ?? 'pending'),
            'statusLabel' => (string) ($job['statusLabel'] ?? ''),
            'timelineLabel' => (string) ($job['timelineLabel'] ?? ''),
            'brandLogoUrl' => (string) ($job['brandLogoUrl'] ?? ''),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildRecentOrdersBootstrap(Kunde $customer): array
    {
        $customerEmail = mb_strtolower(trim((string) $customer->getEmail()));

        /** @var list<Auftrag> $orders */
        $queryBuilder = $this->auftragRepository->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setMaxResults(20);

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

        $orders = $queryBuilder->getQuery()->getResult();

        return array_map(function (Auftrag $order): array {
            $status = $order->getStatus();
            $createdAt = $order->getCreatedAt();
            $vehicleLabel = $this->buildVehicleLabel($order);
            $vehicleData = is_array($order->getVehicleData()) ? $order->getVehicleData() : [];
            $selectedOptions = $vehicleData['selectedOptions'] ?? [];
            $orderNumber = $order->getOrderNumber();

            return [
                'id' => $orderNumber,
                'orderNumber' => $orderNumber,
                'filename' => $vehicleLabel,
                'vehicleLabel' => $vehicleLabel,
                'brandLogoUrl' => $this->resolveBrandLogoUrl($order->getManufacturer()),
                'stageLabel' => trim((string) ($order->getStageLabel() ?? '')),
                'ecu' => trim((string) ($order->getEcu() ?? '')),
                'status' => $this->mapStatusForReact($status),
                'statusLabel' => $this->resolveOrderStatusLabel($status),
                'timelineLabel' => $createdAt instanceof \DateTimeImmutable ? $createdAt->format('d.m.Y H:i') : '',
                'createdAt' => $createdAt?->format('Y-m-d H:i'),
                'updatedAt' => $createdAt?->format('Y-m-d H:i'),
                'credits' => $this->resolveOrderCredits($vehicleData),
                'options' => is_array($selectedOptions) ? array_values(array_filter(array_map(
                    static fn (mixed $item): string => is_string($item) ? trim($item) : '',
                    $selectedOptions
                ), static fn (string $item): bool => '' !== $item)) : [],
                'files' => [
                    'originalName' => trim((string) ($order->getFileName() ?? '')),
                    'modifiedName' => trim((string) ($order->getFinishedFileName() ?? '')),
                    'originalDownloadUrl' => $this->generateUrl('srf_portal_dashboard_order_download_original', ['id' => $orderNumber]),
                    'modifiedDownloadUrl' => '' !== trim((string) ($order->getFinishedFilePath() ?? ''))
                        ? $this->generateUrl('srf_portal_dashboard_order_download_modified', ['id' => $orderNumber])
                        : null,
                ],
            ];
        }, $orders);
    }

    private function resolveBrandLogoUrl(?string $manufacturer): string
    {
        $name = trim((string) $manufacturer);
        if ('' === $name) {
            return '';
        }

        $brand = $this->vehicleBrandRepository->findOneBy(['name' => $name]);
        if (null === $brand) {
            return '';
        }

        return trim((string) ($brand->getImage() ?? ''));
    }

    private function resolveOrderCredits(array $vehicleData): int
    {
        foreach (['totalCredits', 'total_credits', 'credits', 'creditTotal'] as $key) {
            $value = $vehicleData[$key] ?? null;
            if (is_int($value)) {
                return max(0, $value);
            }

            if (is_float($value) || is_string($value)) {
                if (!is_numeric((string) $value)) {
                    continue;
                }

                return max(0, (int) round((float) $value));
            }
        }

        return 0;
    }

    /**
     * @param list<string> $statuses
     */
    private function countOrdersByStatuses(Kunde $customer, array $statuses): int
    {
        $customerEmail = mb_strtolower(trim((string) $customer->getEmail()));
        $queryBuilder = $this->auftragRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.status IN (:statuses)')
            ->setParameter('statuses', $statuses);

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

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    private function countOrdersSince(Kunde $customer, \DateTimeImmutable $since): int
    {
        $customerEmail = mb_strtolower(trim((string) $customer->getEmail()));
        $queryBuilder = $this->auftragRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.createdAt >= :since')
            ->setParameter('since', $since);

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

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    private function resolveOrderStatusLabel(string $status): string
    {
        return match ($status) {
            Auftrag::STATUS_IN_PROGRESS => 'In Bearbeitung',
            Auftrag::STATUS_PARKED => 'Geparkt',
            Auftrag::STATUS_COMPLETED => 'Abgeschlossen',
            Auftrag::STATUS_REJECTED => 'Abgelehnt',
            default => 'Eingegangen',
        };
    }

    private function buildVehicleLabel(Auftrag $order): string
    {
        $vehicleDisplay = trim((string) ($order->getVehicleDisplay() ?? ''));
        if ($vehicleDisplay !== '') {
            return $vehicleDisplay;
        }

        $vehicleData = $order->getVehicleData();
        if (is_array($vehicleData)) {
            $dataDisplay = trim((string) ($vehicleData['vehicleDisplay'] ?? ''));
            if ($dataDisplay !== '') {
                return $dataDisplay;
            }
        }

        $parts = array_values(array_filter([
            trim((string) ($order->getManufacturer() ?? '')),
            trim((string) ($order->getVehicleSeries() ?? '')),
            trim((string) ($order->getModel() ?? '')),
        ], static fn (string $value): bool => $value !== ''));

        if ($parts === [] && is_array($vehicleData)) {
            $parts = array_values(array_filter([
                trim((string) ($vehicleData['manufacturer'] ?? '')),
                trim((string) (($vehicleData['vehicleSeries'] ?? $vehicleData['series'] ?? ''))),
                trim((string) (($vehicleData['vehicleModel'] ?? $vehicleData['model'] ?? ''))),
            ], static fn (string $value): bool => $value !== ''));
        }

        if ($parts === []) {
            $engineOnly = trim((string) ($order->getEngine() ?? ''));
            if ($engineOnly !== '') {
                return $engineOnly;
            }

            if (is_array($vehicleData)) {
                $engineFromData = trim((string) ($vehicleData['engine'] ?? ''));
                if ($engineFromData !== '') {
                    return $engineFromData;
                }
            }

            return '';
        }

        $label = implode(' ', $parts);
        $engine = trim((string) ($order->getEngine() ?? ''));

        if ($engine !== '' && !str_contains(mb_strtolower($label), mb_strtolower($engine))) {
            $label .= ' · ' . $engine;
        }

        return $label;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildTicketBootstrap(Kunde $customer): array
    {
        $queryBuilder = $this->supportTicketRepository->createQueryBuilder('t')
            ->andWhere('t.status != :closed')
            ->andWhere('t.kunde = :customer')
            ->setParameter('closed', SupportTicket::STATUS_CLOSED)
            ->setParameter('customer', $customer)
            ->orderBy('t.updatedAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setMaxResults(3);

        /** @var list<SupportTicket> $tickets */
        $tickets = $queryBuilder->getQuery()->getResult();

        return array_map(function (SupportTicket $ticket): array {
            return [
                'id' => $ticket->getTicketNumber(),
                'subject' => $ticket->getSubject(),
                'status' => $ticket->getStatus() === SupportTicket::STATUS_OPEN ? 'open' : 'answered',
                'priority' => $ticket->getPriority(),
                'updatedAt' => $ticket->getUpdatedAt()?->format('Y-m-d H:i'),
            ];
        }, $tickets);
    }

    private function resolveCustomerOrderByIdentifier(Kunde $customer, string $identifier): ?Auftrag
    {
        $normalized = trim($identifier);
        if ('' === $normalized) {
            return null;
        }

        $id = 0;
        if (preg_match('/^A-(\d+)$/i', $normalized, $matches) === 1) {
            $id = (int) $matches[1];
        } elseif (ctype_digit($normalized)) {
            $id = (int) $normalized;
        }

        if ($id <= 0) {
            return null;
        }

        $customerEmail = mb_strtolower(trim((string) $customer->getEmail()));
        $queryBuilder = $this->auftragRepository->createQueryBuilder('a')
            ->andWhere('a.id = :id')
            ->setParameter('id', $id)
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

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
