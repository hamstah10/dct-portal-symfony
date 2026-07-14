<?php

namespace App\Entity;

use App\Repository\EcuOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcuOptionRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_domain_model_ecuoption')]
class EcuOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_data_pool', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $vehicleDataPool = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'master_option', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?EcuOptionMaster $masterOption = null;

    #[ORM\ManyToOne(inversedBy: 'options')]
    #[ORM\JoinColumn(name: 'category_master_id', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?EcuOptionCategoryMaster $categoryMaster = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'tooltip_text', type: Types::TEXT, nullable: true)]
    private ?string $tooltipText = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'icon_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $iconMediaAsset = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $images = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(name: 'is_main_option')]
    private bool $isMainOption = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    /**
     * @var Collection<int, Ecu>
     */
    #[ORM\ManyToMany(targetEntity: Ecu::class, mappedBy: 'options')]
    private Collection $ecus;

    /**
     * @var Collection<int, VehicleEngine>
     */
    #[ORM\ManyToMany(targetEntity: VehicleEngine::class, mappedBy: 'options')]
    private Collection $vehicleEngines;

    private mixed $uploadedImage = null;

    /**
     * @var array<int, mixed>
     */
    private array $uploadedImages = [];

    public function __construct()
    {
        $this->ecus = new ArrayCollection();
        $this->vehicleEngines = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('EcuOption #' . ($this->id ?? 0));
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

    public function getVehicleDataPool(): ?VehicleDataPool
    {
        return $this->vehicleDataPool;
    }

    public function setVehicleDataPool(?VehicleDataPool $vehicleDataPool): static
    {
        $this->vehicleDataPool = $vehicleDataPool;

        return $this;
    }

    public function getMasterOption(): ?EcuOptionMaster
    {
        return $this->masterOption;
    }

    public function setMasterOption(?EcuOptionMaster $masterOption): static
    {
        $this->masterOption = $masterOption;

        return $this;
    }

    public function getCategoryMaster(): ?EcuOptionCategoryMaster
    {
        return $this->categoryMaster;
    }

    public function setCategoryMaster(?EcuOptionCategoryMaster $categoryMaster): static
    {
        $this->categoryMaster = $categoryMaster;

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

    public function getTooltipText(): ?string
    {
        return $this->tooltipText;
    }

    public function setTooltipText(?string $tooltipText): static
    {
        $this->tooltipText = $tooltipText;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

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

    public function getIconMediaAsset(): ?MediaAsset
    {
        return $this->iconMediaAsset;
    }

    public function setIconMediaAsset(?MediaAsset $iconMediaAsset): static
    {
        $this->iconMediaAsset = $iconMediaAsset;

        return $this;
    }

    public function getResolvedIconPath(): ?string
    {
        $mediaAssetPath = trim((string) ($this->iconMediaAsset?->getFilePath() ?? ''));
        if ('' !== $mediaAssetPath) {
            return $mediaAssetPath;
        }

        $legacyPath = trim((string) ($this->icon ?? ''));

        return '' !== $legacyPath ? $legacyPath : null;
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

    public function getImages(): ?string
    {
        return $this->images;
    }

    public function setImages(?string $images): static
    {
        $this->images = $images;

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

    /**
     * @return array<int, mixed>
     */
    public function getUploadedImages(): array
    {
        return $this->uploadedImages;
    }

    /**
     * @param array<int, mixed> $uploadedImages
     */
    public function setUploadedImages(array $uploadedImages): static
    {
        $this->uploadedImages = $uploadedImages;

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

    public function isMainOption(): bool
    {
        return $this->isMainOption;
    }

    public function setIsMainOption(bool $isMainOption): static
    {
        $this->isMainOption = $isMainOption;

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

    /**
     * @return Collection<int, Ecu>
     */
    public function getEcus(): Collection
    {
        return $this->ecus;
    }

    /**
     * @return Collection<int, VehicleEngine>
     */
    public function getVehicleEngines(): Collection
    {
        return $this->vehicleEngines;
    }
}
