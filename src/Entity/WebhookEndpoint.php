<?php

namespace App\Entity;

use App\Repository\WebhookEndpointRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebhookEndpointRepository::class)]
#[ORM\Table(name: 'webhook_endpoint')]
class WebhookEndpoint
{
    public const EVENT_ORDER_CREATED = 'order.created';
    public const EVENT_ORDER_STATUS_CHANGED = 'order.status_changed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $name = '';

    #[ORM\Column(name: 'target_url', length: 500)]
    private string $targetUrl = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secret = null;

    #[ORM\Column(name: 'subscribed_events', type: Types::JSON)]
    private array $subscribedEvents = [self::EVENT_ORDER_CREATED];

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(name: 'timeout_seconds')]
    private int $timeoutSeconds = 10;

    #[ORM\Column(name: 'verify_ssl')]
    private bool $verifySsl = true;

    #[ORM\Column(name: 'last_delivery_at', nullable: true)]
    private ?\DateTimeImmutable $lastDeliveryAt = null;

    #[ORM\Column(name: 'last_http_status', nullable: true)]
    private ?int $lastHttpStatus = null;

    #[ORM\Column(name: 'last_error', type: Types::TEXT, nullable: true)]
    private ?string $lastError = null;

    #[ORM\Column(name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('Webhook #' . ($this->id ?? 0));
    }

    /**
     * @return array<string, string>
     */
    public static function getAvailableEventsChoices(): array
    {
        return [
            'Auftrag erstellt (order.created)' => self::EVENT_ORDER_CREATED,
            'Auftragsstatus geaendert (order.status_changed)' => self::EVENT_ORDER_STATUS_CHANGED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getAvailableEvents(): array
    {
        return array_values(self::getAvailableEventsChoices());
    }

    public function supportsEvent(string $eventName): bool
    {
        return in_array(strtolower(trim($eventName)), $this->getSubscribedEvents(), true);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    public function setTargetUrl(string $targetUrl): static
    {
        $this->targetUrl = trim($targetUrl);

        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): static
    {
        $normalized = null !== $secret ? trim($secret) : null;
        $this->secret = '' === (string) $normalized ? null : $normalized;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getSubscribedEvents(): array
    {
        return $this->normalizeEvents($this->subscribedEvents);
    }

    /**
     * @param array<int, string> $subscribedEvents
     */
    public function setSubscribedEvents(array $subscribedEvents): static
    {
        $this->subscribedEvents = $this->normalizeEvents($subscribedEvents);

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getTimeoutSeconds(): int
    {
        return $this->timeoutSeconds;
    }

    public function setTimeoutSeconds(int $timeoutSeconds): static
    {
        $this->timeoutSeconds = max(1, min(120, $timeoutSeconds));

        return $this;
    }

    public function isVerifySsl(): bool
    {
        return $this->verifySsl;
    }

    public function setVerifySsl(bool $verifySsl): static
    {
        $this->verifySsl = $verifySsl;

        return $this;
    }

    public function getLastDeliveryAt(): ?\DateTimeImmutable
    {
        return $this->lastDeliveryAt;
    }

    public function setLastDeliveryAt(?\DateTimeImmutable $lastDeliveryAt): static
    {
        $this->lastDeliveryAt = $lastDeliveryAt;

        return $this;
    }

    public function getLastHttpStatus(): ?int
    {
        return $this->lastHttpStatus;
    }

    public function setLastHttpStatus(?int $lastHttpStatus): static
    {
        $this->lastHttpStatus = $lastHttpStatus;

        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $lastError): static
    {
        $this->lastError = $lastError;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @param array<int, string> $events
     * @return array<int, string>
     */
    private function normalizeEvents(array $events): array
    {
        $available = self::getAvailableEvents();
        $normalized = [];

        foreach ($events as $event) {
            $value = strtolower(trim((string) $event));
            if ('' === $value) {
                continue;
            }
            if (!in_array($value, $available, true)) {
                continue;
            }
            $normalized[] = $value;
        }

        $normalized = array_values(array_unique($normalized));

        if ([] === $normalized) {
            return [self::EVENT_ORDER_CREATED];
        }

        return $normalized;
    }
}
