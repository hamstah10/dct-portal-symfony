<?php

namespace App\Entity;

use App\Repository\VehicleCoverageMatchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleCoverageMatchRepository::class)]
#[ORM\Index(columns: ['status'])]
#[ORM\Index(columns: ['match_level'])]
class VehicleCoverageMatch
{
    public const LEVEL_POSSIBLE = 'possible';
    public const LEVEL_LIKELY = 'likely';
    public const LEVEL_STRONG = 'strong';
    public const LEVEL_EXACT = 'exact';

    /** @var list<string> */
    public const LEVELS = [
        self::LEVEL_POSSIBLE,
        self::LEVEL_LIKELY,
        self::LEVEL_STRONG,
        self::LEVEL_EXACT,
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_REVIEW = 'review';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_IGNORED = 'ignored';
    public const STATUS_PUBLISHED = 'published';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_REVIEW,
        self::STATUS_CONFIRMED,
        self::STATUS_REJECTED,
        self::STATUS_IGNORED,
        self::STATUS_PUBLISHED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?DetectedEcu $detectedEcu = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ToolNewsItem $newsItem = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ToolProvider $provider = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $vehicleType = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $vehicleBrand = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $vehicleSeries = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $vehicleModel = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $vehicleEngine = null;

    #[ORM\Column(nullable: true)]
    private ?int $vehicleEngineId = null;

    #[ORM\Column(nullable: true)]
    private ?int $vehicleModelId = null;

    #[ORM\Column(nullable: true)]
    private ?int $vehicleSeriesId = null;

    #[ORM\Column(nullable: true)]
    private ?int $vehicleBrandId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $engineCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ecuName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $ecuFamily = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $mcu = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $fuel = null;

    #[ORM\Column(nullable: true)]
    private ?int $hp = null;

    #[ORM\Column(nullable: true)]
    private ?int $kw = null;

    #[ORM\Column(nullable: true)]
    private ?int $nm = null;

    #[ORM\Column(nullable: true)]
    private ?int $yearFrom = null;

    #[ORM\Column(nullable: true)]
    private ?int $yearTo = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $matchScore = 0.0;

    #[ORM\Column(length: 20)]
    private string $matchLevel = self::LEVEL_POSSIBLE;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $matchReason = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDetectedEcu(): ?DetectedEcu
    {
        return $this->detectedEcu;
    }

    public function setDetectedEcu(?DetectedEcu $detectedEcu): static
    {
        $this->detectedEcu = $detectedEcu;

        return $this;
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

    public function getVehicleType(): ?string
    {
        return $this->vehicleType;
    }

    public function setVehicleType(?string $vehicleType): static
    {
        $this->vehicleType = $vehicleType;

        return $this;
    }

    public function getVehicleBrand(): ?string
    {
        return $this->vehicleBrand;
    }

    public function setVehicleBrand(?string $vehicleBrand): static
    {
        $this->vehicleBrand = $vehicleBrand;

        return $this;
    }

    public function getVehicleSeries(): ?string
    {
        return $this->vehicleSeries;
    }

    public function setVehicleSeries(?string $vehicleSeries): static
    {
        $this->vehicleSeries = $vehicleSeries;

        return $this;
    }

    public function getVehicleModel(): ?string
    {
        return $this->vehicleModel;
    }

    public function setVehicleModel(?string $vehicleModel): static
    {
        $this->vehicleModel = $vehicleModel;

        return $this;
    }

    public function getVehicleEngine(): ?string
    {
        return $this->vehicleEngine;
    }

    public function setVehicleEngine(?string $vehicleEngine): static
    {
        $this->vehicleEngine = $vehicleEngine;

        return $this;
    }

    public function getVehicleEngineId(): ?int
    {
        return $this->vehicleEngineId;
    }

    public function setVehicleEngineId(?int $vehicleEngineId): static
    {
        $this->vehicleEngineId = $vehicleEngineId;

        return $this;
    }

    public function getVehicleModelId(): ?int
    {
        return $this->vehicleModelId;
    }

    public function setVehicleModelId(?int $vehicleModelId): static
    {
        $this->vehicleModelId = $vehicleModelId;

        return $this;
    }

    public function getVehicleSeriesId(): ?int
    {
        return $this->vehicleSeriesId;
    }

    public function setVehicleSeriesId(?int $vehicleSeriesId): static
    {
        $this->vehicleSeriesId = $vehicleSeriesId;

        return $this;
    }

    public function getVehicleBrandId(): ?int
    {
        return $this->vehicleBrandId;
    }

    public function setVehicleBrandId(?int $vehicleBrandId): static
    {
        $this->vehicleBrandId = $vehicleBrandId;

        return $this;
    }

    public function getEngineCode(): ?string
    {
        return $this->engineCode;
    }

    public function setEngineCode(?string $engineCode): static
    {
        $this->engineCode = $engineCode;

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

    public function getEcuFamily(): ?string
    {
        return $this->ecuFamily;
    }

    public function setEcuFamily(?string $ecuFamily): static
    {
        $this->ecuFamily = $ecuFamily;

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

    public function getFuel(): ?string
    {
        return $this->fuel;
    }

    public function setFuel(?string $fuel): static
    {
        $this->fuel = $fuel;

        return $this;
    }

    public function getHp(): ?int
    {
        return $this->hp;
    }

    public function setHp(?int $hp): static
    {
        $this->hp = $hp;

        return $this;
    }

    public function getKw(): ?int
    {
        return $this->kw;
    }

    public function setKw(?int $kw): static
    {
        $this->kw = $kw;

        return $this;
    }

    public function getNm(): ?int
    {
        return $this->nm;
    }

    public function setNm(?int $nm): static
    {
        $this->nm = $nm;

        return $this;
    }

    public function getYearFrom(): ?int
    {
        return $this->yearFrom;
    }

    public function setYearFrom(?int $yearFrom): static
    {
        $this->yearFrom = $yearFrom;

        return $this;
    }

    public function getYearTo(): ?int
    {
        return $this->yearTo;
    }

    public function setYearTo(?int $yearTo): static
    {
        $this->yearTo = $yearTo;

        return $this;
    }

    public function getMatchScore(): float
    {
        return $this->matchScore;
    }

    public function setMatchScore(float $matchScore): static
    {
        $this->matchScore = max(0.0, min(1.0, $matchScore));

        return $this;
    }

    public function getMatchLevel(): string
    {
        return $this->matchLevel;
    }

    public function setMatchLevel(string $matchLevel): static
    {
        $this->matchLevel = in_array($matchLevel, self::LEVELS, true) ? $matchLevel : self::LEVEL_POSSIBLE;

        return $this;
    }

    public function getMatchReason(): ?string
    {
        return $this->matchReason;
    }

    public function setMatchReason(?string $matchReason): static
    {
        $this->matchReason = $matchReason;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMatchData(): ?array
    {
        return $this->matchData;
    }

    /**
     * @param array<string, mixed>|null $matchData
     */
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
