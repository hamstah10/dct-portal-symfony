<?php

namespace App\Entity;

use App\Repository\VehicleSeriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleSeriesRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_vehicleseries')]
class VehicleSeries
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'series')]
    #[ORM\JoinColumn(name: 'relation_to_vehiclebrand', referencedColumnName: 'uid', nullable: true)]
    private ?VehicleBrand $vehicleBrand = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_data_pool', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $vehicleDataPool = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'performance_brand', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?PerformanceBrand $performanceBrand = null;

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
     * @var Collection<int, VehicleModel>
     */
    #[ORM\OneToMany(mappedBy: 'vehicleSeries', targetEntity: VehicleModel::class)]
    private Collection $models;

    public function __construct()
    {
        $this->models = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('VehicleSeries #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicleBrand(): ?VehicleBrand
    {
        return $this->vehicleBrand;
    }

    public function setVehicleBrand(?VehicleBrand $vehicleBrand): static
    {
        $this->vehicleBrand = $vehicleBrand;

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

    public function getPerformanceBrand(): ?PerformanceBrand
    {
        return $this->performanceBrand;
    }

    public function setPerformanceBrand(?PerformanceBrand $performanceBrand): static
    {
        $this->performanceBrand = $performanceBrand;

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
     * @return Collection<int, VehicleModel>
     */
    public function getModels(): Collection
    {
        return $this->models;
    }

    public function addModel(VehicleModel $model): static
    {
        if (!$this->models->contains($model)) {
            $this->models->add($model);
            $model->setVehicleSeries($this);
        }

        return $this;
    }

    public function removeModel(VehicleModel $model): static
    {
        if ($this->models->removeElement($model) && $model->getVehicleSeries() === $this) {
            $model->setVehicleSeries(null);
        }

        return $this;
    }

    /** @return array<string, mixed> */
    public function getSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brandName' => $this->vehicleBrand?->getName() ?? '',
            'active' => $this->active,
            'sorting' => $this->sorting,
            'vehicleTypeName' => $this->vehicleBrand?->getVehicleType()?->getName() ?? '',
            'dataPoolKey' => $this->vehicleDataPool?->getPoolKey() ?? '',
            'performanceBrandName' => $this->performanceBrand?->getName() ?? '',
        ];
    }
}
