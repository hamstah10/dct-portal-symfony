<?php

namespace App\Entity;

use App\Repository\DynoVehicleRepository;
use App\Util\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DynoVehicleRepository::class)]
#[ORM\Table(name: 'dyno_vehicle')]
class DynoVehicle
{
    #[ORM\Id]
    #[ORM\Column(length: 64)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $make = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $stage = null;

    #[ORM\Column(name: 'max_ps', nullable: true)]
    private ?int $maxPs = null;

    #[ORM\Column(name: 'max_nm', nullable: true)]
    private ?int $maxNm = null;

    #[ORM\Column(name: 'max_rpm', nullable: true)]
    private ?int $maxRpm = null;

    #[ORM\Column(name: 'idle_rpm', nullable: true)]
    private ?int $idleRpm = null;

    #[ORM\Column(name: 'engine_cc', nullable: true)]
    private ?int $engineCc = null;

    #[ORM\Column(nullable: true)]
    private ?int $cylinders = null;

    #[ORM\Column(name: 'fuel_type', length: 40, nullable: true)]
    private ?string $fuelType = null;

    #[ORM\Column(name: 'is_active')]
    private bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, DynoCustomer>
     */
    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: DynoCustomer::class)]
    private Collection $customers;

    /**
     * @var Collection<int, DynoTrip>
     */
    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: DynoTrip::class)]
    private Collection $trips;

    /**
     * @var Collection<int, DynoSprint>
     */
    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: DynoSprint::class)]
    private Collection $sprints;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->createdAt = new \DateTimeImmutable();
        $this->customers = new ArrayCollection();
        $this->trips = new ArrayCollection();
        $this->sprints = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : $this->id;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMake(): ?string
    {
        return $this->make;
    }

    public function setMake(?string $make): static
    {
        $this->make = $make;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getStage(): ?string
    {
        return $this->stage;
    }

    public function setStage(?string $stage): static
    {
        $this->stage = $stage;

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

    public function getMaxRpm(): ?int
    {
        return $this->maxRpm;
    }

    public function setMaxRpm(?int $maxRpm): static
    {
        $this->maxRpm = $maxRpm;

        return $this;
    }

    public function getIdleRpm(): ?int
    {
        return $this->idleRpm;
    }

    public function setIdleRpm(?int $idleRpm): static
    {
        $this->idleRpm = $idleRpm;

        return $this;
    }

    public function getEngineCc(): ?int
    {
        return $this->engineCc;
    }

    public function setEngineCc(?int $engineCc): static
    {
        $this->engineCc = $engineCc;

        return $this;
    }

    public function getCylinders(): ?int
    {
        return $this->cylinders;
    }

    public function setCylinders(?int $cylinders): static
    {
        $this->cylinders = $cylinders;

        return $this;
    }

    public function getFuelType(): ?string
    {
        return $this->fuelType;
    }

    public function setFuelType(?string $fuelType): static
    {
        $this->fuelType = $fuelType;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

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
     * @return Collection<int, DynoCustomer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    /**
     * @return Collection<int, DynoTrip>
     */
    public function getTrips(): Collection
    {
        return $this->trips;
    }

    /**
     * @return Collection<int, DynoSprint>
     */
    public function getSprints(): Collection
    {
        return $this->sprints;
    }
}
