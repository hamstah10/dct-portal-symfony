<?php

namespace App\Entity;

use App\Repository\VehicleBrandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleBrandRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_vehiclebrand')]
class VehicleBrand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'brands')]
    #[ORM\JoinColumn(name: 'relation_to_vehicletype', referencedColumnName: 'uid', nullable: true)]
    private ?VehicleType $vehicleType = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_data_pool', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $vehicleDataPool = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'master_brand', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?BrandMaster $masterBrand = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(name: 'scraper_source', length: 80, options: ['default' => 'legacy'])]
    private string $scraperSource = 'legacy';

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(name: 'show_in_top_menu')]
    private bool $showInTopMenu = false;

    #[ORM\Column]
    private int $sorting = 0;

    private mixed $uploadedImage = null;

    /**
     * @var Collection<int, VehicleSeries>
     */
    #[ORM\OneToMany(mappedBy: 'vehicleBrand', targetEntity: VehicleSeries::class)]
    private Collection $series;

    public function __construct()
    {
        $this->series = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('VehicleBrand #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicleType(): ?VehicleType
    {
        return $this->vehicleType;
    }

    public function setVehicleType(?VehicleType $vehicleType): static
    {
        $this->vehicleType = $vehicleType;

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

    public function getMasterBrand(): ?BrandMaster
    {
        return $this->masterBrand;
    }

    public function setMasterBrand(?BrandMaster $masterBrand): static
    {
        $this->masterBrand = $masterBrand;

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

    public function getScraperSource(): string
    {
        return $this->scraperSource;
    }

    public function setScraperSource(?string $scraperSource): static
    {
        $scraperSource = trim((string) ($scraperSource ?? ''));
        $this->scraperSource = '' !== $scraperSource ? $scraperSource : 'legacy';

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

    public function isShowInTopMenu(): bool
    {
        return $this->showInTopMenu;
    }

    public function setShowInTopMenu(bool $showInTopMenu): static
    {
        $this->showInTopMenu = $showInTopMenu;

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
     * @return Collection<int, VehicleSeries>
     */
    public function getSeries(): Collection
    {
        return $this->series;
    }

    public function addSeries(VehicleSeries $series): static
    {
        if (!$this->series->contains($series)) {
            $this->series->add($series);
            $series->setVehicleBrand($this);
        }

        return $this;
    }

    public function removeSeries(VehicleSeries $series): static
    {
        if ($this->series->removeElement($series) && $series->getVehicleBrand() === $this) {
            $series->setVehicleBrand(null);
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
            'active' => $this->active,
            'sorting' => $this->sorting,
            'vehicleTypeName' => $this->vehicleType?->getName() ?? '',
            'dataPoolKey' => $this->vehicleDataPool?->getPoolKey() ?? '',
        ];
    }
}
