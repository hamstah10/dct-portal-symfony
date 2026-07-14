<?php

namespace App\Entity;

use App\Repository\GearboxRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GearboxRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_gearbox')]
class Gearbox
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'gearboxes')]
    #[ORM\JoinColumn(name: 'gearbox_type', referencedColumnName: 'uid', nullable: true)]
    private ?GearboxType $gearboxType = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_data_pool', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $vehicleDataPool = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'manufacturer', referencedColumnName: 'uid', nullable: true)]
    private ?EcuManufacturer $manufacturer = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'icon_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $iconMediaAsset = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $images = null;

    #[ORM\Column]
    private int $sorting = 0;

    #[ORM\Column]
    private bool $active = true;

    private mixed $uploadedImage = null;

    /**
     * @var array<int, mixed>
     */
    private array $uploadedImages = [];

    /**
     * @var Collection<int, Ecu>
     */
    #[ORM\ManyToMany(targetEntity: Ecu::class, inversedBy: 'gearboxes')]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_gearbox_ecu_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $ecus;

    /**
     * @var Collection<int, VehicleEngine>
     */
    #[ORM\ManyToMany(targetEntity: VehicleEngine::class, mappedBy: 'gearboxes')]
    private Collection $vehicleEngines;

    /**
     * @var Collection<int, GearboxToolEntry>
     */
    #[ORM\OneToMany(mappedBy: 'gearbox', targetEntity: GearboxToolEntry::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'id' => 'ASC'])]
    private Collection $toolEntries;

    public function __construct()
    {
        $this->ecus = new ArrayCollection();
        $this->vehicleEngines = new ArrayCollection();
        $this->toolEntries = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('Gearbox #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGearboxType(): ?GearboxType
    {
        return $this->gearboxType;
    }

    public function setGearboxType(?GearboxType $gearboxType): static
    {
        $this->gearboxType = $gearboxType;

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

    public function getManufacturer(): ?EcuManufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?EcuManufacturer $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getImages(): ?string
    {
        return $this->images;
    }

    public function setImages(?string $images): static
    {
        $this->images = $images;

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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

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

    /**
     * @return Collection<int, Ecu>
     */
    public function getEcus(): Collection
    {
        return $this->ecus;
    }

    public function addEcu(Ecu $ecu): static
    {
        if (!$this->ecus->contains($ecu)) {
            $this->ecus->add($ecu);
        }

        return $this;
    }

    public function removeEcu(Ecu $ecu): static
    {
        $this->ecus->removeElement($ecu);

        return $this;
    }

    /**
     * @return Collection<int, VehicleEngine>
     */
    public function getVehicleEngines(): Collection
    {
        return $this->vehicleEngines;
    }

    public function addVehicleEngine(VehicleEngine $vehicleEngine): static
    {
        if (!$this->vehicleEngines->contains($vehicleEngine)) {
            $this->vehicleEngines->add($vehicleEngine);
            $vehicleEngine->addGearbox($this);
        }

        return $this;
    }

    public function removeVehicleEngine(VehicleEngine $vehicleEngine): static
    {
        if ($this->vehicleEngines->removeElement($vehicleEngine)) {
            $vehicleEngine->removeGearbox($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, GearboxToolEntry>
     */
    public function getToolEntries(): Collection
    {
        return $this->toolEntries;
    }

    public function addToolEntry(GearboxToolEntry $toolEntry): static
    {
        if (!$this->toolEntries->contains($toolEntry)) {
            $this->toolEntries->add($toolEntry);
            $toolEntry->setGearbox($this);
        }

        return $this;
    }

    public function removeToolEntry(GearboxToolEntry $toolEntry): static
    {
        if ($this->toolEntries->removeElement($toolEntry)) {
            if ($toolEntry->getGearbox() === $this) {
                $toolEntry->setGearbox(null);
            }
        }

        return $this;
    }
}
