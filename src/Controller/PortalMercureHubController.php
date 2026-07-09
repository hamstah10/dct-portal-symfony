<?php

namespace App\Controller;

use App\Portals\Chiptuningfile\Security\PortalCustomerUser;
use App\Service\Realtime\PortalMercureEventStore;
use App\Service\Realtime\PortalMercureJwtVerifier;
use App\Service\Realtime\PortalMercureTopics;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

final class PortalMercureHubController extends AbstractController
{
    private const INITIAL_STREAM_PADDING_BYTES = 8192;

    public function __construct(
        private readonly PortalMercureEventStore $eventStore,
        private readonly PortalMercureJwtVerifier $jwtVerifier,
        private readonly PortalMercureTopics $topics,
    ) {
    }

    #[Route('/mercure/hub', name: 'app_portal_mercure_hub', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        if ($this->container->has('profiler')) {
            $profiler = $this->container->get('profiler');
            if (\is_object($profiler) && method_exists($profiler, 'disable')) {
                $profiler->disable();
            }
        }

        if ($request->isMethod('POST')) {
            return $this->publish($request);
        }

        return $this->subscribe($request);
    }

    #[Route('/mercure/poll', name: 'app_portal_mercure_poll', methods: ['GET'])]
    public function poll(Request $request): JsonResponse
    {
        $topics = $this->extractTopics($request);
        if ([] === $topics) {
            return $this->json([
                'latestSequence' => $this->eventStore->latestSequence(),
                'events' => [],
            ]);
        }

        $this->assertSubscriptionAllowed($topics);

        $after = max(
            $this->normalizeEventSequence($request->query->get('after')),
            $this->normalizeEventSequence($request->headers->get('Last-Event-ID'))
        );

        $events = array_map(function (array $event): array {
            $payload = json_decode($event['data'], true);

            return [
                'id' => (string) $event['id'],
                'sequence' => (int) $event['sequence'],
                'type' => $event['type'],
                'retry' => $event['retry'],
                'payload' => \is_array($payload) ? $payload : null,
            ];
        }, $this->eventStore->fetchAfter($after, $topics));

        return $this->json([
            'latestSequence' => $this->eventStore->latestSequence(),
            'events' => $events,
        ]);
    }

    private function publish(Request $request): Response
    {
        $token = $this->extractBearerToken($request);
        if (!$this->jwtVerifier->isValid($token)) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED, $this->defaultHeaders());
        }

        $topics = $this->extractTopics($request);
        if ([] === $topics) {
            return new Response('Missing topic parameter.', Response::HTTP_BAD_REQUEST, $this->defaultHeaders());
        }

        $data = trim((string) $request->request->get('data', ''));
        if ('' === $data) {
            return new Response('Missing data parameter.', Response::HTTP_BAD_REQUEST, $this->defaultHeaders());
        }

        $eventId = $this->eventStore->append(
            $topics,
            $data,
            $request->request->has('private'),
            $this->stringOrNull($request->request->get('type')),
            $this->intOrNull($request->request->get('retry')),
        );

        return new Response($eventId, Response::HTTP_OK, array_merge($this->defaultHeaders(), [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]));
    }

    private function subscribe(Request $request): StreamedResponse
    {
        $topics = $this->extractTopics($request);
        if ([] === $topics) {
            throw $this->createNotFoundException();
        }

        $this->assertSubscriptionAllowed($topics);

        $lastEventIdHeader = $request->headers->get('Last-Event-ID');
        $lastEventIdQuery = $request->query->get('lastEventID', $request->query->get('Last-Event-ID'));
        $lastSequence = max(
            $this->normalizeEventSequence($lastEventIdHeader),
            $this->normalizeEventSequence($lastEventIdQuery)
        );

        $bootstrapSequence = null;
        if (0 === $lastSequence) {
            $bootstrapSequence = $this->eventStore->latestSequence();
            $lastSequence = $bootstrapSequence;
        }

        // Stream output starts during Response::sendContent(); close the session lock before that
        // to avoid late cookie/header writes on long-running SSE responses.
        if ($request->hasSession() && $request->getSession()->isStarted()) {
            $request->getSession()->save();
        }

        $response = new StreamedResponse(function () use ($topics, $lastSequence, $bootstrapSequence): void {
            @set_time_limit(0);
            @ignore_user_abort(true);
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            @ob_implicit_flush(true);

            echo "retry: 15000\n\n";
            echo ': '.str_repeat(' ', self::INITIAL_STREAM_PADDING_BYTES)."\n\n";
            $this->flushOutput();

            if (null !== $bootstrapSequence) {
                echo 'id: '.$bootstrapSequence."\n";
                echo "data: {\"summary\":\"\",\"content\":{}}\n\n";
                $this->flushOutput();
            }

            $currentSequence = $lastSequence;
            $deadline = microtime(true) + 55.0;

            while (microtime(true) < $deadline) {
                if (connection_aborted()) {
                    break;
                }

                $events = $this->eventStore->fetchAfter($currentSequence, $topics);
                if ([] !== $events) {
                    foreach ($events as $event) {
                        $currentSequence = max($currentSequence, (int) $event['sequence']);

                        if (null !== $event['retry']) {
                            echo 'retry: '.$event['retry']."\n";
                        }

                        if (null !== $event['type']) {
                            echo 'event: '.$event['type']."\n";
                        }

                        echo 'id: '.$event['id']."\n";
                        echo 'data: '.$event['data']."\n\n";
                        $this->flushOutput();
                    }

                    continue;
                }

                echo ": ping\n\n";
                $this->flushOutput();
                usleep(2_000_000);
            }
        });

        foreach ($this->defaultHeaders() as $name => $value) {
            $response->headers->set($name, $value);
        }

        $response->headers->set('Content-Type', 'text/event-stream; charset=UTF-8');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, no-transform');
        $response->headers->set('Content-Encoding', 'none');

        return $response;
    }

    /**
     * @return list<string>
     */
    private function extractTopics(Request $request): array
    {
        $topics = [];

        $requestPayload = $request->request->all();
        $queryPayload = $request->query->all();

        foreach ([$requestPayload['topic'] ?? null, $queryPayload['topic'] ?? null] as $candidate) {
            if (\is_array($candidate)) {
                $topics = $candidate;
                break;
            }

            if (\is_string($candidate) && '' !== trim($candidate)) {
                $topics = [$candidate];
                break;
            }
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $topic): string => trim((string) $topic),
            $topics
        ))));
    }

    /**
     * @param list<string> $topics
     */
    private function assertSubscriptionAllowed(array $topics): void
    {
        foreach ($topics as $topic) {
            if (preg_match('#^tfd/portal/customer/(\d+)/orders$#', $topic, $matches) === 1) {
                $requestedCustomerId = (int) $matches[1];
                $user = $this->getUser();

                if ($user instanceof PortalCustomerUser) {
                    $currentCustomerId = (int) ($user->getCustomer()->getId() ?? 0);
                    if ($requestedCustomerId !== $currentCustomerId) {
                        $this->denyAccessUnlessGranted('ROLE_ADMIN');
                    }

                    continue;
                }

                $this->denyAccessUnlessGranted('ROLE_ADMIN');

                continue;
            }

            if (
                $topic === PortalMercureTopics::PORTAL_ADMIN
                || $topic === PortalMercureTopics::PORTAL_INCOMING
                || str_starts_with($topic, 'tfd/portal/ai-project/')
            ) {
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function defaultHeaders(): array
    {
        return [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];
    }

    private function extractBearerToken(Request $request): ?string
    {
        $header = trim((string) $request->headers->get('Authorization', ''));
        if ('' !== $header && preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function normalizeEventSequence(mixed $value): int
    {
        return is_numeric($value) ? max(0, (int) $value) : 0;
    }

    private function flushOutput(): void
    {
        if (function_exists('ob_flush')) {
            @ob_flush();
        }

        @flush();
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (!\is_string($value)) {
            return null;
        }

        $value = trim($value);

        return '' !== $value ? $value : null;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
