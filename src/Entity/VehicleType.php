<?php

namespace App\Entity;

use App\Repository\VehicleTypeRepository;
use App\Entity\MediaAsset;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleTypeRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_vehicletype')]
class VehicleType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private int $sorting = 0;

    private mixed $uploadedImage = null;

    #[ORM\ManyToOne(targetEntity: MediaAsset::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $imageMediaAsset = null;

    /**
     * @var Collection<int, VehicleBrand>
     */
    #[ORM\OneToMany(mappedBy: 'vehicleType', targetEntity: VehicleBrand::class)]
    private Collection $brands;

    /**
     * @var Collection<int, VehicleDataPool>
     */
    #[ORM\ManyToMany(targetEntity: VehicleDataPool::class, mappedBy: 'vehicleTypes')]
    private Collection $dataPools;

    public function __construct()
    {
        $this->brands = new ArrayCollection();
        $this->dataPools = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('VehicleType #' . ($this->id ?? 0));
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
        $this->name = $name;

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getImageMediaAsset(): ?MediaAsset
    {
        return $this->imageMediaAsset;
    }

    public function setImageMediaAsset(?MediaAsset $imageMediaAsset): static
    {
        $this->imageMediaAsset = $imageMediaAsset;

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
     * @return Collection<int, VehicleBrand>
     */
    public function getBrands(): Collection
    {
        return $this->brands;
    }

    public function addBrand(VehicleBrand $brand): static
    {
        if (!$this->brands->contains($brand)) {
            $this->brands->add($brand);
            $brand->setVehicleType($this);
        }

        return $this;
    }

    public function removeBrand(VehicleBrand $brand): static
    {
        if ($this->brands->removeElement($brand) && $brand->getVehicleType() === $this) {
            $brand->setVehicleType(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, VehicleDataPool>
     */
    public function getDataPools(): Collection
    {
        return $this->dataPools;
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
        ];
    }
}
