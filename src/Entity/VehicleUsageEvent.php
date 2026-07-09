<?php

namespace App\Entity;

use App\Repository\VehicleUsageEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleUsageEventRepository::class)]
#[ORM\Table(name: 'vehicle_usage_event')]
class VehicleUsageEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'event_type', length: 120)]
    private string $eventType = '';

    #[ORM\Column(name: 'event_source', length: 40)]
    private string $eventSource = 'website';

    #[ORM\Column(name: 'application_name', length: 150, nullable: true)]
    private ?string $applicationName = null;

    #[ORM\Column(name: 'route_name', length: 190, nullable: true)]
    private ?string $routeName = null;

    #[ORM\Column(name: 'request_uri', length: 500, nullable: true)]
    private ?string $requestUri = null;

    #[ORM\Column(name: 'http_method', length: 10)]
    private string $httpMethod = 'GET';

    #[ORM\Column(name: 'ip_address', length: 64, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(name: 'user_agent', length: 500, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $referrer = null;

    #[ORM\Column(name: 'session_id', length: 190, nullable: true)]
    private ?string $sessionId = null;

    #[ORM\Column(name: 'vehicle_type_id', nullable: true)]
    private ?int $vehicleTypeId = null;

    #[ORM\Column(name: 'brand_id', nullable: true)]
    private ?int $brandId = null;

    #[ORM\Column(name: 'series_id', nullable: true)]
    private ?int $seriesId = null;

    #[ORM\Column(name: 'model_id', nullable: true)]
    private ?int $modelId = null;

    #[ORM\Column(name: 'engine_id', nullable: true)]
    private ?int $engineId = null;

    #[ORM\Column(name: 'stage_id', nullable: true)]
    private ?int $stageId = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $payload = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getEventSource(): string
    {
        return $this->eventSource;
    }

    public function setEventSource(string $eventSource): static
    {
        $this->eventSource = $eventSource;

        return $this;
    }

    public function getApplicationName(): ?string
    {
        return $this->applicationName;
    }

    public function setApplicationName(?string $applicationName): static
    {
        $this->applicationName = $applicationName;

        return $this;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(?string $routeName): static
    {
        $this->routeName = $routeName;

        return $this;
    }

    public function getRequestUri(): ?string
    {
        return $this->requestUri;
    }

    public function setRequestUri(?string $requestUri): static
    {
        $this->requestUri = $requestUri;

        return $this;
    }

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    public function setHttpMethod(string $httpMethod): static
    {
        $this->httpMethod = $httpMethod;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }

    public function setReferrer(?string $referrer): static
    {
        $this->referrer = $referrer;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): static
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getVehicleTypeId(): ?int
    {
        return $this->vehicleTypeId;
    }

    public function setVehicleTypeId(?int $vehicleTypeId): static
    {
        $this->vehicleTypeId = $vehicleTypeId;

        return $this;
    }

    public function getBrandId(): ?int
    {
        return $this->brandId;
    }

    public function setBrandId(?int $brandId): static
    {
        $this->brandId = $brandId;

        return $this;
    }

    public function getSeriesId(): ?int
    {
        return $this->seriesId;
    }

    public function setSeriesId(?int $seriesId): static
    {
        $this->seriesId = $seriesId;

        return $this;
    }

    public function getModelId(): ?int
    {
        return $this->modelId;
    }

    public function setModelId(?int $modelId): static
    {
        $this->modelId = $modelId;

        return $this;
    }

    public function getEngineId(): ?int
    {
        return $this->engineId;
    }

    public function setEngineId(?int $engineId): static
    {
        $this->engineId = $engineId;

        return $this;
    }

    public function getStageId(): ?int
    {
        return $this->stageId;
    }

    public function setStageId(?int $stageId): static
    {
        $this->stageId = $stageId;

        return $this;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }
}
