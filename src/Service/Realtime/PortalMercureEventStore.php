<?php

namespace App\Service\Realtime;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class PortalMercureEventStore
{
    private const MAX_EVENTS = 500;

    public function __construct(
        #[Autowire('%kernel.project_dir%/var/share')] private readonly string $shareDir,
    ) {
    }

    public function append(array $topics, string $data, bool $private = false, ?string $type = null, ?int $retry = null): string
    {
        $path = $this->getStoragePath();
        $directory = \dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Mercure-Speicherverzeichnis konnte nicht erstellt werden.');
        }

        $handle = fopen($path, 'c+');
        if (false === $handle) {
            throw new \RuntimeException('Mercure-Speicherdatei konnte nicht geoeffnet werden.');
        }

        try {
            if (!flock($handle, \LOCK_EX)) {
                throw new \RuntimeException('Mercure-Speicherdatei konnte nicht gesperrt werden.');
            }

            rewind($handle);
            $raw = stream_get_contents($handle);
            $state = $this->decodeState(\is_string($raw) ? $raw : '');

            $sequence = max(1, (int) ($state['next_sequence'] ?? 1));
            $event = [
                'sequence' => $sequence,
                'id' => (string) $sequence,
                'topics' => array_values(array_unique(array_filter(array_map(
                    static fn (mixed $topic): string => trim((string) $topic),
                    $topics
                )))),
                'data' => $data,
                'private' => $private,
                'type' => null === $type ? null : trim($type),
                'retry' => $retry,
                'publishedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ];

            $state['next_sequence'] = $sequence + 1;
            $state['events'][] = $event;

            if (\count($state['events']) > self::MAX_EVENTS) {
                $state['events'] = array_slice($state['events'], -self::MAX_EVENTS);
            }

            $json = json_encode($state, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, $json);
            fflush($handle);

            return $event['id'];
        } finally {
            flock($handle, \LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * @param list<string> $topics
     *
     * @return list<array{id:string,sequence:int,data:string,type:?string,retry:?int}>
     */
    public function fetchAfter(int $lastSequence, array $topics): array
    {
        $state = $this->readState();
        $normalizedTopics = array_values(array_unique(array_filter(array_map(
            static fn (mixed $topic): string => trim((string) $topic),
            $topics
        ))));

        $events = [];
        foreach ($state['events'] as $event) {
            if (!\is_array($event)) {
                continue;
            }

            $sequence = (int) ($event['sequence'] ?? 0);
            if ($sequence <= $lastSequence) {
                continue;
            }

            $eventTopics = array_values(array_filter(array_map('strval', $event['topics'] ?? [])));
            if ([] === array_intersect($normalizedTopics, $eventTopics)) {
                continue;
            }

            $events[] = [
                'id' => (string) ($event['id'] ?? $sequence),
                'sequence' => $sequence,
                'data' => (string) ($event['data'] ?? ''),
                'type' => isset($event['type']) && \is_string($event['type']) && '' !== trim($event['type']) ? trim($event['type']) : null,
                'retry' => isset($event['retry']) && is_numeric($event['retry']) ? (int) $event['retry'] : null,
            ];
        }

        return $events;
    }

    public function latestSequence(): int
    {
        $state = $this->readState();
        $nextSequence = (int) ($state['next_sequence'] ?? 1);

        return max(0, $nextSequence - 1);
    }

    /**
     * @return array{next_sequence:int,events:list<array<string,mixed>>}
     */
    private function readState(): array
    {
        return $this->withLockedState(static function (array $state): array {
            return $state;
        }, \LOCK_SH);
    }

    /**
     * @template T
     *
     * @param callable(array{next_sequence:int,events:list<array<string,mixed>>}):T $callback
     *
     * @return T
     */
    private function withLockedState(callable $callback, int $lockType): mixed
    {
        $path = $this->getStoragePath();
        $directory = \dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Mercure-Speicherverzeichnis konnte nicht erstellt werden.');
        }

        $handle = fopen($path, 'c+');
        if (false === $handle) {
            throw new \RuntimeException('Mercure-Speicherdatei konnte nicht geoeffnet werden.');
        }

        try {
            if (!flock($handle, $lockType)) {
                throw new \RuntimeException('Mercure-Speicherdatei konnte nicht gesperrt werden.');
            }

            rewind($handle);
            $raw = stream_get_contents($handle);
            $state = $this->decodeState(\is_string($raw) ? $raw : '');

            return $callback($state);
        } finally {
            flock($handle, \LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * @return array{next_sequence:int,events:list<array<string,mixed>>}
     */
    private function decodeState(string $raw): array
    {
        if ('' === trim($raw)) {
            return [
                'next_sequence' => 1,
                'events' => [],
            ];
        }

        $decoded = json_decode($raw, true);
        if (!\is_array($decoded)) {
            return [
                'next_sequence' => 1,
                'events' => [],
            ];
        }

        $events = [];
        foreach (($decoded['events'] ?? []) as $event) {
            if (\is_array($event)) {
                $events[] = $event;
            }
        }

        return [
            'next_sequence' => max(1, (int) ($decoded['next_sequence'] ?? 1)),
            'events' => $events,
        ];
    }

    private function getStoragePath(): string
    {
        return rtrim($this->shareDir, '/').'/mercure/events.json';
    }
}
