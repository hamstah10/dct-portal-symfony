<?php

namespace App\Service\Realtime;

use Psr\Log\LoggerInterface;

final class PortalRealtimePublisher
{
    public function __construct(
        private readonly PortalMercureEventStore $eventStore,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param string|list<string>     $topics
     * @param array<string, mixed>    $content
     */
    public function publish(array|string $topics, string $summary, array $content = []): void
    {
        $summary = trim($summary);
        if ('' === $summary) {
            return;
        }

        $payload = [
            'summary' => $summary,
            'content' => $this->normalizeContent($content),
        ];

        try {
            $json = json_encode($payload, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
            $this->eventStore->append((array) $topics, $json, true);
        } catch (\Throwable $exception) {
            $this->logger->warning('Mercure update could not be published.', [
                'summary' => $summary,
                'topics' => (array) $topics,
                'exception' => $exception,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $content
     *
     * @return array<string, mixed>
     */
    private function normalizeContent(array $content): array
    {
        $normalized = [];

        foreach ($content as $key => $value) {
            if (null === $value) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
                if ('' === $value) {
                    continue;
                }
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
