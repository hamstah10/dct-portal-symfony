<?php

namespace App\Entity;

use App\Repository\VehicleModelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleModelRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_vehiclemodel')]
class VehicleModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'models')]
    #[ORM\JoinColumn(name: 'relation_to_vehicleseries', referencedColumnName: 'uid', nullable: true)]
    private ?VehicleSeries $vehicleSeries = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_data_pool', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $vehicleDataPool = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private int $sorting = 0;

    private mixed $uploadedImage = null;

    /**
     * @var Collection<int, VehicleEngine>
     */
    #[ORM\OneToMany(mappedBy: 'vehicleModel', targetEntity: VehicleEngine::class)]
    private Collection $engines;

    public function __construct()
    {
        $this->engines = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('VehicleModel #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicleSeries(): ?VehicleSeries
    {
        return $this->vehicleSeries;
    }

    public function setVehicleSeries(?VehicleSeries $vehicleSeries): static
    {
        $this->vehicleSeries = $vehicleSeries;

        return $this;
    }

    public function getVehicleDataPool(): ?VehicleDataPool
    {
        return $this->vehicleDataPool;
    }

    public function setVehicleDataPool(?VehicleDataPool $vehicleDataPool): static
    {
        $this->vehicleDataPool = $vehicleDataPool;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getUploadedImage(): mixed
    {
        return $this->uploadedImage;
    }

    public function setUploadedImage(mixed $uploadedImage): static
    {
        $this->uploadedImage = $uploadedImage;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

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

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * @return Collection<int, VehicleEngine>
     */
    public function getEngines(): Collection
    {
        return $this->engines;
    }

    public function addEngine(VehicleEngine $engine): static
    {
        if (!$this->engines->contains($engine)) {
            $this->engines->add($engine);
            $engine->setVehicleModel($this);
        }

        return $this;
    }

    public function removeEngine(VehicleEngine $engine): static
    {
        if ($this->engines->removeElement($engine) && $engine->getVehicleModel() === $this) {
            $engine->setVehicleModel(null);
        }

        return $this;
    }

    /** @return array<string, mixed> */
    public function getSearchableArray(): array
    {
        $series = $this->vehicleSeries;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'seriesName' => $series?->getName() ?? '',
            'brandName' => $series?->getVehicleBrand()?->getName() ?? '',
            'active' => $this->active,
            'sorting' => $this->sorting,
            'vehicleTypeName' => $series?->getVehicleBrand()?->getVehicleType()?->getName() ?? '',
            'dataPoolKey' => $this->vehicleDataPool?->getPoolKey() ?? '',
        ];
    }
}
