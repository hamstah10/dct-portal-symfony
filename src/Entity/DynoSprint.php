<?php

namespace App\Entity;

use App\Repository\DynoSprintRepository;
use App\Util\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DynoSprintRepository::class)]
#[ORM\Table(name: 'dyno_sprint')]
class DynoSprint
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\ManyToOne(inversedBy: 'sprints')]
    #[ORM\JoinColumn(name: 'vehicle_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?DynoVehicle $vehicle = null;

    #[ORM\Column(name: 'vehicle_name', length: 255, nullable: true)]
    private ?string $vehicleName = null;

    #[ORM\Column(name: 'sprint_type', length: 50)]
    private string $sprintType = '';

    #[ORM\Column(name: 'time_seconds', type: Types::DECIMAL, precision: 8, scale: 2)]
    private string $timeSeconds = '0.00';

    #[ORM\Column(name: 'trip_id', length: 36, nullable: true)]
    private ?string $tripId = null;

    #[ORM\Column(name: 'distance_m', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $distanceMeters = '0.00';

    #[ORM\Column(name: 'max_rpm', nullable: true)]
    private ?int $maxRpm = null;

    #[ORM\Column(name: 'max_ps', nullable: true)]
    private ?int $maxPs = null;

    #[ORM\Column(name: 'max_nm', nullable: true)]
    private ?int $maxNm = null;

    #[ORM\Column(name: 'drive_mode', length: 60, nullable: true)]
    private ?string $driveMode = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->createdAt = new \DateTimeImmutable();
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

    public function getSprintType(): string
    {
        return $this->sprintType;
    }

    public function setSprintType(string $sprintType): static
    {
        $this->sprintType = $sprintType;

        return $this;
    }

    public function getTimeSeconds(): string
    {
        return $this->timeSeconds;
    }

    public function setTimeSeconds(string $timeSeconds): static
    {
        $this->timeSeconds = $timeSeconds;

        return $this;
    }

    public function getTripId(): ?string
    {
        return $this->tripId;
    }

    public function setTripId(?string $tripId): static
    {
        $this->tripId = $tripId;

        return $this;
    }

    public function getDistanceMeters(): string
    {
        return $this->distanceMeters;
    }

    public function setDistanceMeters(string $distanceMeters): static
    {
        $this->distanceMeters = $distanceMeters;

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

    public function getMaxPs(): ?int
    {
        return $this->maxPs;
    }

    public function setMaxPs(?int $maxPs): static
    {
        $this->maxPs = $maxPs;

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

    public function getDriveMode(): ?string
    {
        return $this->driveMode;
    }

    public function setDriveMode(?string $driveMode): static
    {
        $this->driveMode = $driveMode;

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
