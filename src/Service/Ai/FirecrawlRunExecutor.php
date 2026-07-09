<?php

namespace App\Service\Ai;

use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\Bridge\Firecrawl\Firecrawl;
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\PlatformInterface;

final class FirecrawlRunExecutor
{
    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly Firecrawl $firecrawl,
        private readonly LoggerInterface $logger,
        private readonly FirecrawlRunStorage $storage,
    ) {
    }

    /**
     * @param null|callable(int, int, string): void $progressCallback
     */
    public function execute(int $runId, ?callable $progressCallback = null): void
    {
        $run = $this->storage->getRun($runId);
        if (!is_array($run)) {
            throw new \RuntimeException(sprintf('Firecrawl-Run mit ID %d nicht gefunden.', $runId));
        }

        $action = (string) ($run['action'] ?? 'scrape');
        $url = trim((string) ($run['url'] ?? ''));
        $instruction = trim((string) ($run['instruction'] ?? ''));
        $model = trim((string) ($run['model'] ?? ''));
        $progressMax = max(1, (int) ($run['progressMax'] ?? FirecrawlRunStorage::DEFAULT_PROGRESS_MAX));
        $response = null;
        $metadata = null;
        $mapLinks = [];

        if ('' === $url) {
            $this->storage->failRun($runId, 'Keine URL fuer den Firecrawl-Run gespeichert.');

            return;
        }

        if ('' === $model) {
            $this->storage->failRun($runId, 'Kein Modell fuer den Firecrawl-Run gespeichert.');

            return;
        }

        try {
            $this->pushProgress($runId, 1, $progressMax, 'Initialisiere Firecrawl und Agent', $progressCallback, time());

            $toolbox = new Toolbox([$this->firecrawl], logger: $this->logger);
            $toolProcessor = new AgentProcessor($toolbox, includeSources: true);
            $agent = new Agent($this->platform, $model, inputProcessors: [$toolProcessor], outputProcessors: [$toolProcessor]);

            $this->pushProgress($runId, 2, $progressMax, $this->buildRunningMessage($action), $progressCallback);

            $messages = new MessageBag(Message::ofUser(sprintf(
                "%s\n\n%s",
                $this->buildPromptPrefix($action, $url),
                $instruction
            )));

            $result = $agent->call($messages);

            $this->pushProgress($runId, max(1, $progressMax - 1), $progressMax, 'Verarbeite Antwort', $progressCallback);

            $response = method_exists($result, 'getContent') ? $result->getContent() : (string) $result;
            $metadata = method_exists($result, 'getMetadata') ? $result->getMetadata()->all() : null;

            if ('map' === $action) {
                $mapLinks = $this->extractLinksFromResult($response);
                if ([] === $mapLinks) {
                    $mapLinks = $this->extractLinksFromMetadata($metadata);
                }
            }

            $this->storage->completeRun($runId, $response, $metadata, $mapLinks);
            $this->notifyProgress($progressCallback, $progressMax, $progressMax, 'Abgeschlossen');
        } catch (\Throwable $exception) {
            $this->storage->failRun($runId, $exception->getMessage(), $response, $metadata, $mapLinks);
            $this->notifyProgress($progressCallback, $progressMax, $progressMax, 'Fehlgeschlagen');

            throw $exception;
        }
    }

    /**
     * @param null|callable(int, int, string): void $progressCallback
     */
    private function pushProgress(
        int $runId,
        int $current,
        int $max,
        string $label,
        ?callable $progressCallback,
        ?int $startedAt = null,
    ): void {
        $this->storage->updateProgress($runId, FirecrawlRunStorage::STATUS_RUNNING, $current, $max, $label, $startedAt);
        $this->notifyProgress($progressCallback, $current, $max, $label);
    }

    /**
     * @param null|callable(int, int, string): void $progressCallback
     */
    private function notifyProgress(?callable $progressCallback, int $current, int $max, string $label): void
    {
        if (null === $progressCallback) {
            return;
        }

        $progressCallback($current, $max, $label);
    }

    private function buildPromptPrefix(string $action, string $url): string
    {
        return match ($action) {
            'crawl' => sprintf('Crawl the following URL: %s', $url),
            'map' => sprintf('Map the following URL: %s', $url),
            default => sprintf('Scrape the following URL: %s', $url),
        };
    }

    private function buildRunningMessage(string $action): string
    {
        return match ($action) {
            'crawl' => 'Firecrawl crawl laeuft im Hintergrund',
            'map' => 'Firecrawl map laeuft im Hintergrund',
            default => 'Firecrawl scrape laeuft im Hintergrund',
        };
    }

    /**
     * @return list<string>
     */
    private function extractLinksFromResult(?string $response): array
    {
        if (null === $response) {
            return [];
        }

        $count = preg_match_all('~https?://\\S+~i', $response, $matches);
        if (false === $count || 0 === $count) {
            return [];
        }

        $links = array_map(static function (string $url): string {
            return rtrim($url, " \t\n\r\0\x0B,.)]");
        }, $matches[0]);

        return array_values(array_unique(array_filter($links, static fn (string $url): bool => '' !== trim($url))));
    }

    /**
     * @param array<string, mixed>|null $metadata
     *
     * @return list<string>
     */
    private function extractLinksFromMetadata(?array $metadata): array
    {
        if (!is_array($metadata)) {
            return [];
        }

        foreach ($metadata as $value) {
            if (is_array($value) && $this->looksLikeLinkList($value)) {
                return array_values(array_unique(array_filter(array_map('strval', $value))));
            }
        }

        return [];
    }

    /**
     * @param array<mixed> $value
     */
    private function looksLikeLinkList(array $value): bool
    {
        if ([] === $value) {
            return false;
        }

        foreach ($value as $item) {
            if (!is_string($item) || !str_starts_with($item, 'http')) {
                return false;
            }
        }

        return true;
    }
}
