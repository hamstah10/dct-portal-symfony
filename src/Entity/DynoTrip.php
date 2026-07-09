<?php

namespace App\Entity;

use App\Repository\DynoTripRepository;
use App\Util\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DynoTripRepository::class)]
#[ORM\Table(name: 'dyno_trip')]
class DynoTrip
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\ManyToOne(inversedBy: 'trips')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?DynoVehicle $vehicle = null;

    #[ORM\Column(name: 'vehicle_name', length: 255, nullable: true)]
    private ?string $vehicleName = null;

    #[ORM\Column(name: 'start_time', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $startTime;

    #[ORM\Column(name: 'end_time', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $endTime;

    #[ORM\Column(name: 'duration_seconds')]
    private int $durationSeconds = 0;

    #[ORM\Column(name: 'distance_km', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $distanceKm = '0.00';

    #[ORM\Column(name: 'max_speed_kmh', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $maxSpeedKmh = '0.00';

    #[ORM\Column(name: 'avg_speed_kmh', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $avgSpeedKmh = '0.00';

    #[ORM\Column(name: 'max_rpm', nullable: true)]
    private ?int $maxRpm = null;

    #[ORM\Column(name: 'max_nm', nullable: true)]
    private ?int $maxNm = null;

    #[ORM\Column(name: 'max_ps', nullable: true)]
    private ?int $maxPs = null;

    #[ORM\Column(name: 'drive_mode', length: 60, nullable: true)]
    private ?string $driveMode = null;

    #[ORM\Column(name: 'data_points', type: Types::JSON, nullable: true)]
    private ?array $dataPoints = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->id = Uuid::v4();
        $this->startTime = $now;
        $this->endTime = $now;
        $this->createdAt = $now;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getVehicle(): ?DynoVehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?DynoVehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getVehicleName(): ?string
    {
        return $this->vehicleName;
    }

    public function setVehicleName(?string $vehicleName): static
    {
        $this->vehicleName = $vehicleName;

        return $this;
    }

    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): \DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getDurationSeconds(): int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(int $durationSeconds): static
    {
        $this->durationSeconds = max(0, $durationSeconds);

        return $this;
    }

    public function getDistanceKm(): string
    {
        return $this->distanceKm;
    }

    public function setDistanceKm(string $distanceKm): static
    {
        $this->distanceKm = $distanceKm;

        return $this;
    }

    public function getMaxSpeedKmh(): string
    {
        return $this->maxSpeedKmh;
    }

    public function setMaxSpeedKmh(string $maxSpeedKmh): static
    {
        $this->maxSpeedKmh = $maxSpeedKmh;

        return $this;
    }

    public function getAvgSpeedKmh(): string
    {
        return $this->avgSpeedKmh;
    }

    public function setAvgSpeedKmh(string $avgSpeedKmh): static
    {
        $this->avgSpeedKmh = $avgSpeedKmh;

        return $this;
    }

    public function getMaxRpm(): ?int
    {
        return $this->maxRpm;
    }

    public function setMaxRpm(?int $maxRpm): static
    {
        $this->maxRpm = $maxRpm;

        return $this;
    }

    public function getMaxNm(): ?int
    {
        return $this->maxNm;
    }

    public function setMaxNm(?int $maxNm): static
    {
        $this->maxNm = $maxNm;

        return $this;
    }

    public function getMaxPs(): ?int
    {
        return $this->maxPs;
    }

    public function setMaxPs(?int $maxPs): static
    {
        $this->maxPs = $maxPs;

        return $this;
    }

    public function getDriveMode(): ?string
    {
        return $this->driveMode;
    }

    public function setDriveMode(?string $driveMode): static
    {
        $this->driveMode = $driveMode;

        return $this;
    }

    public function getDataPoints(): ?array
    {
        return $this->dataPoints;
    }

    public function setDataPoints(?array $dataPoints): static
    {
        $this->dataPoints = $dataPoints;

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
}
