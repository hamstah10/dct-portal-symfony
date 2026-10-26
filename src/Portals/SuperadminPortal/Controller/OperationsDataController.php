<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Auftrag;
use App\Entity\EmailKonto;
use App\Entity\Firma;
use App\Entity\Kunde;
use App\Entity\SupportEmailMessage;
use App\Entity\SupportTicket;
use App\Entity\SuperadminCalendarEntry;
use App\Entity\SuperadminCalendarEvent;
use App\Entity\SuperadminPinnedVehicleType;
use App\Entity\SuperadminRememberedVehicle;
use App\Entity\SuperadminVehicleCompareList;
use App\Repository\AuftragRepository;
use App\Repository\EmailKontoRepository;
use App\Repository\FirmaRepository;
use App\Repository\KundeRepository;
use App\Repository\SupportEmailMessageRepository;
use App\Repository\SupportTicketRepository;
use App\Repository\SuperadminCalendarEntryRepository;
use App\Repository\SuperadminDashboardLinkRepository;
use App\Repository\SuperadminPinnedVehicleTypeRepository;
use App\Repository\SuperadminRememberedVehicleRepository;
use App\Repository\SuperadminVehicleCompareListRepository;
use App\Service\SuperadminProjectPasswordCipher;
use App\Service\SupportEmailFetchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class OperationsDataController extends AbstractController
{
    public function __construct(
        private readonly AuftragRepository $auftragRepository,
        private readonly KundeRepository $kundeRepository,
        private readonly SupportTicketRepository $supportTicketRepository,
        private readonly SupportEmailMessageRepository $supportEmailMessageRepository,
        private readonly EmailKontoRepository $emailKontoRepository,
        private readonly FirmaRepository $firmaRepository,
        private readonly SuperadminCalendarEntryRepository $calendarEntryRepository,
        private readonly SuperadminRememberedVehicleRepository $rememberedVehicleRepository,
        private readonly SuperadminPinnedVehicleTypeRepository $pinnedVehicleTypeRepository,
        private readonly SupportEmailFetchService $supportEmailFetchService,
        private readonly \App\Repository\GearboxStageOptionRepository $gearboxStageOptionRepository,
        private readonly \App\Repository\GearboxStageInfoRepository $gearboxStageInfoRepository,
        private readonly \App\Repository\GearboxDtcCodeRepository $gearboxDtcCodeRepository,
        private readonly \App\Repository\SuperadminSettingRepository $superadminSettingRepository,
        private readonly \App\Repository\VehicleBrandRepository $vehicleBrandRepository,
        private readonly \Symfony\Component\AssetMapper\AssetMapperInterface $assetMapper,
        private readonly EntityManagerInterface $entityManager,
        private readonly SuperadminDashboardLinkRepository $dashboardLinkRepository,
        private readonly SuperadminVehicleCompareListRepository $vehicleCompareListRepository,
        private readonly \App\Repository\SuperadminProjectRepository $superadminProjectRepository,
    ) {
    }

    #[Route('/operations-api/settings', name: 'superadmin_portal_operations_api_settings', methods: ['GET'])]
    public function settings(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portalRuntimeResolver->requirePortalSystem($request);

        $settings = [];
        foreach ($this->superadminSettingRepository->findAll() as $setting) {
            $settings[$setting->getSettingKey()] = $setting->getSettingValue();
        }

        return $this->json(['data' => (object) $settings]);
    }

    #[Route('/operations-api/settings', name: 'superadmin_portal_operations_api_settings_set', methods: ['PUT'])]
    public function settingsSet(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portalRuntimeResolver->requirePortalSystem($request);

        $body = json_decode((string) $request->getContent(), true);
        $key = is_array($body) ? trim((string) ($body['key'] ?? '')) : '';
        if ('' === $key || preg_match('/^[a-z0-9._-]{2,100}$/i', $key) !== 1) {
            return $this->json(['message' => 'Ungültiger Einstellungs-Schlüssel.'], 400);
        }

        $setting = $this->superadminSettingRepository->findOneBy(['settingKey' => $key]);
        if (!$setting instanceof \App\Entity\SuperadminSetting) {
            $setting = (new \App\Entity\SuperadminSetting())->setSettingKey($key);
        }

        $setting->setSettingValue($body['value'] ?? null);
        $this->entityManager->persist($setting);
        $this->entityManager->flush();

        return $this->json(['data' => ['key' => $key, 'value' => $setting->getSettingValue()]]);
    }

    #[Route('/operations-api/vehicle-brands', name: 'superadmin_portal_operations_api_vehicle_brands', methods: ['GET'])]
    public function vehicleBrands(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        $pool = $portal->getVehicleDataPool();
        if (null === $pool) {
            return $this->json(['data' => []]);
        }

        $brands = $this->vehicleBrandRepository->createQueryBuilder('b')
            ->andWhere('b.vehicleDataPool = :pool')
            ->andWhere('b.active = :active')
            ->setParameter('pool', $pool)
            ->setParameter('active', true)
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(['data' => array_values(array_map(
            static fn (\App\Entity\VehicleBrand $brand): array => [
                'id' => $brand->getId(),
                'name' => $brand->getName(),
                'showInTopMenu' => $brand->isShowInTopMenu(),
            ],
            $brands
        ))]);
    }

    #[Route('/operations-api/vehicle-brands/{id}/top-menu', name: 'superadmin_portal_operations_api_vehicle_brand_top_menu', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function vehicleBrandTopMenu(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portal = $portalRuntimeResolver->requirePortalSystem($request);

        $brand = $this->vehicleBrandRepository->find($id);
        if (!$brand instanceof \App\Entity\VehicleBrand || $brand->getVehicleDataPool()?->getId() !== $portal->getVehicleDataPool()?->getId()) {
            return $this->json(['message' => 'Marke nicht gefunden.'], 404);
        }

        $body = json_decode((string) $request->getContent(), true);
        $enabled = is_array($body) && filter_var($body['enabled'] ?? false, FILTER_VALIDATE_BOOL);

        $brand->setShowInTopMenu($enabled);
        $this->entityManager->flush();

        return $this->json(['data' => [
            'id' => $brand->getId(),
            'name' => $brand->getName(),
            'showInTopMenu' => $brand->isShowInTopMenu(),
        ]]);
    }

    #[Route('/operations-api/dsg-dtc-codes', name: 'superadmin_portal_operations_api_dsg_dtc_codes', methods: ['GET'])]
    public function dsgDtcCodes(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portalRuntimeResolver->requirePortalSystem($request);

        $codes = $this->gearboxDtcCodeRepository->findActiveOrdered();

        return $this->json(['data' => array_values(array_map(
            static fn (\App\Entity\GearboxDtcCode $code): array => [
                'id' => $code->getId(),
                'obdCode' => $code->getObdCode(),
                'vagCode' => $code->getVagCode(),
                'description' => $code->getDescription(),
            ],
            $codes
        ))]);
    }

    #[Route('/operations-api/dsg-stage-infos', name: 'superadmin_portal_operations_api_dsg_stage_infos', methods: ['GET'])]
    public function dsgStageInfos(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portalRuntimeResolver->requirePortalSystem($request);

        $infos = $this->gearboxStageInfoRepository->findActiveOrdered();

        return $this->json(['data' => array_values(array_map(
            static fn (\App\Entity\GearboxStageInfo $info): array => [
                'name' => $info->getName(),
                'description' => $info->getDescription(),
                'features' => array_values(array_map(
                    static fn (\App\Entity\GearboxStageInfoFeature $feature): array => [
                        'label' => $feature->getLabel(),
                        'included' => $feature->isIncluded(),
                    ],
                    $info->getFeatures()->toArray()
                )),
            ],
            $infos
        ))]);
    }

    #[Route('/operations-api/dsg-stage-options', name: 'superadmin_portal_operations_api_dsg_stage_options', methods: ['GET'])]
    public function dsgStageOptions(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $portalRuntimeResolver->requirePortalSystem($request);

        $rows = $this->gearboxStageOptionRepository->findBy(['active' => true], ['sorting' => 'ASC', 'id' => 'ASC']);

        $gearboxes = [];
        foreach ($rows as $row) {
            $gearboxName = $row->getGearboxName();
            $category = $row->getCategory();

            $gearboxes[$gearboxName] ??= ['name' => $gearboxName, 'categories' => []];
            $gearboxes[$gearboxName]['categories'][$category] ??= [
                'name' => $category,
                'description' => $row->getCategoryDescription(),
                'options' => [],
            ];

            $gearboxes[$gearboxName]['categories'][$category]['options'][] = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'fahrbarkeit' => $row->getFahrbarkeit(),
                'stage1' => $row->getStage1(),
                'stage2' => $row->getStage2(),
                'stage2plus' => $row->getStage2Plus(),
                'stage3' => $row->getStage3(),
                'stage4' => $row->getStage4(),
            ];
        }

        return $this->json(['data' => array_values(array_map(
            static fn (array $gearbox): array => [
                'name' => $gearbox['name'],
                'categories' => array_values($gearbox['categories']),
            ],
            $gearboxes
        ))]);
    }

    #[Route('/operations-api/order-notifications', name: 'superadmin_portal_operations_api_order_notifications', methods: ['GET'])]
    public function orderNotifications(Request $request, PortalRuntimeResolver $portalRuntimeResolver): StreamedResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $lastEventIdHeader = $request->headers->get('Last-Event-ID');
        $lastEventId = is_numeric($lastEventIdHeader) ? (int) $lastEventIdHeader : null;
        $bootstrapId = null;

        if (null === $lastEventId) {
            $bootstrapId = $this->getLatestOrderId();
            $lastEventId = $bootstrapId;
        }

        if ($request->hasSession() && $request->getSession()->isStarted()) {
            $request->getSession()->save();
        }

        $response = new StreamedResponse(function () use ($lastEventId, $bootstrapId): void {
            @set_time_limit(0);

            echo "retry: 15000\n\n";

            if (null !== $bootstrapId) {
                echo 'id: ' . (string) $bootstrapId . "\n";
                echo "data: {\"summary\":\"\",\"content\":{}}\n\n";
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                @flush();
            }

            $currentLastId = (int) $lastEventId;
            $deadline = microtime(true) + 55.0;

            while (microtime(true) < $deadline) {
                if (connection_aborted()) {
                    break;
                }

                $orders = $this->auftragRepository->createQueryBuilder('a')
                    ->andWhere('a.id > :lastId')
                    ->setParameter('lastId', $currentLastId)
                    ->orderBy('a.id', 'ASC')
                    ->setMaxResults(10)
                    ->getQuery()
                    ->getResult();

                foreach ($orders as $order) {
                    if (!$order instanceof Auftrag) {
                        continue;
                    }

                    $id = (int) ($order->getId() ?? 0);
                    if ($id > $currentLastId) {
                        $currentLastId = $id;
                    }

                    echo 'id: ' . (string) $id . "\n";
                    echo 'data: ' . json_encode($this->buildOrderNotificationPayload($order), \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) . "\n\n";

                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }
                    @flush();
                }

                if ([] === $orders) {
                    echo ": ping\n\n";
                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }
                    @flush();
                }

                usleep(3_000_000);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    #[Route('/operations-api/orders/{id}', name: 'superadmin_portal_operations_api_order_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function orderDetail(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $order = $this->auftragRepository->find($id);
        if (null === $order) {
            return $this->json(['message' => 'Auftrag nicht gefunden.'], 404);
        }

        return $this->json(['data' => $this->serializeOrderDetail($order)]);
    }

    #[Route('/operations-api/orders/{id}/download-original', name: 'superadmin_portal_operations_api_order_download_original', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function orderDownloadOriginal(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): \Symfony\Component\HttpFoundation\Response
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $order = $this->auftragRepository->find($id);
        if (null === $order) {
            return new \Symfony\Component\HttpFoundation\Response('Auftrag nicht gefunden.', 404);
        }

        $raw = trim((string) $order->getFileData());
        if ('' === $raw) {
            return new \Symfony\Component\HttpFoundation\Response('Keine Originaldatei vorhanden.', 404);
        }

        $mimeType = 'application/octet-stream';
        $payload = $raw;

        if (str_starts_with($raw, 'data:')) {
            $parts = explode(',', $raw, 2);
            if (2 === count($parts)) {
                if (preg_match('/^data:([^;]+);base64/i', $parts[0], $m)) {
                    $mimeType = $m[1];
                }
                $payload = $parts[1];
            }
        }

        $decoded = base64_decode($payload, true);
        if (false === $decoded) {
            return new \Symfony\Component\HttpFoundation\Response('Datei konnte nicht gelesen werden.', 400);
        }

        $fileName = trim((string) $order->getFileName()) ?: 'original.bin';
        $response = new \Symfony\Component\HttpFoundation\Response($decoded);
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($fileName) . '"');
        $response->headers->set('Content-Length', (string) strlen($decoded));

        return $response;
    }

    #[Route('/operations-api/orders/{id}/status', name: 'superadmin_portal_operations_api_order_status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function orderStatus(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $order = $this->auftragRepository->find($id);
        if (null === $order) {
            return $this->json(['message' => 'Auftrag nicht gefunden.'], 404);
        }

        $body = json_decode((string) $request->getContent(), true);
        $frontendStatus = is_array($body) ? (string) ($body['status'] ?? '') : '';

        $statusMap = [
            'received'    => Auftrag::STATUS_RECEIVED,
            'in_progress' => Auftrag::STATUS_IN_PROGRESS,
            'parked'      => Auftrag::STATUS_PARKED,
            'completed'   => Auftrag::STATUS_COMPLETED,
            'rejected'    => Auftrag::STATUS_REJECTED,
        ];

        if (!isset($statusMap[$frontendStatus])) {
            return $this->json(['message' => 'Ungültiger Status: ' . $frontendStatus], 400);
        }

        $order->setStatus($statusMap[$frontendStatus]);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeOrderDetail($order)]);
    }

    #[Route('/operations-api/orders/{id}/upload-finished', name: 'superadmin_portal_operations_api_order_upload_finished', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function orderUploadFinished(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $order = $this->auftragRepository->find($id);
        if (null === $order) {
            return $this->json(['message' => 'Auftrag nicht gefunden.'], 404);
        }

        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            return $this->json(['message' => 'Keine Datei erhalten.'], 400);
        }

        $relativeDir = 'uploads/auftraege/fertig/' . $id;
        $absoluteDir = $this->getParameter('kernel.project_dir') . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0775, true);
        }

        $clientName = trim(basename(str_replace(['\\', "\0"], ['/', ''], (string) $file->getClientOriginalName())));
        if ('' === $clientName) {
            $clientName = 'datei.bin';
        }

        $file->move($absoluteDir, $clientName);

        $entry = [
            'path'       => $relativeDir . '/' . $clientName,
            'name'       => $clientName,
            'size'       => $file->getSize() ?: null,
            'uploadedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        $existing = $order->getFinishedFiles();
        if ([] === $existing && null !== $order->getFinishedFilePath()) {
            $existing[] = [
                'path'       => $order->getFinishedFilePath(),
                'name'       => $order->getFinishedFileName(),
                'size'       => $order->getFinishedFileSize(),
                'uploadedAt' => $order->getFinishedFileUploadedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }
        $existing[] = $entry;

        $order->setFinishedFilePath($entry['path']);
        $order->setFinishedFileName($entry['name']);
        $order->setFinishedFileSize($entry['size']);
        $order->setFinishedFileUploadedAt(new \DateTimeImmutable());
        $order->setFinishedFiles($existing);

        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeOrderDetail($order)]);
    }

    #[Route('/operations-api/orders', name: 'superadmin_portal_operations_api_orders', methods: ['GET'])]
    public function orders(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $companyId = (int) $request->query->get('company', 0);
        $qb = $this->auftragRepository->createQueryBuilder('a')
            ->leftJoin('a.kunde', 'k')
            ->addSelect('k')
            ->leftJoin('k.firma', 'f')
            ->addSelect('f')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(250);

        if ($companyId > 0) {
            $qb
                ->andWhere('f.id = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $orders = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (Auftrag $order): array => $this->serializeOrder($order), $orders),
        ]);
    }

    #[Route('/operations-api/customers', name: 'superadmin_portal_operations_api_customers', methods: ['GET'])]
    public function customers(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $companyId = (int) $request->query->get('company', 0);
        $qb = $this->kundeRepository->createQueryBuilder('k')
            ->leftJoin('k.firma', 'f')
            ->addSelect('f')
            ->orderBy('k.createdAt', 'DESC')
            ->setMaxResults(250);

        if ($companyId > 0) {
            $qb
                ->andWhere('f.id = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $customers = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (Kunde $customer): array => $this->serializeCustomer($customer), $customers),
        ]);
    }

    #[Route('/operations-api/customers/{id}', name: 'superadmin_portal_operations_api_customer_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function customerDetail(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $customer = $this->kundeRepository->find($id);
        if (!$customer instanceof Kunde) {
            return $this->json(['message' => 'Kunde nicht gefunden.'], 404);
        }

        return $this->json(['data' => $this->serializeCustomerDetail($customer)]);
    }

    #[Route('/operations-api/tickets', name: 'superadmin_portal_operations_api_tickets', methods: ['GET'])]
    public function tickets(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $companyId = (int) $request->query->get('company', 0);
        $qb = $this->supportTicketRepository->createQueryBuilder('t')
            ->leftJoin('t.firma', 'f')
            ->addSelect('f')
            ->leftJoin('t.kunde', 'k')
            ->addSelect('k')
            ->orderBy('t.updatedAt', 'DESC')
            ->setMaxResults(250);

        if ($companyId > 0) {
            $qb
                ->andWhere('f.id = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $tickets = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (SupportTicket $ticket): array => $this->serializeTicket($ticket), $tickets),
        ]);
    }

    /** Zähler für die Badges in der Hauptnavigation: offene Aufträge und offene Tickets. */
    #[Route('/operations-api/nav-counts', name: 'superadmin_portal_operations_api_nav_counts', methods: ['GET'])]
    public function navCounts(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $orders = (int) $this->auftragRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.status = :status')
            ->setParameter('status', Auftrag::STATUS_RECEIVED)
            ->getQuery()
            ->getSingleScalarResult();

        $tickets = (int) $this->supportTicketRepository->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.status = :status')
            ->setParameter('status', SupportTicket::STATUS_OPEN)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json(['data' => ['orders' => $orders, 'tickets' => $tickets]]);
    }

    /** Status eines Tickets ändern. Die ID ist die Ticket-Nummer aus der Liste. */
    #[Route('/operations-api/tickets/{id}/status', name: 'superadmin_portal_operations_api_ticket_status', methods: ['POST'])]
    public function ticketStatus(string $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $payload = json_decode((string) $request->getContent(), true);
        $status = is_array($payload) ? (string) ($payload['status'] ?? '') : '';
        $mapped = match ($status) {
            'open' => SupportTicket::STATUS_OPEN,
            'in_progress' => SupportTicket::STATUS_IN_PROGRESS,
            'closed' => SupportTicket::STATUS_CLOSED,
            default => null,
        };
        if (null === $mapped) {
            throw new BadRequestHttpException('Unbekannter Ticket-Status.');
        }

        // Die Ticket-Nummer wird aus der ID gebildet (T-000123), ist also kein eigenes Feld.
        $ticketId = 1 === preg_match('/^T-0*(\d+)$/i', $id, $matches) ? (int) $matches[1] : (ctype_digit($id) ? (int) $id : 0);
        $ticket = $ticketId > 0 ? $this->supportTicketRepository->find($ticketId) : null;
        if (!$ticket instanceof SupportTicket) {
            throw new NotFoundHttpException('Ticket nicht gefunden.');
        }

        $ticket->setStatus($mapped);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeTicket($ticket)]);
    }

    #[Route('/operations-api/companies', name: 'superadmin_portal_operations_api_companies', methods: ['GET'])]
    public function companies(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $companies = $this->firmaRepository->findBy(['aktiv' => true], ['name' => 'ASC']);

        return $this->json([
            'data' => array_map(fn (Firma $company): array => $this->serializeCompany($company), $companies),
        ]);
    }

    #[Route('/operations-api/remembered-vehicles', name: 'superadmin_portal_operations_api_remembered_vehicles', methods: ['GET'])]
    public function rememberedVehicles(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $vehicles = $this->rememberedVehicleRepository->createQueryBuilder('v')
            ->orderBy('v.updatedAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        return $this->json([
            'data' => array_map(fn (SuperadminRememberedVehicle $vehicle): array => $this->serializeRememberedVehicle($vehicle), $vehicles),
        ]);
    }

    #[Route('/operations-api/remembered-vehicles', name: 'superadmin_portal_operations_api_remembered_vehicle_create', methods: ['POST'])]
    public function rememberedVehicleCreate(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $body = json_decode((string) $request->getContent(), true);
        if (!is_array($body)) {
            return $this->json(['message' => 'Ungültige Anfrage.'], 400);
        }

        $selection = $body['selection'] ?? $body;
        if (!is_array($selection)) {
            return $this->json(['message' => 'Fahrzeugauswahl fehlt.'], 400);
        }

        $engine = $selection['engine'] ?? null;
        $engineId = is_array($engine) && isset($engine['id']) && is_numeric($engine['id']) ? (int) $engine['id'] : 0;
        if ($engineId <= 0) {
            return $this->json(['message' => 'Motor fehlt.'], 400);
        }

        $label = trim((string) ($body['label'] ?? ''));
        $subline = trim((string) ($body['subline'] ?? ''));
        if ('' === $label) {
            $brand = is_array($selection['brand'] ?? null) ? (string) ($selection['brand']['name'] ?? '') : '';
            $engineName = is_array($engine) ? (string) ($engine['name'] ?? '') : '';
            $label = trim($brand . ' ' . $engineName);
        }

        if ('' === $label) {
            return $this->json(['message' => 'Bezeichnung fehlt.'], 400);
        }

        if ('' === $subline) {
            $subline = implode(' · ', array_values(array_filter([
                is_array($selection['type'] ?? null) ? (string) ($selection['type']['name'] ?? '') : '',
                is_array($selection['brand'] ?? null) ? (string) ($selection['brand']['name'] ?? '') : '',
                is_array($selection['series'] ?? null) ? (string) ($selection['series']['name'] ?? '') : '',
                is_array($selection['model'] ?? null) ? (string) ($selection['model']['name'] ?? '') : '',
            ])));
        }

        $vehicle = $this->rememberedVehicleRepository->findOneBy(['engineId' => $engineId]);
        $isNew = !$vehicle instanceof SuperadminRememberedVehicle;
        if ($isNew) {
            $vehicle = new SuperadminRememberedVehicle();
        }

        $vehicle
            ->setEngineId($engineId)
            ->setLabel($label)
            ->setSubline($subline)
            ->setSelection($selection)
            ->touch();

        $this->entityManager->persist($vehicle);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeRememberedVehicle($vehicle)], $isNew ? 201 : 200);
    }

    #[Route('/operations-api/remembered-vehicles/{id}', name: 'superadmin_portal_operations_api_remembered_vehicle_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function rememberedVehicleDelete(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $vehicle = $this->rememberedVehicleRepository->find($id);
        if (!$vehicle instanceof SuperadminRememberedVehicle) {
            return $this->json(['message' => 'Gemerktes Fahrzeug nicht gefunden.'], 404);
        }

        $this->entityManager->remove($vehicle);
        $this->entityManager->flush();

        return $this->json(['data' => ['deleted' => true]]);
    }

    #[Route('/operations-api/pinned-vehicle-type', name: 'superadmin_portal_operations_api_pinned_vehicle_type_get', methods: ['GET'])]
    public function pinnedVehicleType(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $pinned = $this->pinnedVehicleTypeRepository->findOneBy([]);

        return $this->json([
            'data' => $pinned instanceof SuperadminPinnedVehicleType ? ['typeId' => $pinned->getTypeId()] : null,
        ]);
    }

    #[Route('/operations-api/pinned-vehicle-type', name: 'superadmin_portal_operations_api_pinned_vehicle_type_set', methods: ['PUT'])]
    public function pinnedVehicleTypeSet(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $body = json_decode((string) $request->getContent(), true);
        $typeId = is_array($body) && isset($body['typeId']) && is_numeric($body['typeId']) ? (int) $body['typeId'] : 0;
        if ($typeId <= 0) {
            return $this->json(['message' => 'Fahrzeugtyp fehlt.'], 400);
        }

        $pinned = $this->pinnedVehicleTypeRepository->findOneBy([]);
        if (!$pinned instanceof SuperadminPinnedVehicleType) {
            $pinned = new SuperadminPinnedVehicleType();
        }

        $pinned->setTypeId($typeId)->touch();

        $this->entityManager->persist($pinned);
        $this->entityManager->flush();

        return $this->json(['data' => ['typeId' => $pinned->getTypeId()]]);
    }

    #[Route('/operations-api/pinned-vehicle-type', name: 'superadmin_portal_operations_api_pinned_vehicle_type_delete', methods: ['DELETE'])]
    public function pinnedVehicleTypeDelete(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $pinned = $this->pinnedVehicleTypeRepository->findOneBy([]);
        if ($pinned instanceof SuperadminPinnedVehicleType) {
            $this->entityManager->remove($pinned);
            $this->entityManager->flush();
        }

        return $this->json(['data' => true]);
    }

    #[Route('/operations-api/calendar-entries', name: 'superadmin_portal_operations_api_calendar_entries', methods: ['GET'])]
    public function calendarEntries(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $companyId = (int) $request->query->get('company', 0);
        $from = $this->parseDateTime((string) $request->query->get('from', '')) ?? (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);
        $to = $this->parseDateTime((string) $request->query->get('to', '')) ?? (new \DateTimeImmutable('last day of this month'))->setTime(23, 59, 59);

        $qb = $this->calendarEntryRepository->createQueryBuilder('e')
            ->leftJoin('e.firma', 'f')
            ->addSelect('f')
            ->leftJoin('e.kunde', 'k')
            ->addSelect('k')
            ->andWhere('e.startsAt BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('e.startsAt', 'ASC')
            ->setMaxResults(500);

        if ($companyId > 0) {
            $qb
                ->andWhere('f.id = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $entries = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(fn (SuperadminCalendarEntry $entry): array => $this->serializeCalendarEntry($entry), $entries),
        ]);
    }

    #[Route('/operations-api/calendar-entries', name: 'superadmin_portal_operations_api_calendar_entry_create', methods: ['POST'])]
    public function calendarEntryCreate(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $body = json_decode((string) $request->getContent(), true);
        if (!is_array($body)) {
            return $this->json(['message' => 'Ungültige Anfrage.'], 400);
        }

        $customerType = (string) ($body['customer_type'] ?? SuperadminCalendarEntry::CUSTOMER_TYPE_EXISTING);
        $reason = trim((string) ($body['reason'] ?? ''));
        $startsAt = $this->parseDateTime((string) ($body['starts_at'] ?? ''));
        $endsAt = $this->parseDateTime((string) ($body['ends_at'] ?? ''));

        if (null === $startsAt) {
            return $this->json(['message' => 'Datum/Uhrzeit fehlt.'], 400);
        }

        if ('' === $reason) {
            return $this->json(['message' => 'Grund fehlt.'], 400);
        }

        $entry = (new SuperadminCalendarEntry())
            ->setCustomerType($customerType)
            ->setReason($reason)
            ->setStartsAt($startsAt)
            ->setEndsAt($endsAt)
            ->setNotes(trim((string) ($body['notes'] ?? '')) ?: null)
            ->setUpdatedAt(new \DateTimeImmutable());

        if (SuperadminCalendarEntry::CUSTOMER_TYPE_EXISTING === $entry->getCustomerType()) {
            $customerId = (int) ($body['customer_id'] ?? 0);
            $customer = $customerId > 0 ? $this->kundeRepository->find($customerId) : null;
            if (!$customer instanceof Kunde) {
                return $this->json(['message' => 'Bestandskunde fehlt.'], 400);
            }

            $entry
                ->setKunde($customer)
                ->setFirma($customer->getFirma());
        } else {
            $newCustomerName = trim((string) ($body['new_customer_name'] ?? ''));
            if ('' === $newCustomerName) {
                return $this->json(['message' => 'Name für Neukunde fehlt.'], 400);
            }

            $companyId = (int) ($body['company_id'] ?? 0);
            $company = $companyId > 0 ? $this->firmaRepository->find($companyId) : null;

            $entry
                ->setNewCustomerName($newCustomerName)
                ->setNewCustomerEmail(trim((string) ($body['new_customer_email'] ?? '')) ?: null)
                ->setFirma($company instanceof Firma ? $company : null);
        }

        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeCalendarEntry($entry)], 201);
    }

    #[Route('/operations-api/calendar-events', name: 'superadmin_portal_operations_api_calendar_events_list', methods: ['GET'])]
    public function calendarEventList(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $events = $this->entityManager->getRepository(SuperadminCalendarEvent::class)->findBy([], ['startsAt' => 'ASC'], 2000);

        return $this->json(['data' => array_map(fn (SuperadminCalendarEvent $event): array => $this->serializeCalendarEvent($event), $events)]);
    }

    #[Route('/operations-api/calendar-events', name: 'superadmin_portal_operations_api_calendar_events_create', methods: ['POST'])]
    public function calendarEventCreate(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $body = json_decode((string) $request->getContent(), true);
        if (!is_array($body)) {
            return $this->json(['message' => 'Ungültige Anfrage.'], 400);
        }

        $event = new SuperadminCalendarEvent();
        $error = $this->fillCalendarEvent($event, $body);
        if (null !== $error) {
            return $this->json(['message' => $error], 400);
        }

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeCalendarEvent($event)], 201);
    }

    #[Route('/operations-api/calendar-events/{id}', name: 'superadmin_portal_operations_api_calendar_events_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function calendarEventUpdate(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $event = $this->entityManager->getRepository(SuperadminCalendarEvent::class)->find($id);
        if (null === $event) {
            return $this->json(['message' => 'Termin nicht gefunden.'], 404);
        }

        $body = json_decode((string) $request->getContent(), true);
        if (!is_array($body)) {
            return $this->json(['message' => 'Ungültige Anfrage.'], 400);
        }

        $error = $this->fillCalendarEvent($event, $body);
        if (null !== $error) {
            return $this->json(['message' => $error], 400);
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeCalendarEvent($event)]);
    }

    #[Route('/operations-api/calendar-events/{id}', name: 'superadmin_portal_operations_api_calendar_events_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function calendarEventDelete(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $event = $this->entityManager->getRepository(SuperadminCalendarEvent::class)->find($id);
        if (null === $event) {
            return $this->json(['message' => 'Termin nicht gefunden.'], 404);
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();

        return $this->json(['data' => ['id' => $id, 'deleted' => true]]);
    }

    /**
     * Übernimmt die Felder aus dem Request. Gibt eine Fehlermeldung zurück oder null bei Erfolg.
     *
     * @param array<string, mixed> $body
     */
    private function fillCalendarEvent(SuperadminCalendarEvent $event, array $body): ?string
    {
        $title = trim((string) ($body['title'] ?? ''));
        if ('' === $title) {
            return 'Titel fehlt.';
        }

        $startsAt = $this->parseDateTime((string) ($body['start'] ?? ''));
        $endsAt = $this->parseDateTime((string) ($body['end'] ?? ''));
        if (null === $startsAt || null === $endsAt) {
            return 'Start oder Ende fehlt.';
        }

        if ($endsAt < $startsAt) {
            return 'Ende muss nach dem Start liegen.';
        }

        // Zeiten in der Server-Zeitzone speichern, sonst verschieben sich die Termine beim Lesen
        $zone = new \DateTimeZone(date_default_timezone_get());
        // firmaId 0/fehlend = Termin gilt für alle Firmen
        $firmaId = (int) ($body['firmaId'] ?? 0);
        $firma = $firmaId > 0 ? $this->firmaRepository->find($firmaId) : null;
        if ($firmaId > 0 && !$firma instanceof Firma) {
            return 'Firma nicht gefunden.';
        }

        $calendarId = in_array($body['calendarId'] ?? null, ['support', 'operations', 'marketing'], true)
            ? (string) $body['calendarId']
            : 'support';
        $guests = is_array($body['guests'] ?? null)
            ? array_values(array_filter(array_map(static fn (mixed $guest): string => trim((string) $guest), $body['guests']), static fn (string $guest): bool => '' !== $guest))
            : [];

        $event
            ->setFirma($firma)
            ->setTitle($title)
            ->setDescription(trim((string) ($body['description'] ?? '')) ?: null)
            ->setLocation(trim((string) ($body['location'] ?? '')) ?: null)
            ->setGuests($guests)
            ->setCalendarId($calendarId)
            ->setColor(trim((string) ($body['color'] ?? '')) ?: null)
            ->setAllDay((bool) ($body['allDay'] ?? false))
            ->setStartsAt($startsAt->setTimezone($zone))
            ->setEndsAt($endsAt->setTimezone($zone))
            ->setUpdatedAt(new \DateTimeImmutable());

        return null;
    }

    /** @return array<string, mixed> */
    private function serializeCalendarEvent(SuperadminCalendarEvent $event): array
    {
        return [
            'id' => $event->getId(),
            'firmaId' => $event->getFirma()?->getId(),
            'firmaName' => $event->getFirma()?->getName(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'location' => $event->getLocation(),
            'guests' => $event->getGuests(),
            'calendarId' => $event->getCalendarId(),
            'color' => $event->getColor(),
            'allDay' => $event->isAllDay(),
            'start' => $event->getStartsAt()->format(\DATE_ATOM),
            'end' => $event->getEndsAt()->format(\DATE_ATOM),
        ];
    }

    #[Route('/operations-api/emails', name: 'superadmin_portal_operations_api_emails', methods: ['GET'])]
    public function emails(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $folder = strtolower(trim((string) $request->query->get('folder', 'all')));
        $search = trim((string) $request->query->get('q', ''));
        $companyId = (int) $request->query->get('company', 0);
        $accountId = (int) $request->query->get('account', 0);

        $qb = $this->supportEmailMessageRepository->createQueryBuilder('m')
            ->leftJoin('m.emailKonto', 'k')
            ->addSelect('k')
            ->orderBy('m.receivedAt', 'DESC')
            ->addOrderBy('m.sentAt', 'DESC')
            ->addOrderBy('m.updatedAt', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->setMaxResults(250);

        $validFolders = array_values(SupportEmailMessage::getFolderChoices());
        if ('all' !== $folder && in_array($folder, $validFolders, true)) {
            $qb
                ->andWhere('m.folder = :folder')
                ->setParameter('folder', $folder);
        }

        if ('' !== $search) {
            $token = '%' . mb_strtolower($search) . '%';
            $qb
                ->andWhere(
                    'LOWER(COALESCE(m.subject, \'\')) LIKE :search
                     OR LOWER(COALESCE(m.fromName, \'\')) LIKE :search
                     OR LOWER(COALESCE(m.fromEmail, \'\')) LIKE :search
                     OR LOWER(COALESCE(m.toEmail, \'\')) LIKE :search
                     OR LOWER(COALESCE(m.bodyText, \'\')) LIKE :search'
                )
                ->setParameter('search', $token);
        }

        if ($accountId > 0) {
            $qb
                ->andWhere('k.id = :accountId')
                ->setParameter('accountId', $accountId);
        } elseif ($companyId > 0) {
            $qb
                ->leftJoin('k.firma', 'f')
                ->andWhere('f.id = :companyId')
                ->setParameter('companyId', $companyId);
        }

        $messages = $qb->getQuery()->getResult();

        return $this->json([
            'data' => array_map(
                fn (SupportEmailMessage $message): array => $this->serializeEmailMessage($message, false),
                $messages
            ),
        ]);
    }

    #[Route('/operations-api/email-accounts', name: 'superadmin_portal_operations_api_email_accounts', methods: ['GET'])]
    public function emailAccounts(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $accounts = $this->emailKontoRepository->createQueryBuilder('k')
            ->leftJoin('k.firma', 'f')
            ->addSelect('f')
            ->andWhere('k.active = :active')
            ->setParameter('active', true)
            ->orderBy('f.name', 'ASC')
            ->addOrderBy('k.sorting', 'ASC')
            ->addOrderBy('k.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json([
            'data' => array_map(
                fn (EmailKonto $account): array => $this->serializeEmailAccount($account),
                $accounts
            ),
        ]);
    }

    #[Route('/operations-api/emails/{id}', name: 'superadmin_portal_operations_api_email_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function emailDetail(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $message = $this->supportEmailMessageRepository->find($id);
        if (!$message instanceof SupportEmailMessage) {
            return $this->json(['message' => 'E-Mail nicht gefunden.'], 404);
        }

        if (!$message->isRead()) {
            $message
                ->setIsRead(true)
                ->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        return $this->json(['data' => $this->serializeEmailMessage($message, true)]);
    }

    #[Route('/operations-api/emails/fetch', name: 'superadmin_portal_operations_api_email_fetch', methods: ['POST'])]
    public function emailFetch(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $accounts = $this->emailKontoRepository->findBy(
            ['active' => true, 'fetchEnabled' => true],
            ['sorting' => 'ASC', 'name' => 'ASC']
        );

        $stats = [
            'fetched' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($accounts as $account) {
            if (!$account instanceof EmailKonto) {
                continue;
            }

            $result = $this->supportEmailFetchService->fetchAccount($account, 100);
            $stats['fetched'] += (int) ($result['fetched'] ?? 0);
            $stats['created'] += (int) ($result['created'] ?? 0);
            $stats['updated'] += (int) ($result['updated'] ?? 0);
            $stats['failed'] += (int) ($result['failed'] ?? 0);

            $error = trim((string) ($result['error'] ?? ''));
            if ('' !== $error) {
                $stats['errors'][] = sprintf('%s: %s', (string) ($result['accountLabel'] ?? 'Konto'), $error);
            }
        }

        return $this->json(['data' => $stats]);
    }

    /** @var array<string, string|null> */
    private array $orderBrandLogoCache = [];

    /**
     * Markenlogo für die Auftragsliste über den Brand-Master auflösen (pro Request gecacht).
     */
    private function resolveOrderBrandLogo(?string $manufacturer): ?string
    {
        $key = mb_strtolower(trim((string) ($manufacturer ?? '')));
        if ('' === $key) {
            return null;
        }

        if (array_key_exists($key, $this->orderBrandLogoCache)) {
            return $this->orderBrandLogoCache[$key];
        }

        $master = $this->entityManager->getRepository(\App\Entity\BrandMaster::class)
            ->createQueryBuilder('bm')
            ->andWhere('LOWER(bm.name) = :name')
            ->setParameter('name', $key)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $path = trim((string) ($master?->getResolvedImagePath() ?? ''));

        // AssetMapper-Pfade (assets/...) auf den digested Public-Pfad aufloesen,
        // da der logische Pfad nicht direkt ausliefbar ist.
        $logical = ltrim($path, '/');
        if (str_starts_with($logical, 'assets/')) {
            $publicPath = $this->assetMapper->getPublicPath(substr($logical, 7));
            $path = $publicPath ?? '';
        } elseif ('' !== $path && !str_starts_with($path, '/') && !preg_match('#^https?://#i', $path)) {
            $path = '/' . $path;
        }

        return $this->orderBrandLogoCache[$key] = ('' !== $path ? $path : null);
    }

    /** Verbrauchte Credits je Kunde (Verbrauch minus Storno). */
    private ?array $customerCreditsCache = null;

    private function customerCredits(?int $customerId): int
    {
        if (null === $customerId) {
            return 0;
        }

        if (null === $this->customerCreditsCache) {
            $rows = $this->entityManager->getConnection()->fetchAllAssociative(
                "SELECT kunde_id, SUM(-amount) AS credits FROM kunde_credit_transaction WHERE transaction_type IN ('usage', 'reversal') GROUP BY kunde_id"
            );
            $this->customerCreditsCache = [];
            foreach ($rows as $row) {
                $this->customerCreditsCache[(int) $row['kunde_id']] = (int) $row['credits'];
            }
        }

        return $this->customerCreditsCache[$customerId] ?? 0;
    }

    /** Verbrauchte Credits je Auftragsnummer, aus den Kunden-Transaktionen (Verbrauch minus Storno). */
    private ?array $orderCreditsCache = null;

    private function orderCredits(?string $orderNumber): int
    {
        if (null === $orderNumber || '' === $orderNumber) {
            return 0;
        }

        if (null === $this->orderCreditsCache) {
            $rows = $this->entityManager->getConnection()->fetchAllAssociative(
                "SELECT id, amount, transaction_type, description, reversal_of_id FROM kunde_credit_transaction WHERE transaction_type IN ('usage', 'reversal')"
            );
            $usageOrder = [];
            foreach ($rows as $row) {
                if ('usage' === $row['transaction_type'] && preg_match('/^Auftrag (\S+)/', (string) $row['description'], $m)) {
                    $usageOrder[(int) $row['id']] = $m[1];
                }
            }
            $map = [];
            foreach ($rows as $row) {
                $number = 'usage' === $row['transaction_type']
                    ? ($usageOrder[(int) $row['id']] ?? null)
                    : ($usageOrder[(int) $row['reversal_of_id']] ?? null);
                if (null === $number) {
                    continue;
                }
                $map[$number] = ($map[$number] ?? 0) - (int) $row['amount'];
            }
            $this->orderCreditsCache = $map;
        }

        return (int) ($this->orderCreditsCache[$orderNumber] ?? 0);
    }

    private function serializeOrder(Auftrag $order): array
    {
        $customer = $order->getKunde();
        $company = $customer?->getFirma();
        $customerName = $customer?->__toString() ?: 'Unbekannter Kunde';
        $vehicleLabel = $order->getVehicleDisplay();
        if (null === $vehicleLabel || '' === trim($vehicleLabel)) {
            $vehicleLabel = trim(implode(' ', array_filter([
                $order->getManufacturer(),
                $order->getModel(),
                $order->getBuilt(),
            ], static fn (?string $value): bool => null !== $value && '' !== trim($value))));
        }

        $items = array_values(array_filter([
            $order->getStageLabel(),
            $order->getSelectedOptionsSummary() !== '-' ? $order->getSelectedOptionsSummary() : null,
            $order->getTuningSetupSummary() !== '-' ? $order->getTuningSetupSummary() : null,
        ], static fn (?string $value): bool => null !== $value && '' !== trim($value)));

        if ([] === $items) {
            $items = ['Datei-Upload'];
        }

        return [
            'id' => $order->getOrderNumber(),
            'entity_id' => $order->getId(),
            'created_at' => $order->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updated_at' => $order->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'status' => $this->mapOrderStatus($order->getStatus()),
            'total_eur' => 0,
            'credits' => $this->orderCredits($order->getOrderNumber()),
            'items' => $items,
            'customer_id' => $customer?->getId(),
            'customer_name' => $customerName,
            'customer_email' => $customer?->getEmail() ?? $order->getCustomerEmail() ?? '',
            'company_id' => $company?->getId(),
            'company_name' => $company?->getName() ?? $customer?->getFirmenname(),
            'vehicle_brand_logo' => $this->resolveOrderBrandLogo($order->getManufacturer()),
            'vehicle_label' => $vehicleLabel ?: 'Fahrzeug nicht angegeben',
            'vehicle_subline' => trim(implode(' · ', array_filter([
                $order->getEngine(),
                $order->getEcu(),
            ], static fn (?string $value): bool => null !== $value && '' !== trim($value)))),
            'priority_label' => $order->getPriorityLabel() ?? 'Normal',
            'admin_url' => $order->getId() !== null ? '/admin/auftrag/' . $order->getId() : null,
            'admin_customer_url' => $customer?->getId() !== null ? '/admin/kunde/' . $customer->getId() : null,
        ];
    }

    private function assertOperationsRequest(Request $request, PortalRuntimeResolver $portalRuntimeResolver): void
    {
        $systemType = (string) $request->attributes->get('_brand_system_type', '');
        if (SystemType::SUPERADMIN === $systemType) {
            return;
        }

        $portal = $portalRuntimeResolver->requirePortalSystem($request);
        if ($portal->getPortalType() !== \App\Entity\Portal::TYPE_SUPERADMIN_PORTAL) {
            throw new NotFoundHttpException('Superadmin operations API not available for this portal.');
        }
    }

    private function getLatestOrderId(): int
    {
        try {
            $value = $this->auftragRepository->createQueryBuilder('a')
                ->select('MAX(a.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Throwable) {
            return 0;
        }

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @return array{summary: string, content: array<string, mixed>, order: array<string, mixed>}
     */
    private function buildOrderNotificationPayload(Auftrag $order): array
    {
        $serializedOrder = $this->serializeOrder($order);
        $summary = sprintf('Neuer Auftrag %s', $order->getOrderNumber());
        $bodyParts = array_values(array_filter([
            $serializedOrder['company_name'] ?? null,
            $serializedOrder['customer_name'] ?? null,
            $serializedOrder['vehicle_label'] ?? null,
        ], static fn (mixed $value): bool => is_string($value) && '' !== trim($value)));

        return [
            'summary' => $summary,
            'content' => [
                'body' => [] !== $bodyParts ? implode(' | ', $bodyParts) : 'Ein neuer Auftrag ist eingegangen.',
                'tag' => (string) ($order->getId() ?? ''),
            ],
            'order' => $serializedOrder,
        ];
    }

    private function serializeCustomer(Kunde $customer): array
    {
        $company = $customer->getFirma();
        $orders = $customer->getAuftraege()->toArray();
        usort($orders, static function (Auftrag $left, Auftrag $right): int {
            return ($right->getCreatedAt()?->getTimestamp() ?? 0) <=> ($left->getCreatedAt()?->getTimestamp() ?? 0);
        });

        /** @var Auftrag|null $latestOrder */
        $latestOrder = $orders[0] ?? null;
        $vehicleLabel = $latestOrder?->getVehicleDisplay();
        if (null === $vehicleLabel || '' === trim($vehicleLabel)) {
            $vehicleLabel = trim(implode(' ', array_filter([
                $latestOrder?->getManufacturer(),
                $latestOrder?->getModel(),
                $latestOrder?->getBuilt(),
            ], static fn (?string $value): bool => null !== $value && '' !== trim($value))));
        }

        return [
            'id' => (string) $customer->getId(),
            'entity_id' => $customer->getId(),
            'created_at' => $customer->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'status' => $customer->isGesperrt() ? 'blocked' : 'active',
            'name' => (string) $customer,
            'email' => $customer->getEmail() ?? '',
            'phone' => $customer->getTelefon(),
            'company_id' => $company?->getId(),
            'company_name' => $company?->getName() ?? $customer->getFirmenname(),
            'customer_number' => $customer->getKundennummer(),
            'order_count' => count($orders),
            'total_eur' => 0,
            'credits' => $this->customerCredits($customer->getId()),
            'vehicle_label' => $vehicleLabel,
            'admin_url' => $customer->getId() !== null ? '/admin/kunde/' . $customer->getId() : null,
        ];
    }

    private function serializeCustomerDetail(Kunde $customer): array
    {
        $base = $this->serializeCustomer($customer);
        $orders = $customer->getAuftraege()->toArray();
        usort($orders, static function (Auftrag $left, Auftrag $right): int {
            return ($right->getCreatedAt()?->getTimestamp() ?? 0) <=> ($left->getCreatedAt()?->getTimestamp() ?? 0);
        });

        $addressLine = trim(implode(' ', array_filter([
            $customer->getStrasse(),
            $customer->getHausnummer(),
        ], static fn (?string $value): bool => null !== $value && '' !== trim($value))));
        $cityLine = trim(implode(' ', array_filter([
            $customer->getPlz(),
            $customer->getStadt(),
        ], static fn (?string $value): bool => null !== $value && '' !== trim($value))));

        return array_merge($base, [
            'first_name' => $customer->getVorname(),
            'last_name' => $customer->getNachname(),
            'street' => $customer->getStrasse(),
            'house_number' => $customer->getHausnummer(),
            'postal_code' => $customer->getPlz(),
            'city' => $customer->getStadt(),
            'country' => $customer->getLand(),
            'address_line' => $addressLine ?: null,
            'city_line' => $cityLine ?: null,
            'website' => $customer->getWebsite(),
            'company_description' => $customer->getFirmenbeschreibung(),
            'vat_id' => $customer->getUstId(),
            'tax_number' => $customer->getSteuernummer(),
            'lexware_contact_id' => $customer->getLexwareContactId(),
            'credits' => $customer->getCredits(),
            'brevo_synced_at' => $customer->getBrevoSyncedAt()?->format(\DateTimeInterface::ATOM),
            'password_changed_at' => $customer->getPasswordChangedAt()?->format(\DateTimeInterface::ATOM),
            'recent_orders' => array_map(
                fn (Auftrag $order): array => $this->serializeOrder($order),
                array_slice($orders, 0, 25)
            ),
        ]);
    }

    private function serializeTicket(SupportTicket $ticket): array
    {
        $customer = $ticket->getKunde();
        $company = $ticket->getFirma();

        return [
            'id' => $ticket->getTicketNumber(),
            'entity_id' => $ticket->getId(),
            'subject' => $ticket->getSubject(),
            'customer_name' => $customer?->__toString() ?: 'Unbekannter Kunde',
            'customer_email' => $customer?->getEmail() ?? '',
            'company_id' => $company?->getId(),
            'company_name' => $company?->getName(),
            'priority' => $this->mapTicketPriority($ticket->getPriority()),
            'status' => $this->mapTicketStatus($ticket->getStatus()),
            'category' => 'Support',
            'created_at' => $ticket->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'last_reply_at' => ($ticket->getLastMessageAt() ?? $ticket->getUpdatedAt())?->format(\DateTimeInterface::ATOM),
            'messages' => $ticket->getMessageCount(),
            'last_message_preview' => $ticket->getLastMessagePreview(),
            'admin_url' => null,
        ];
    }

    private function serializeEmailMessage(SupportEmailMessage $message, bool $includeBody): array
    {
        $account = $message->getEmailKonto();
        $company = $account?->getFirma();
        $date = $message->getReceivedAt() ?: $message->getSentAt() ?: $message->getCreatedAt();
        $bodyText = $message->getBodyText();
        $preview = trim(preg_replace('/\s+/', ' ', strip_tags($bodyText)) ?? '');
        if (mb_strlen($preview) > 220) {
            $preview = mb_substr($preview, 0, 220) . '...';
        }

        $fromName = trim((string) ($message->getFromName() ?? ''));
        $fromEmail = trim((string) ($message->getFromEmail() ?? ''));
        $toEmail = trim((string) ($message->getToEmail() ?? ''));

        $data = [
            'id' => (string) ($message->getId() ?? 0),
            'entity_id' => $message->getId(),
            'account_id' => $account?->getId(),
            'account_label' => $account ? (string) $account : null,
            'company_id' => $company?->getId(),
            'company_name' => $company?->getName(),
            'folder' => $message->getFolder(),
            'from_name' => '' !== $fromName ? $fromName : ($fromEmail ?: '-'),
            'from_email' => $fromEmail,
            'to_name' => $toEmail ?: '-',
            'to_email' => $toEmail,
            'subject' => $message->getSubject() ?: '(Ohne Betreff)',
            'preview' => $preview,
            'timestamp' => $date?->format(\DateTimeInterface::ATOM),
            'read' => $message->isRead(),
            'starred' => $message->isStarred(),
            'attachment' => null,
            'admin_url' => $message->getId() !== null ? '/admin/support/email?message=' . $message->getId() : null,
        ];

        if ($includeBody) {
            $data['body'] = $bodyText;
            $data['body_html'] = $message->getBodyHtml();
        }

        return $data;
    }

    private function serializeEmailAccount(EmailKonto $account): array
    {
        $company = $account->getFirma();

        return [
            'id' => $account->getId(),
            'label' => (string) $account,
            'email_address' => $account->getEmailAddress(),
            'fetch_enabled' => $account->isFetchEnabled(),
            'send_enabled' => $account->isSendEnabled(),
            'company_id' => $company?->getId(),
            'company_name' => $company?->getName() ?? 'Ohne Firma',
        ];
    }

    private function serializeCompany(Firma $company): array
    {
        return [
            'id' => $company->getId(),
            'name' => $company->getName() ?? ('Firma #' . $company->getId()),
            'active' => $company->isAktiv(),
        ];
    }

    private function serializeCalendarEntry(SuperadminCalendarEntry $entry): array
    {
        $customer = $entry->getKunde();
        $company = $entry->getFirma();

        return [
            'id' => $entry->getId(),
            'company_id' => $company?->getId(),
            'company_name' => $company?->getName(),
            'customer_type' => $entry->getCustomerType(),
            'customer_id' => $customer?->getId(),
            'customer_name' => $customer ? (string) $customer : $entry->getNewCustomerName(),
            'customer_email' => $customer?->getEmail() ?? $entry->getNewCustomerEmail(),
            'reason' => $entry->getReason(),
            'starts_at' => $entry->getStartsAt()->format(\DateTimeInterface::ATOM),
            'ends_at' => $entry->getEndsAt()?->format(\DateTimeInterface::ATOM),
            'notes' => $entry->getNotes(),
            'created_at' => $entry->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updated_at' => $entry->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    private function serializeRememberedVehicle(SuperadminRememberedVehicle $vehicle): array
    {
        $selection = $vehicle->getSelection();

        return [
            'id' => $vehicle->getId(),
            'key' => (string) $vehicle->getEngineId(),
            'engine_id' => $vehicle->getEngineId(),
            'label' => $vehicle->getLabel(),
            'subline' => $vehicle->getSubline(),
            'type' => is_array($selection['type'] ?? null) ? $selection['type'] : null,
            'brand' => is_array($selection['brand'] ?? null) ? $selection['brand'] : null,
            'series' => is_array($selection['series'] ?? null) ? $selection['series'] : null,
            'model' => is_array($selection['model'] ?? null) ? $selection['model'] : null,
            'engine' => is_array($selection['engine'] ?? null) ? $selection['engine'] : null,
            'selection' => $selection,
            'savedAt' => $vehicle->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'created_at' => $vehicle->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updated_at' => $vehicle->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    private function parseDateTime(string $value): ?\DateTimeImmutable
    {
        $value = trim($value);
        if ('' === $value) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function serializeOrderDetail(Auftrag $order): array
    {
        $base = $this->serializeOrder($order);

        $hasOriginal = '' !== trim((string) $order->getFileData());
        $finishedFiles = array_values(array_filter(
            $order->getFinishedFiles(),
            static fn (mixed $f): bool => is_array($f) && isset($f['name']),
        ));

        return array_merge($base, [
            'order_comment'      => $order->getOrderComment(),
            'admin_on_hold_msg'  => $order->getAdminOnHoldMessage(),
            'tuning_tool'        => $order->getTuningTool(),
            'tuning_method'      => $order->getTuningMethod(),
            'slave_or_master'    => $order->getSlaveOrMaster(),
            'reading_type'       => $order->getReadingType(),
            'vehicle_series'     => $order->getVehicleSeries(),
            'vehicle_type'       => $order->getVehicleType(),
            'built'              => $order->getBuilt(),
            'original_file'      => $hasOriginal ? [
                'name'      => $order->getFileName() ?? 'original.bin',
                'size'      => $order->getFileSize(),
                'download_url' => '/operations-api/orders/' . $order->getId() . '/download-original',
            ] : null,
            'finished_files'     => array_map(static function (array $f): array {
                return [
                    'name'         => (string) ($f['name'] ?? ''),
                    'size'         => isset($f['size']) ? (int) $f['size'] : null,
                    'uploaded_at'  => isset($f['uploadedAt']) ? (string) $f['uploadedAt'] : null,
                    'download_url' => isset($f['path']) ? '/operations-api/orders/file?path=' . rawurlencode((string) $f['path']) : null,
                ];
            }, $finishedFiles),
        ]);
    }

    private function mapOrderStatus(string $status): string
    {
        return match ($status) {
            Auftrag::STATUS_RECEIVED => 'received',
            Auftrag::STATUS_IN_PROGRESS => 'in_progress',
            Auftrag::STATUS_COMPLETED => 'completed',
            Auftrag::STATUS_REJECTED => 'rejected',
            Auftrag::STATUS_PARKED => 'parked',
            default => 'received',
        };
    }

    private function mapTicketPriority(string $priority): string
    {
        return match ($priority) {
            SupportTicket::PRIORITY_LOW => 'low',
            SupportTicket::PRIORITY_HIGH => 'high',
            SupportTicket::PRIORITY_URGENT => 'urgent',
            default => 'normal',
        };
    }

    private function mapTicketStatus(string $status): string
    {
        return match ($status) {
            SupportTicket::STATUS_IN_PROGRESS => 'in_progress',
            SupportTicket::STATUS_CLOSED => 'closed',
            default => 'open',
        };
    }

    #[Route('/operations-api/dashboard-links', name: 'superadmin_portal_operations_api_dashboard_links', methods: ['GET'])]
    public function dashboardLinks(): JsonResponse
    {
        $links = $this->dashboardLinkRepository->findBy(['active' => true], ['sortOrder' => 'ASC']);

        return $this->json([
            'data' => array_map(static function ($link): array {
                return [
                    'id' => $link->getId(),
                    'title' => $link->getTitle(),
                    'url' => $link->getUrl(),
                    'description' => $link->getDescription(),
                    'category' => $link->getCategory(),
                    'icon_name' => $link->getIconName(),
                    'open_in_new_tab' => $link->isOpenInNewTab(),
                ];
            }, $links),
        ]);
    }

    #[Route('/operations-api/vehicle-compare-lists', name: 'superadmin_portal_operations_api_vehicle_compare_lists', methods: ['GET'])]
    public function vehicleCompareLists(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['data' => []], 401);
        }

        $lists = $this->vehicleCompareListRepository->findBy(['user' => $user], ['updatedAt' => 'DESC']);

        return $this->json([
            'data' => array_map(static function ($list): array {
                return [
                    'id' => $list->getId(),
                    'name' => $list->getName(),
                    'vehicles' => $list->getVehicles(),
                    'createdAt' => $list->getCreatedAt()->format('c'),
                    'updatedAt' => $list->getUpdatedAt()->format('c'),
                ];
            }, $lists),
        ]);
    }

    #[Route('/operations-api/vehicle-compare-lists', name: 'superadmin_portal_operations_api_vehicle_compare_lists_create', methods: ['POST'])]
    public function createVehicleCompareList(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $payload = json_decode((string) $request->getContent(), true);
        $name = $payload['name'] ?? 'Unbenannt';
        $vehicles = $payload['vehicles'] ?? [];

        $list = new SuperadminVehicleCompareList();
        $list->setUser($user);
        $list->setName($name);
        $list->setVehicles($vehicles);

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        return $this->json([
            'data' => [
                'id' => $list->getId(),
                'name' => $list->getName(),
                'vehicles' => $list->getVehicles(),
                'createdAt' => $list->getCreatedAt()->format('c'),
                'updatedAt' => $list->getUpdatedAt()->format('c'),
            ],
        ], 201);
    }

    #[Route('/operations-api/vehicle-compare-lists/{id}', name: 'superadmin_portal_operations_api_vehicle_compare_lists_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteVehicleCompareList(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $list = $this->vehicleCompareListRepository->find($id);
        if (!$list || $list->getUser() !== $user) {
            throw new NotFoundHttpException('Compare list not found');
        }

        $this->entityManager->remove($list);
        $this->entityManager->flush();

        return $this->json(['deleted' => true]);
    }

    #[Route('/operations-api/superadmin-projects', name: 'superadmin_portal_operations_api_superadmin_projects', methods: ['GET'])]
    public function superadminProjects(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $projects = $this->superadminProjectRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->json([
            'data' => array_map(fn (\App\Entity\SuperadminProject $project): array => $this->serializeSuperadminProject($project), $projects),
        ]);
    }

    #[Route('/operations-api/superadmin-projects/{id}', name: 'superadmin_portal_operations_api_superadmin_project_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function superadminProjectDetail(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $project = $this->superadminProjectRepository->find($id);
        if (!$project) {
            return $this->json(['message' => 'Projekt nicht gefunden'], 404);
        }

        return $this->json(['data' => $this->serializeSuperadminProject($project)]);
    }

    #[Route('/operations-api/superadmin-projects/{id}/login-password', name: 'superadmin_portal_operations_api_superadmin_project_login_password', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function superadminProjectLoginPassword(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver, SuperadminProjectPasswordCipher $passwordCipher): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $project = $this->superadminProjectRepository->find($id);
        if (!$project) {
            return $this->json(['message' => 'Projekt nicht gefunden'], 404);
        }

        $encrypted = $project->getLoginPasswordEncrypted();
        $password = null === $encrypted ? null : $passwordCipher->decrypt($encrypted);

        return $this->json(['data' => ['login_password' => $password]]);
    }

    #[Route('/operations-api/superadmin-projects', name: 'superadmin_portal_operations_api_superadmin_project_create', methods: ['POST'])]
    public function createSuperadminProject(Request $request, PortalRuntimeResolver $portalRuntimeResolver, SuperadminProjectPasswordCipher $passwordCipher): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $body = json_decode((string) $request->getContent(), true);
        $body = is_array($body) ? $body : [];

        $project = new \App\Entity\SuperadminProject();
        $project->setName((string) ($body['name'] ?? ''));
        $project->setUrl((string) ($body['url'] ?? ''));
        $project->setDescription((string) ($body['description'] ?? '') ?: null);
        $project->setLoginUsername((string) ($body['login_username'] ?? ''));
        $project->setActive((bool) ($body['active'] ?? true));
        $this->applyLoginPassword($project, $body, $passwordCipher);

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeSuperadminProject($project)], 201);
    }

    #[Route('/operations-api/superadmin-projects/{id}', name: 'superadmin_portal_operations_api_superadmin_project_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function updateSuperadminProject(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver, SuperadminProjectPasswordCipher $passwordCipher): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $project = $this->superadminProjectRepository->find($id);
        if (!$project) {
            return $this->json(['message' => 'Projekt nicht gefunden'], 404);
        }

        $body = json_decode((string) $request->getContent(), true);
        $body = is_array($body) ? $body : [];

        $project->setName((string) ($body['name'] ?? $project->getName()));
        $project->setUrl((string) ($body['url'] ?? $project->getUrl()));
        $project->setDescription((string) ($body['description'] ?? '') ?: null);
        $project->setLoginUsername((string) ($body['login_username'] ?? ''));
        if (isset($body['active'])) {
            $project->setActive((bool) $body['active']);
        }
        $this->applyLoginPassword($project, $body, $passwordCipher);

        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeSuperadminProject($project)]);
    }

    #[Route('/operations-api/superadmin-projects/{id}', name: 'superadmin_portal_operations_api_superadmin_project_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteSuperadminProject(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $project = $this->superadminProjectRepository->find($id);
        if (!$project) {
            return $this->json(['message' => 'Projekt nicht gefunden'], 404);
        }

        $this->entityManager->remove($project);
        $this->entityManager->flush();

        return $this->json(['deleted' => true]);
    }

    #[Route('/operations-api/superadmin-projects/{id}/upload-image', name: 'superadmin_portal_operations_api_superadmin_project_upload_image', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function uploadProjectImage(int $id, Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertOperationsRequest($request, $portalRuntimeResolver);

        $project = $this->superadminProjectRepository->find($id);
        if (!$project) {
            return $this->json(['message' => 'Projekt nicht gefunden'], 404);
        }

        $uploadedFile = $request->files->get('image');
        if (!$uploadedFile) {
            return $this->json(['message' => 'Keine Datei hochgeladen'], 400);
        }

        if (!str_starts_with($uploadedFile->getMimeType() ?? '', 'image/')) {
            return $this->json(['message' => 'Nur Bilder sind erlaubt'], 400);
        }

        if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
            return $this->json(['message' => 'Datei zu groß (max. 5MB)'], 400);
        }

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/projects';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $filename = $id . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move($uploadsDir, $filename);

        $project->setImagePath('/uploads/projects/' . $filename);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serializeSuperadminProject($project)]);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function applyLoginPassword(\App\Entity\SuperadminProject $project, array $body, SuperadminProjectPasswordCipher $passwordCipher): void
    {
        if (!array_key_exists('login_password', $body) || '' === (string) $body['login_password']) {
            return;
        }

        $project->setLoginPasswordEncrypted($passwordCipher->encrypt((string) $body['login_password']));
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSuperadminProject(\App\Entity\SuperadminProject $project): array
    {
        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'url' => $project->getUrl(),
            'description' => $project->getDescription(),
            'image_path' => $project->getImagePath(),
            'login_username' => $project->getLoginUsername(),
            'has_login_password' => null !== $project->getLoginPasswordEncrypted(),
            'active' => $project->isActive(),
            'created_at' => $project->getCreatedAt()->format('c'),
            'updated_at' => $project->getUpdatedAt()->format('c'),
        ];
    }
}
