<?php

namespace App\Controller\Admin;

use App\Entity\Auftrag;
use App\Repository\AuftragRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

final class OrderNotificationStreamController extends AbstractController
{
    public function __construct(
        private readonly AuftragRepository $auftragRepository,
        private readonly LoggerInterface $logger,
    )
    {
    }

    #[Route('/admin/notify/orders', name: 'admin_order_notifications_stream', methods: ['GET'])]
    public function __invoke(Request $request): StreamedResponse
    {
        $user = $this->getUser();

        // EventSource sends the last received "id" as a header when reconnecting.
        $lastEventIdHeader = $request->headers->get('Last-Event-ID');
        $lastEventId = is_numeric($lastEventIdHeader) ? (int) $lastEventIdHeader : null;

        // First connect: do not replay all existing orders, start from the latest ID.
        // We still emit a silent "bootstrap" event (empty summary) so EventSource sends Last-Event-ID on reconnects.
        $bootstrapId = null;
        if (null === $lastEventId) {
            $bootstrapId = $this->getLatestOrderId();
            $lastEventId = $bootstrapId;
        }

        $this->logger->info('Admin order notify stream connect', [
            'user' => $user ? (string) $user : null,
            'lastEventIdHeader' => $lastEventIdHeader,
            'lastEventId' => $lastEventId,
            'bootstrapId' => $bootstrapId,
            'host' => $request->getHttpHost(),
            'scheme' => $request->getScheme(),
            'xfp' => $request->headers->get('x-forwarded-proto'),
            'cfVisitor' => $request->headers->get('cf-visitor'),
            'uri' => $request->getUri(),
        ]);

        // Stream output starts during Response::sendContent(); close the session lock before that
        // to avoid late cookie/header writes on long-running SSE responses.
        if ($request->hasSession() && $request->getSession()->isStarted()) {
            $request->getSession()->save();
        }

        $response = new StreamedResponse(function () use ($lastEventId, $bootstrapId): void {
            // Avoid timeouts while we keep the SSE stream open.
            @set_time_limit(0);

            $retryMs = 15000;
            echo "retry: {$retryMs}\n\n";

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

                if ([] !== $orders) {
                    foreach ($orders as $order) {
                        if (!$order instanceof Auftrag) {
                            continue;
                        }

                        $payload = $this->buildNotificationPayload($order);

                        $id = (int) ($order->getId() ?? 0);
                        if ($id > $currentLastId) {
                            $currentLastId = $id;
                        }

                        echo 'id: ' . (string) $id . "\n";
                        echo 'data: ' . json_encode($payload, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) . "\n\n";

                        $this->logger->info('Admin order notify emitted', [
                            'orderId' => $id,
                            'orderNumber' => $order->getOrderNumber(),
                        ]);

                        if (function_exists('ob_flush')) {
                            @ob_flush();
                        }
                        @flush();
                    }

                    continue;
                }

                echo ": ping\n\n";
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                @flush();

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
     * @return array{summary: string, content: array<string, mixed>}
     */
    private function buildNotificationPayload(Auftrag $order): array
    {
        $summary = sprintf('Neuer Auftrag %s', $order->getOrderNumber());

        $bodyParts = [];
        $vehicleDisplay = trim((string) $order->getVehicleDisplay());
        if ('' !== $vehicleDisplay) {
            $bodyParts[] = $vehicleDisplay;
        } else {
            $manufacturer = trim((string) $order->getManufacturer());
            $model = trim((string) $order->getModel());
            $engine = trim((string) $order->getEngine());
            $built = trim((string) $order->getBuilt());

            $fallback = trim(implode(' ', array_filter([$manufacturer, $model, $engine])));
            if ('' !== $built) {
                $fallback = '' !== $fallback ? ($fallback . ' (' . $built . ')') : $built;
            }

            if ('' !== $fallback) {
                $bodyParts[] = $fallback;
            }
        }

        $stage = trim((string) $order->getStageLabel());
        if ('' !== $stage) {
            $bodyParts[] = $stage;
        }

        $customer = $order->getKunde();
        if (null !== $customer) {
            $customerLabel = trim((string) $customer);
            if ('' !== $customerLabel) {
                $bodyParts[] = $customerLabel;
            }
        }

        $body = implode(' | ', array_values(array_filter($bodyParts)));
        if ('' === $body) {
            $body = 'Ein neuer Auftrag ist eingegangen.';
        }

        return [
            'summary' => $summary,
            'content' => [
                'body' => $body,
                // Used by some browsers to collapse multiple updates for the same order.
                'tag' => (string) ($order->getId() ?? ''),
            ],
        ];
    }
}
