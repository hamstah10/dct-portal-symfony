<?php

namespace App\Service\Analytics;

use App\Entity\VehicleUsageEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

final class VehicleUsageTracker
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function track(Request $request, string $eventType, array $context = []): void
    {
        $normalizedEventType = $this->normalizeEventType($eventType);
        $event = (new VehicleUsageEvent())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setEventType($normalizedEventType)
            ->setEventSource($this->truncate((string) ($context['source'] ?? 'website'), 40))
            ->setApplicationName($this->nullableString($context['applicationName'] ?? null, 150))
            ->setRouteName($this->nullableString($request->attributes->get('_route'), 190))
            ->setRequestUri($this->truncate((string) $request->getRequestUri(), 500))
            ->setHttpMethod($this->truncate(strtoupper((string) $request->getMethod()), 10))
            ->setIpAddress($this->nullableString($request->getClientIp(), 64))
            ->setUserAgent($this->nullableString($request->headers->get('User-Agent'), 500))
            ->setReferrer($this->nullableString($request->headers->get('Referer'), 500))
            ->setSessionId($this->resolveSessionId($request))
            ->setVehicleTypeId($this->toNullableInt($context['vehicleTypeId'] ?? $context['typeId'] ?? null))
            ->setBrandId($this->toNullableInt($context['brandId'] ?? null))
            ->setSeriesId($this->toNullableInt($context['seriesId'] ?? null))
            ->setModelId($this->toNullableInt($context['modelId'] ?? null))
            ->setEngineId($this->toNullableInt($context['engineId'] ?? null))
            ->setStageId($this->toNullableInt($context['stageId'] ?? null));

        $payload = $this->buildPayload($context);
        $event->setPayload([] === $payload ? null : $payload);

        try {
            $this->entityManager->persist($event);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            $this->logger->error('Vehicle usage event could not be persisted.', [
                'eventType' => $normalizedEventType,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function normalizeEventType(string $eventType): string
    {
        $normalized = strtolower(trim($eventType));
        if ('' === $normalized) {
            return 'vdb.unknown';
        }

        if (preg_match('/^[a-z0-9._:-]{2,120}$/', $normalized) !== 1) {
            return 'vdb.unknown';
        }

        return $normalized;
    }

    private function resolveSessionId(Request $request): ?string
    {
        try {
            if ($request->hasSession() && $request->getSession()->isStarted()) {
                return $this->nullableString($request->getSession()->getId(), 190);
            }
        } catch (\Throwable) {
            // fallback to cookie below
        }

        $sessionCookieName = session_name();
        if ('' !== $sessionCookieName) {
            return $this->nullableString($request->cookies->get($sessionCookieName), 190);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function buildPayload(array $context): array
    {
        unset(
            $context['source'],
            $context['applicationName'],
            $context['vehicleTypeId'],
            $context['typeId'],
            $context['brandId'],
            $context['seriesId'],
            $context['modelId'],
            $context['engineId'],
            $context['stageId']
        );

        $payload = [];
        foreach ($context as $key => $value) {
            if (!is_string($key) || '' === trim($key)) {
                continue;
            }

            if (!is_scalar($value) && !is_array($value) && null !== $value) {
                continue;
            }

            $payload[$key] = $value;
        }

        return $payload;
    }

    private function toNullableInt(mixed $value): ?int
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (is_bool($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        $intValue = (int) $value;

        return $intValue > 0 ? $intValue : null;
    }

    private function nullableString(mixed $value, int $maxLength): ?string
    {
        if (null === $value) {
            return null;
        }

        $stringValue = trim((string) $value);
        if ('' === $stringValue) {
            return null;
        }

        return $this->truncate($stringValue, $maxLength);
    }

    private function truncate(string $value, int $maxLength): string
    {
        if ($maxLength <= 0) {
            return '';
        }

        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength);
    }
}
