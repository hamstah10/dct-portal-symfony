<?php

declare(strict_types=1);

namespace App\Portals\Srfile\Controller;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Auftrag;
use App\Entity\Kunde;
use App\Entity\LexwareInvoice;
use App\Entity\Portal;
use App\Entity\PortalPriceList;
use App\Entity\PortalPriceListItem;
use App\Entity\SupportTicket;
use App\Entity\SupportTicketAttachment;
use App\Entity\SupportTicketMessage;
use App\Portals\Chiptuningfile\Service\PortalDashboardBuilder;
use App\Portals\Chiptuningfile\Service\PortalInvoiceStatusService;
use App\Repository\DtcCodeRepository;
use App\Service\SupportTicketFileService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LexwareInvoiceRepository;
use App\Repository\PortalPriceListRepository;
use App\Repository\ProfitCalculatorOptionRepository;
use App\Portals\Srfile\Security\SrfilePortalCustomerUser;
use App\Repository\AuftragRepository;
use App\Repository\SupportTicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReactNavigationController extends AbstractController
{
    public function __construct(
        private readonly AuftragRepository $auftragRepository,
        private readonly SupportTicketRepository $supportTicketRepository,
        private readonly LexwareInvoiceRepository $lexwareInvoiceRepository,
        private readonly PortalPriceListRepository $portalPriceListRepository,
        private readonly ProfitCalculatorOptionRepository $profitCalculatorOptionRepository,
        private readonly DtcCodeRepository $dtcCodeRepository,
        private readonly PortalInvoiceStatusService $portalInvoiceStatusService,
        private readonly SupportTicketFileService $supportTicketFileService,
        private readonly PortalDashboardBuilder $portalDashboardBuilder,
    ) {
    }

    #[Route('/orders', name: 'srf_portal_orders', methods: ['GET'])]
    public function orders(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        $portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);

        $customer = $this->getCurrentCustomer();
        $orders = null !== $customer ? $this->buildOrdersBootstrap($customer) : [];

        return $this->renderHost($request, $portalRuntimeResolver, 'Orders | Siemoneit Racing', [
            'orders' => [
                'items' => $orders,
                'customerId' => $customer?->getId(),
                'parkedOrders' => $this->portalDashboardBuilder->buildParkedOrders($customer, 10),
            ],
        ]);
    }

    #[Route('/orders/{id}', name: 'srf_portal_order_detail', methods: ['GET'])]
    public function orderDetail(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        $portalRuntimeResolver->requirePortalModule(Portal::MODULE_ORDER, $request);

        $customer = $this->getCurrentCustomer();
        $orders = null !== $customer ? $this->buildOrdersBootstrap($customer) : [];

        return $this->renderHost($request, $portalRuntimeResolver, 'Order Detail | Siemoneit Racing', [
            'orders' => [
                'items' => $orders,
                'customerId' => $customer?->getId(),
                'parkedOrders' => $this->portalDashboardBuilder->buildParkedOrders($customer, 10),
            ],
        ]);
    }

    #[Route('/price-list', name: 'srf_portal_price_list', methods: ['GET'])]
    public function priceList(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        return $this->renderHost($request, $portalRuntimeResolver, 'Price List | Siemoneit Racing', [
            'priceList' => $this->buildPriceListBootstrap(),
        ]);
    }

    #[Route('/invoices', name: 'srf_portal_invoices', methods: ['GET'])]
    public function invoices(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();
        $invoices = null !== $customer ? $this->buildInvoicesBootstrap($customer, $portal) : [];

        return $this->renderHost($request, $portalRuntimeResolver, 'Invoices | Siemoneit Racing', [
            'invoices' => [
                'items' => $invoices,
            ],
        ]);
    }

    #[Route('/invoices/{id}', name: 'srf_portal_invoice_detail', methods: ['GET'])]
    public function invoiceDetail(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();
        $invoices = null !== $customer ? $this->buildInvoicesBootstrap($customer, $portal) : [];

        return $this->renderHost($request, $portalRuntimeResolver, 'Invoice Detail | Siemoneit Racing', [
            'invoices' => [
                'items' => $invoices,
            ],
        ]);
    }

    #[Route('/tickets', name: 'srf_portal_tickets', methods: ['GET'])]
    public function tickets(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();
        $tickets = null !== $customer ? $this->buildTicketsBootstrap($customer) : [];

        return $this->renderHost($request, $portalRuntimeResolver, 'Tickets | Siemoneit Racing', [
            'tickets' => [
                'items' => $tickets,
            ],
        ]);
    }

    #[Route('/tickets/{id}', name: 'srf_portal_ticket_detail', requirements: ['id' => 'T-\d+|\d+'], methods: ['GET'])]
    public function ticketDetail(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();
        $tickets = null !== $customer ? $this->buildTicketsBootstrap($customer) : [];

        return $this->renderHost($request, $portalRuntimeResolver, 'Ticket Detail | Siemoneit Racing', [
            'tickets' => [
                'items' => $tickets,
            ],
        ]);
    }

    #[Route('/tickets/api', name: 'srf_portal_tickets_api', methods: ['GET', 'POST'])]
    public function ticketsApi(
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        if (!$customer instanceof Kunde) {
            return $this->json(['message' => 'Nicht autorisiert.'], Response::HTTP_FORBIDDEN);
        }

        if ($request->isMethod('GET')) {
            return $this->json([
                'items' => $this->buildTicketsBootstrap($customer),
            ]);
        }

        $payload = $this->readJsonPayload($request);
        $subject = trim((string) ($payload['subject'] ?? ''));
        $messageText = trim((string) ($payload['message'] ?? ''));
        $priority = $this->normalizeTicketPriority((string) ($payload['priority'] ?? SupportTicket::PRIORITY_NORMAL));
        $relatedOrder = $this->normalizeRelatedOrderIdentifier((string) ($payload['relatedOrder'] ?? ''));

        if ('' !== $relatedOrder && !preg_match('/^\[' . preg_quote($relatedOrder, '/') . '\]\s*/i', $subject)) {
            $subject = sprintf('[%s] %s', $relatedOrder, $subject);
        }

        if ('' === $subject) {
            return $this->json(['message' => 'Betreff fehlt.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ('' === $messageText) {
            return $this->json(['message' => 'Nachricht fehlt.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (null === $customer->getFirma()) {
            return $this->json(['message' => 'Kundenfirma fehlt.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $ticket = (new SupportTicket())
            ->setFirma($customer->getFirma())
            ->setKunde($customer)
            ->setSubject($subject)
            ->setPriority($priority)
            ->setStatus(SupportTicket::STATUS_OPEN);

        $message = (new SupportTicketMessage())
            ->setTicket($ticket)
            ->setKunde($customer)
            ->setSenderType(SupportTicketMessage::SENDER_CUSTOMER)
            ->setSenderName($this->resolveCustomerDisplayName($customer))
            ->setMessage($messageText);

        $ticket->addMessage($message);
        $ticket->registerNewMessage($messageText, $message->getCreatedAt() ?? new \DateTimeImmutable());

        $entityManager->persist($ticket);
        $entityManager->flush();

        return $this->json([
            'ticket' => $this->buildTicketArray($ticket),
        ], Response::HTTP_CREATED);
    }

    #[Route('/tickets/api/{id}', name: 'srf_portal_ticket_api_detail', methods: ['GET'])]
    public function ticketApiDetail(
        string $id,
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): JsonResponse {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        if (!$customer instanceof Kunde) {
            return $this->json(['message' => 'Nicht autorisiert.'], Response::HTTP_FORBIDDEN);
        }

        $ticket = $this->resolveCustomerTicketByIdentifier($customer, $id);
        if (!$ticket instanceof SupportTicket) {
            return $this->json(['message' => 'Ticket nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'ticket' => $this->buildTicketArray($ticket),
        ]);
    }

    #[Route('/tickets/api/{id}/reply', name: 'srf_portal_ticket_api_reply', methods: ['POST'])]
    public function ticketApiReply(
        string $id,
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        if (!$customer instanceof Kunde) {
            return $this->json(['message' => 'Nicht autorisiert.'], Response::HTTP_FORBIDDEN);
        }

        $ticket = $this->resolveCustomerTicketByIdentifier($customer, $id);
        if (!$ticket instanceof SupportTicket) {
            return $this->json(['message' => 'Ticket nicht gefunden.'], Response::HTTP_NOT_FOUND);
        }

        if ($ticket->getStatus() === SupportTicket::STATUS_CLOSED) {
            return $this->json(['message' => 'Ticket ist geschlossen.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $messageText = trim((string) ($request->request->get('message', '')));
        if ('' === $messageText) {
            $payload = $this->readJsonPayload($request);
            $messageText = trim((string) ($payload['message'] ?? ''));
        }
        if ('' === $messageText) {
            return $this->json(['message' => 'Nachricht fehlt.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $message = (new SupportTicketMessage())
            ->setTicket($ticket)
            ->setKunde($customer)
            ->setSenderType(SupportTicketMessage::SENDER_CUSTOMER)
            ->setSenderName($this->resolveCustomerDisplayName($customer))
            ->setMessage($messageText);

        $ticket->addMessage($message);
        $ticket->registerNewMessage($messageText, $message->getCreatedAt() ?? new \DateTimeImmutable());
        $ticket->setStatus(SupportTicket::STATUS_OPEN);

        $entityManager->flush();

        $uploadedFiles = $request->files->all('attachments');
        if ([] === $uploadedFiles) {
            $singleFile = $request->files->get('attachment');
            if ($singleFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                $uploadedFiles = [$singleFile];
            }
        }

        if ([] !== $uploadedFiles) {
            $description = trim((string) $request->request->get('attachment_description', ''));

            foreach ($uploadedFiles as $uploadedFile) {
                if (!$uploadedFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    continue;
                }

                try {
                    if ($uploadedFile->isValid()) {
                        $this->supportTicketFileService->uploadFile($ticket, $uploadedFile, '' !== $description ? $description : null);
                        continue;
                    }

                    return $this->json(['message' => 'Datei ungültig.'], Response::HTTP_UNPROCESSABLE_ENTITY);
                } catch (\InvalidArgumentException $exception) {
                    return $this->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        }

        return $this->json([
            'ticket' => $this->buildTicketArray($ticket),
        ]);
    }

    #[Route('/tickets/api/{id}/attachments/{attachmentId}/download', name: 'srf_portal_ticket_attachment_download', methods: ['GET'])]
    public function ticketAttachmentDownload(
        string $id,
        int $attachmentId,
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        if (!$customer instanceof Kunde) {
            throw $this->createAccessDeniedException();
        }

        $ticket = $this->resolveCustomerTicketByIdentifier($customer, $id);
        if (!$ticket instanceof SupportTicket) {
            throw $this->createNotFoundException('Ticket nicht gefunden.');
        }

        $attachment = null;
        foreach ($ticket->getAttachments() as $candidate) {
            if ($candidate instanceof SupportTicketAttachment && $candidate->getId() === $attachmentId) {
                $attachment = $candidate;
                break;
            }
        }

        if (!$attachment instanceof SupportTicketAttachment) {
            throw $this->createNotFoundException('Anhang nicht gefunden.');
        }

        return $this->file(
            $this->supportTicketFileService->getFilePath($attachment),
            $attachment->getOriginalFilename()
        );
    }

    #[Route('/tickets/api/{id}/attachments/{attachmentId}/preview', name: 'srf_portal_ticket_attachment_preview', methods: ['GET'])]
    public function ticketAttachmentPreview(
        string $id,
        int $attachmentId,
        Request $request,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        if (!$customer instanceof Kunde) {
            throw $this->createAccessDeniedException();
        }

        $ticket = $this->resolveCustomerTicketByIdentifier($customer, $id);
        if (!$ticket instanceof SupportTicket) {
            throw $this->createNotFoundException('Ticket nicht gefunden.');
        }

        $attachment = null;
        foreach ($ticket->getAttachments() as $candidate) {
            if ($candidate instanceof SupportTicketAttachment && $candidate->getId() === $attachmentId) {
                $attachment = $candidate;
                break;
            }
        }

        if (!$attachment instanceof SupportTicketAttachment) {
            throw $this->createNotFoundException('Anhang nicht gefunden.');
        }

        return $this->file($this->supportTicketFileService->getFilePath($attachment));
    }

    #[Route('/configurator', name: 'srf_portal_configurator', methods: ['GET'])]
    public function configurator(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        return $this->renderHost($request, $portalRuntimeResolver, 'Configurator | Siemoneit Racing');
    }

    #[Route('/dtc-codes{trailingSlash}', name: 'srf_portal_dtc_codes', requirements: ['trailingSlash' => '/?'], methods: ['GET'])]
    public function dtcCodes(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        return $this->renderHost($request, $portalRuntimeResolver, 'DTC Codes | Siemoneit Racing', [
            'dtcCodes' => $this->buildDtcCodesBootstrap(),
        ]);
    }

    #[Route('/statistics', name: 'srf_portal_statistics', methods: ['GET'])]
    public function statistics(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        $customer = $this->getCurrentCustomer();
        $statistics = null !== $customer ? $this->buildStatisticsBootstrap($customer) : null;

        return $this->renderHost($request, $portalRuntimeResolver, 'Statistics | Siemoneit Racing', [
            'statistics' => $statistics,
        ]);
    }

    #[Route('/email-templates', name: 'srf_portal_email_templates', methods: ['GET'])]
    public function emailTemplates(Request $request, PortalRuntimeResolver $portalRuntimeResolver): Response
    {
        return $this->renderHost($request, $portalRuntimeResolver, 'Email Templates | Siemoneit Racing');
    }

    #[Route('/header/notifications', name: 'srf_portal_header_notifications', methods: ['GET'])]
    public function headerNotifications(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        if (!$customer instanceof Kunde) {
            return $this->json([
                'orders' => [],
                'tickets' => [],
                'fetchedAt' => (new \DateTimeImmutable())->format(\DATE_ATOM),
            ]);
        }

        return $this->json([
            'orders' => $this->buildOrdersBootstrap($customer),
            'tickets' => $this->buildTicketsBootstrap($customer),
            'fetchedAt' => (new \DateTimeImmutable())->format(\DATE_ATOM),
        ]);
    }

    /**
     * @param array<string, mixed> $reactBootstrap
     */
    private function renderHost(Request $request, PortalRuntimeResolver $portalRuntimeResolver, string $title, array $reactBootstrap = []): Response
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);
        $customer = $this->getCurrentCustomer();

        $reactBootstrap['session'] ??= $this->buildSessionBootstrap($customer);
        $reactBootstrap['portalDesign'] ??= $portal->getResolvedDesign();

        if ($customer instanceof Kunde) {
            $reactBootstrap['orders'] ??= [
                'items' => $this->buildOrdersBootstrap($customer),
                'customerId' => $customer->getId(),
                'parkedOrders' => $this->portalDashboardBuilder->buildParkedOrders($customer, 10),
            ];

            $reactBootstrap['invoices'] ??= [
                'items' => $this->buildInvoicesBootstrap($customer, $portal),
            ];
        }

        return $this->render('portal/srfile/_react_host.html.twig', [
            'title' => $title,
            'reactBootstrap' => $reactBootstrap,
        ]);
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int, categories: list<array<string, string>>}
     */
    private function buildDtcCodesBootstrap(): array
    {
        $codes = $this->dtcCodeRepository->findVisibleForPortal();
        $items = [];
        $categories = [];

        foreach ($codes as $code) {
            $category = $code->getCategory();
            $categoryName = trim((string) $category?->getName());
            $categoryLetter = strtoupper(trim((string) $category?->getBuchstabe()));
            $categoryKey = sprintf('%s|%s', $categoryLetter, $categoryName);

            if ('' !== $categoryName && !isset($categories[$categoryKey])) {
                $categories[$categoryKey] = [
                    'key' => $categoryKey,
                    'name' => $categoryName,
                    'letter' => $categoryLetter,
                ];
            }

            $items[] = [
                'code' => trim($code->getFehlercode()),
                'description' => trim((string) ($code->getBeschreibung() ?? '')),
                'category' => [
                    'name' => $categoryName,
                    'letter' => $categoryLetter,
                ],
            ];
        }

        return [
            'items' => $items,
            'total' => count($items),
            'categories' => array_values($categories),
        ];
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
     * @return array<string, mixed>
     */
    private function buildStatisticsBootstrap(Kunde $customer): array
    {
        $customerEmail = mb_strtolower(trim((string) $customer->getEmail()));

        /** @var list<Auftrag> $orders */
        $queryBuilder = $this->auftragRepository->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setMaxResults(5000);

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
        $months = $this->buildStatisticsMonthBuckets();
        $monthKeys = array_keys($months);
        $currentMonthKey = $monthKeys[count($monthKeys) - 1] ?? null;
        $previousMonthKey = $monthKeys[count($monthKeys) - 2] ?? null;

        $brandCounts = [];
        $brandMonthlyCounts = [];
        $vehicleCounts = [];
        $stageCounts = [];
        $optionCounts = [];
        $totalCredits = 0;

        foreach ($orders as $order) {
            if (!$order instanceof Auftrag) {
                continue;
            }

            $vehicleData = is_array($order->getVehicleData()) ? $order->getVehicleData() : [];
            $credits = $this->resolveOrderCredits($vehicleData);
            $totalCredits += $credits;

            $createdAt = $order->getCreatedAt();
            if ($createdAt instanceof \DateTimeImmutable) {
                $monthKey = $createdAt->format('Y-m');
                if (array_key_exists($monthKey, $months)) {
                    $months[$monthKey]['orders']++;
                    $months[$monthKey]['credits'] += $credits;
                }
            }

            $brand = $this->resolveStatisticsBrand($order, $vehicleData);
            $brandCounts[$brand] = ($brandCounts[$brand] ?? 0) + 1;

            if (null !== $currentMonthKey || null !== $previousMonthKey) {
                $monthKey = $createdAt instanceof \DateTimeImmutable ? $createdAt->format('Y-m') : '';
                if ('' !== $monthKey) {
                    if (!isset($brandMonthlyCounts[$brand])) {
                        $brandMonthlyCounts[$brand] = [];
                    }
                    $brandMonthlyCounts[$brand][$monthKey] = ($brandMonthlyCounts[$brand][$monthKey] ?? 0) + 1;
                }
            }

            $vehicleLabel = $this->resolveStatisticsVehicleLabel($order, $vehicleData);
            $vehicleStage = $this->resolveStatisticsStage($order, $vehicleData);
            if (!isset($vehicleCounts[$vehicleLabel])) {
                $vehicleCounts[$vehicleLabel] = [
                    'vehicle' => $vehicleLabel,
                    'brand' => $brand,
                    'count' => 0,
                    'stage' => $vehicleStage,
                ];
            }
            $vehicleCounts[$vehicleLabel]['count']++;
            if (($vehicleCounts[$vehicleLabel]['stage'] ?? '') === '' && $vehicleStage !== '') {
                $vehicleCounts[$vehicleLabel]['stage'] = $vehicleStage;
            }

            $stage = $vehicleStage !== '' ? $vehicleStage : 'Unbekannt';
            $stageCounts[$stage] = ($stageCounts[$stage] ?? 0) + 1;

            $options = $vehicleData['selectedOptions'] ?? [];
            if (is_array($options)) {
                foreach ($options as $option) {
                    $normalizedOption = trim((string) $option);
                    if ('' === $normalizedOption) {
                        continue;
                    }
                    $optionCounts[$normalizedOption] = ($optionCounts[$normalizedOption] ?? 0) + 1;
                }
            }
        }

        arsort($brandCounts);
        arsort($stageCounts);
        arsort($optionCounts);
        uasort($vehicleCounts, static fn (array $left, array $right): int => (int) ($right['count'] <=> $left['count']));

        $totalOrders = count($orders);
        $thisMonth = null !== $currentMonthKey && isset($months[$currentMonthKey]) ? (int) $months[$currentMonthKey]['orders'] : 0;
        $previousMonth = null !== $previousMonthKey && isset($months[$previousMonthKey]) ? (int) $months[$previousMonthKey]['orders'] : 0;
        $thisMonthTrend = $this->calculateTrendPercent($thisMonth, $previousMonth);
        $avgCredits = $totalOrders > 0 ? (int) round($totalCredits / $totalOrders) : 0;

        $brandStats = [];
        foreach (array_slice($brandCounts, 0, 8, true) as $brand => $count) {
            $brandCurrent = (int) (($brandMonthlyCounts[$brand][$currentMonthKey] ?? 0));
            $brandPrevious = (int) (($brandMonthlyCounts[$brand][$previousMonthKey] ?? 0));

            $brandStats[] = [
                'brand' => $brand,
                'count' => (int) $count,
                'pct' => $totalOrders > 0 ? (int) round((((int) $count) / $totalOrders) * 100) : 0,
                'trend' => $this->calculateTrendPercent($brandCurrent, $brandPrevious),
            ];
        }

        return [
            'monthlyOrders' => array_values(array_map(static fn (array $month): array => [
                'month' => $month['label'],
                'orders' => (int) $month['orders'],
                'credits' => (int) $month['credits'],
            ], $months)),
            'brandStats' => $brandStats,
            'brandPieData' => array_map(static fn (array $item): array => [
                'name' => (string) $item['brand'],
                'value' => (int) $item['count'],
            ], $brandStats),
            'topVehicles' => array_values(array_slice(array_map(static fn (array $vehicle): array => [
                'vehicle' => (string) ($vehicle['vehicle'] ?? ''),
                'brand' => (string) ($vehicle['brand'] ?? ''),
                'count' => (int) ($vehicle['count'] ?? 0),
                'stage' => (string) ($vehicle['stage'] ?? ''),
            ], $vehicleCounts), 0, 10)),
            'stageDistribution' => array_values(array_map(static fn (string $stage, int $count): array => [
                'name' => $stage,
                'value' => $count,
            ], array_keys(array_slice($stageCounts, 0, 8, true)), array_values(array_slice($stageCounts, 0, 8, true)))),
            'optionStats' => array_values(array_map(static fn (string $option, int $count): array => [
                'option' => $option,
                'count' => $count,
            ], array_keys(array_slice($optionCounts, 0, 10, true)), array_values(array_slice($optionCounts, 0, 10, true)))),
            'kpis' => [
                'totalOrders' => $totalOrders,
                'thisMonth' => $thisMonth,
                'thisMonthTrend' => $thisMonthTrend,
                'avgCredits' => $avgCredits,
                'topBrand' => isset($brandStats[0]) ? (string) $brandStats[0]['brand'] : '-',
                'topBrandCount' => isset($brandStats[0]) ? (int) $brandStats[0]['count'] : 0,
            ],
        ];
    }

    /**
     * @return array<string, array{label:string,orders:int,credits:int}>
     */
    private function buildStatisticsMonthBuckets(): array
    {
        $months = [];

        for ($index = 5; $index >= 0; --$index) {
            $month = new \DateTimeImmutable(sprintf('first day of -%d months 00:00:00', $index));
            $months[$month->format('Y-m')] = [
                'label' => $this->formatStatisticsMonthLabel($month),
                'orders' => 0,
                'credits' => 0,
            ];
        }

        return $months;
    }

    private function formatStatisticsMonthLabel(\DateTimeImmutable $month): string
    {
        $map = [
            'Jan' => 'Jan',
            'Feb' => 'Feb',
            'Mar' => 'Mär',
            'Apr' => 'Apr',
            'May' => 'Mai',
            'Jun' => 'Jun',
            'Jul' => 'Jul',
            'Aug' => 'Aug',
            'Sep' => 'Sep',
            'Oct' => 'Okt',
            'Nov' => 'Nov',
            'Dec' => 'Dez',
        ];

        return $map[$month->format('M')] ?? $month->format('m');
    }

    private function resolveStatisticsBrand(Auftrag $order, array $vehicleData): string
    {
        $brand = trim((string) ($order->getManufacturer() ?? ''));
        if ('' === $brand) {
            $brand = trim((string) ($vehicleData['manufacturer'] ?? ''));
        }

        return '' !== $brand ? $brand : 'Andere';
    }

    private function resolveStatisticsVehicleLabel(Auftrag $order, array $vehicleData): string
    {
        $label = trim((string) ($order->getVehicleDisplay() ?? ''));
        if ('' !== $label) {
            return $label;
        }

        $parts = array_values(array_filter([
            trim((string) ($order->getManufacturer() ?? '')),
            trim((string) ($order->getModel() ?? '')),
            trim((string) ($order->getEngine() ?? '')),
        ], static fn (string $value): bool => '' !== $value));

        if ([] === $parts) {
            $parts = array_values(array_filter([
                trim((string) ($vehicleData['manufacturer'] ?? '')),
                trim((string) (($vehicleData['vehicleModel'] ?? $vehicleData['model'] ?? ''))),
                trim((string) ($vehicleData['engine'] ?? '')),
            ], static fn (string $value): bool => '' !== $value));
        }

        if ([] === $parts) {
            return 'Unbekanntes Fahrzeug';
        }

        return implode(' ', $parts);
    }

    private function resolveStatisticsStage(Auftrag $order, array $vehicleData): string
    {
        $stage = trim((string) ($order->getStageLabel() ?? ''));
        if ('' === $stage) {
            $stage = trim((string) ($vehicleData['stageLabel'] ?? ''));
        }

        return $stage;
    }

    private function calculateTrendPercent(int $current, int $previous): int
    {
        if ($previous <= 0) {
            if ($current <= 0) {
                return 0;
            }

            return 100;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }

    /**
     * @param array<string, mixed> $vehicleData
     */
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

    /**
     * @return list<array<string, mixed>>
     */
    private function buildTicketsBootstrap(Kunde $customer): array
    {
        $queryBuilder = $this->supportTicketRepository->createQueryBuilder('t')
            ->leftJoin('t.messages', 'm')
            ->addSelect('m')
            ->andWhere('t.kunde = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('t.updatedAt', 'DESC')
            ->addOrderBy('m.createdAt', 'ASC')
            ->setMaxResults(200);

        /** @var list<SupportTicket> $tickets */
        $tickets = $queryBuilder->getQuery()->getResult();

        return array_map(fn (SupportTicket $ticket): array => $this->buildTicketArray($ticket), $tickets);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTicketArray(SupportTicket $ticket): array
    {
        $messages = [];
        foreach ($ticket->getMessages() as $message) {
            if (!$message instanceof SupportTicketMessage) {
                continue;
            }

            $messages[] = [
                'id' => $message->getId(),
                'sender' => $message->getSender() === SupportTicketMessage::SENDER_CUSTOMER ? 'customer' : 'support',
                'message' => $message->getMessage(),
                'timestamp' => $message->getCreatedAt()?->format(\DATE_ATOM),
            ];
        }

        $attachments = [];
        foreach ($ticket->getAttachments() as $attachment) {
            if (!$attachment instanceof SupportTicketAttachment || null === $attachment->getId()) {
                continue;
            }

            $attachments[] = [
                'id' => $attachment->getId(),
                'name' => $attachment->getOriginalFilename(),
                'description' => $attachment->getDescription(),
                'size' => $attachment->getFilesize(),
                'mimeType' => $attachment->getMimeType(),
                'createdAt' => $attachment->getCreatedAt()?->format(\DATE_ATOM),
                'previewUrl' => sprintf(
                    '/tickets/api/%s/attachments/%d/preview',
                    rawurlencode($ticket->getTicketNumber()),
                    $attachment->getId()
                ),
                'downloadUrl' => sprintf(
                    '/tickets/api/%s/attachments/%d/download',
                    rawurlencode($ticket->getTicketNumber()),
                    $attachment->getId()
                ),
            ];
        }

        return [
            'id' => $ticket->getTicketNumber(),
            'subject' => $ticket->getSubject(),
            'category' => 'other',
            'status' => match ($ticket->getStatus()) {
                SupportTicket::STATUS_IN_PROGRESS => 'answered',
                SupportTicket::STATUS_CLOSED => 'closed',
                default => 'open',
            },
            'priority' => match ($ticket->getPriority()) {
                SupportTicket::PRIORITY_URGENT => 'high',
                SupportTicket::PRIORITY_HIGH => 'high',
                SupportTicket::PRIORITY_LOW => 'low',
                default => 'normal',
            },
            'createdAt' => $ticket->getCreatedAt()?->format(\DATE_ATOM),
            'updatedAt' => $ticket->getUpdatedAt()?->format(\DATE_ATOM),
            'messages' => $messages,
            'attachments' => $attachments,
            'relatedOrder' => $this->extractRelatedOrderFromTicket($ticket),
        ];
    }

    private function resolveCustomerDisplayName(Kunde $customer): string
    {
        $name = trim(sprintf('%s %s', (string) $customer->getVorname(), (string) $customer->getNachname()));
        if ('' !== $name) {
            return $name;
        }

        $company = trim((string) $customer->getFirmenname());
        if ('' !== $company) {
            return $company;
        }

        $email = trim((string) $customer->getEmail());
        if ('' !== $email) {
            return $email;
        }

        return 'Kunde';
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPriceListBootstrap(): array
    {
        $mediaIconMap = $this->buildPriceListMediaIconMap();
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
                'iconPath' => $mediaIconMap[mb_strtolower(trim($item->getCode()))] ?? '',
            ];
        }

        return [
            'name' => $priceList->getName(),
            'description' => $priceList->getDescription(),
            'sections' => $sections,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildPriceListMediaIconMap(): array
    {
        $titleToPath = [];

        foreach ($this->profitCalculatorOptionRepository->findAll() as $option) {
            $path = trim((string) ($option->getResolvedIconPath() ?? ''));
            if ('' === $path) {
                continue;
            }

            $titleToPath[$this->normalizePriceListLookupKey($option->getTitle())] = $path;
        }

        $codeToTitles = [
            'adblue' => ['adblue off'],
            'auspuffklappen' => ['auspuffklappen', 'exhaust flaps'],
            'bmw_mini_garantie_patch' => ['dtc off'],
            'cat' => ['decat cat off'],
            'cvn_patch' => ['dtc off'],
            'dpf' => ['dpf off'],
            'drehmomentueberwachung_off' => ['vmax off'],
            'dsg_furzen' => ['dsg farts'],
            'dtc' => ['dtc off'],
            'egr' => ['agr off'],
            'hardcut_popcorn_limiter' => ['hard rev cut'],
            'harter_begrenzer' => ['hard rev cut'],
            'hotstart' => ['hot start fix'],
            'kaltstart_off' => ['cold start off'],
            'launch' => ['hard rev cut'],
            'launch_control_anti_lag_me7' => ['hard rev cut'],
            'launch_control_anti_lag_me9' => ['hard rev cut'],
            'launch_control_anti_lag_med17' => ['hard rev cut'],
            'luftmengenmesser_off' => ['agr off'],
            'nox_off' => ['decat cat off'],
            'oeldruck_fix' => ['hot start fix'],
            'opf_gpf_off' => ['gpf off'],
            'opf_off' => ['gpf off'],
            'pops' => ['pops bangs'],
            'pop_bang_sport' => ['popbang auf sport button'],
            'pop_bang_tempomat_me9' => ['pops bangs'],
            'sekundaerluftpumpe_off' => ['agr off'],
            'sport_display' => ['vmax off'],
            'startstop' => ['cold start off'],
            'swirl' => ['drallklappen flaps off'],
            'tuning_auf_sport_button' => ['vmax off'],
            'vmax' => ['vmax off'],
            'vmax_limit_setzen' => ['vmax off'],
            'wegfahrsperre_immo_off' => ['decat cat off'],
            'zylinderabschaltung_off' => ['cylinder on demand off'],
        ];

        $result = [];

        foreach ($codeToTitles as $code => $titles) {
            foreach ($titles as $title) {
                $normalizedTitle = $this->normalizePriceListLookupKey($title);
                if ('' !== $normalizedTitle && isset($titleToPath[$normalizedTitle])) {
                    $result[$code] = $titleToPath[$normalizedTitle];
                    break;
                }
            }
        }

        return $result;
    }

    private function normalizePriceListLookupKey(string $value): string
    {
        $normalized = mb_strtolower(trim($value));
        $normalized = str_replace(['ä', 'ö', 'ü', 'ß', '&', '/', '-'], ['a', 'o', 'u', 'ss', ' ', ' ', ' '], $normalized);

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $normalized));
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

    /**
     * @param array<string, mixed> $payload
     */
    private function readJsonPayload(Request $request): array
    {
        $raw = trim((string) $request->getContent());
        if ('' === $raw) {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeTicketPriority(string $priority): string
    {
        $normalized = strtolower(trim($priority));

        return match ($normalized) {
            SupportTicket::PRIORITY_LOW => SupportTicket::PRIORITY_LOW,
            SupportTicket::PRIORITY_HIGH => SupportTicket::PRIORITY_HIGH,
            SupportTicket::PRIORITY_URGENT => SupportTicket::PRIORITY_URGENT,
            default => SupportTicket::PRIORITY_NORMAL,
        };
    }

    private function normalizeRelatedOrderIdentifier(string $value): string
    {
        $normalized = trim($value);
        if ('' === $normalized) {
            return '';
        }

        $normalized = preg_replace('/\s+/', '', $normalized) ?? $normalized;
        $normalized = (string) preg_replace('/[^A-Za-z0-9\-_]/', '', $normalized);

        return trim($normalized);
    }

    private function extractRelatedOrderFromTicket(SupportTicket $ticket): ?string
    {
        $subject = trim($ticket->getSubject());
        if ('' !== $subject && preg_match('/^\[([A-Za-z0-9\-_]+)\]/', $subject, $matches) === 1) {
            $related = $this->normalizeRelatedOrderIdentifier((string) ($matches[1] ?? ''));
            if ('' !== $related) {
                return $related;
            }
        }

        foreach ($ticket->getMessages() as $message) {
            if (!$message instanceof SupportTicketMessage) {
                continue;
            }

            $text = (string) $message->getMessage();
            if (preg_match('/Auftrag:\s*([A-Za-z0-9\-_]+)/i', $text, $matches) === 1) {
                $related = $this->normalizeRelatedOrderIdentifier((string) ($matches[1] ?? ''));
                if ('' !== $related) {
                    return $related;
                }
            }
        }

        return null;
    }

    private function resolveCustomerTicketByIdentifier(Kunde $customer, string $identifier): ?SupportTicket
    {
        $normalized = trim($identifier);
        if ('' === $normalized) {
            return null;
        }

        $id = 0;
        if (preg_match('/^T-(\d+)$/i', $normalized, $matches) === 1) {
            $id = (int) $matches[1];
        } elseif (ctype_digit($normalized)) {
            $id = (int) $normalized;
        }

        if ($id <= 0) {
            return null;
        }

        return $this->supportTicketRepository->createQueryBuilder('t')
            ->andWhere('t.id = :id')
            ->andWhere('t.kunde = :customer')
            ->setParameter('id', $id)
            ->setParameter('customer', $customer)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
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
            'addressLine2' => trim(sprintf(
                '%s %s',
                (string) ($firma?->getPlz() ?? ''),
                (string) ($firma?->getStadt() ?? '')
            )),
            'country' => trim((string) ($firma?->getLand() ?? '')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildInvoiceCustomerPayload(Kunde $customer): array
    {
        $fullName = trim(sprintf('%s %s', (string) $customer->getVorname(), (string) $customer->getNachname()));
        $company = trim((string) $customer->getFirmenname());

        return [
            'name' => '' !== $company ? $company : $fullName,
            'contactName' => $fullName,
            'email' => trim((string) $customer->getEmail()),
            'phone' => trim((string) ($customer->getTelefon() ?? '')),
            'vatId' => trim((string) ($customer->getUstId() ?? '')),
            'taxNumber' => trim((string) ($customer->getSteuernummer() ?? '')),
            'addressLine1' => trim(sprintf(
                '%s %s',
                (string) ($customer->getStrasse() ?? ''),
                (string) ($customer->getHausnummer() ?? '')
            )),
            'addressLine2' => trim(sprintf(
                '%s %s',
                (string) ($customer->getPlz() ?? ''),
                (string) ($customer->getStadt() ?? '')
            )),
            'country' => trim((string) ($customer->getLand() ?? '')),
        ];
    }

    private function getCurrentCustomer(): ?Kunde
    {
        $user = $this->getUser();
        if (!$user instanceof SrfilePortalCustomerUser) {
            return null;
        }

        return $user->getCustomer();
    }

    /**
     * @return array<string, int|null>
     */
    private function buildSessionBootstrap(?Kunde $customer): array
    {
        return [
            'customerId' => $customer?->getId(),
            'credits' => null !== $customer ? (int) $customer->getCredits() : 0,
        ];
    }
}
