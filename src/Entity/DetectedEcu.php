<?php

namespace App\Entity;

use App\Repository\DetectedEcuRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetectedEcuRepository::class)]
#[ORM\Index(columns: ['status'])]
class DetectedEcu
{
    public const STATUS_NEW = 'new';
    public const STATUS_REVIEW = 'review';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_IGNORED = 'ignored';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_REVIEW,
        self::STATUS_CONFIRMED,
        self::STATUS_REJECTED,
        self::STATUS_IGNORED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ToolNewsItem $newsItem = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ToolProvider $provider = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $manufacturer = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $ecuFamily = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ecuName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $mcu = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $readMethod = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $protocol = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $vehicleHint = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $confidence = 0.0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rawText = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $matchData = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_NEW;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->ecuName ?? $this->ecuFamily ?? $this->manufacturer ?? ('Erkannte ECU #' . $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNewsItem(): ?ToolNewsItem
    {
        return $this->newsItem;
    }

    public function setNewsItem(?ToolNewsItem $newsItem): static
    {
        $this->newsItem = $newsItem;

        return $this;
    }

    public function getProvider(): ?ToolProvider
    {
        return $this->provider;
    }

    public function setProvider(?ToolProvider $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?string $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getEcuFamily(): ?string
    {
        return $this->ecuFamily;
    }

    public function setEcuFamily(?string $ecuFamily): static
    {
        $this->ecuFamily = $ecuFamily;

        return $this;
    }

    public function getEcuName(): ?string
    {
        return $this->ecuName;
    }

    public function setEcuName(?string $ecuName): static
    {
        $this->ecuName = $ecuName;

        return $this;
    }

    public function getMcu(): ?string
    {
        return $this->mcu;
    }

    public function setMcu(?string $mcu): static
    {
        $this->mcu = $mcu;

        return $this;
    }

    public function getReadMethod(): ?string
    {
        return $this->readMethod;
    }

    public function setReadMethod(?string $readMethod): static
    {
        $this->readMethod = $readMethod;

        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(?string $protocol): static
    {
        $this->protocol = $protocol;

        return $this;
    }

    public function getVehicleHint(): ?string
    {
        return $this->vehicleHint;
    }

    public function setVehicleHint(?string $vehicleHint): static
    {
        $this->vehicleHint = $vehicleHint;

        return $this;
    }

    public function getConfidence(): float
    {
        return $this->confidence;
    }

    public function setConfidence(float $confidence): static
    {
        $this->confidence = max(0.0, min(1.0, $confidence));

        return $this;
    }

    public function getRawText(): ?string
    {
        return $this->rawText;
    }

    public function setRawText(?string $rawText): static
    {
        $this->rawText = $rawText;

        return $this;
    }

    public function getMatchData(): ?array
    {
        return $this->matchData;
    }

    public function setMatchData(?array $matchData): static
    {
        $this->matchData = $matchData;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = in_array($status, self::STATUSES, true) ? $status : self::STATUS_NEW;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
