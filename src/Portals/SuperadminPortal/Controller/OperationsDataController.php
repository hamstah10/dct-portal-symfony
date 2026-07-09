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
use App\Entity\SuperadminRememberedVehicle;
use App\Repository\AuftragRepository;
use App\Repository\EmailKontoRepository;
use App\Repository\FirmaRepository;
use App\Repository\KundeRepository;
use App\Repository\SupportEmailMessageRepository;
use App\Repository\SupportTicketRepository;
use App\Repository\SuperadminCalendarEntryRepository;
use App\Repository\SuperadminRememberedVehicleRepository;
use App\Service\SupportEmailFetchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
        private readonly SupportEmailFetchService $supportEmailFetchService,
        private readonly EntityManagerInterface $entityManager,
    ) {
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
            'items' => $items,
            'customer_id' => $customer?->getId(),
            'customer_name' => $customerName,
            'customer_email' => $customer?->getEmail() ?? $order->getCustomerEmail() ?? '',
            'company_id' => $company?->getId(),
            'company_name' => $company?->getName() ?? $customer?->getFirmenname(),
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
}
