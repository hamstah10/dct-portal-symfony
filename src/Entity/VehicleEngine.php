<?php

namespace App\Entity;

use App\Repository\VehicleEngineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleEngineRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_vehicleengine')]
class VehicleEngine
{
    public const MEDIA_DISPLAY_IMAGE = 'image';
    public const MEDIA_DISPLAY_GLB = 'glb';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'engines')]
    #[ORM\JoinColumn(name: 'relation_to_vehiclemodel', referencedColumnName: 'uid', nullable: true)]
    private ?VehicleModel $vehicleModel = null;

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

    #[ORM\Column(name: 'glb_model', length: 255, nullable: true)]
    private ?string $glbModel = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'image_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $imageMediaAsset = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'glb_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $glbMediaAsset = null;

    #[ORM\Column(name: 'media_display_mode', length: 20)]
    private string $mediaDisplayMode = self::MEDIA_DISPLAY_IMAGE;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(nullable: true)]
    private ?int $horsepower = null;

    #[ORM\Column(nullable: true)]
    private ?int $torque = null;

    #[ORM\Column(nullable: true)]
    private ?int $ecus = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $fuel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $displacement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $compression = null;

    #[ORM\Column(name: 'bore_stroke', length: 255, nullable: true)]
    private ?string $boreStroke = null;

    #[ORM\Column(name: 'engine_code', length: 255, nullable: true)]
    private ?string $engineCode = null;

    #[ORM\Column(name: 'read_methods', type: Types::JSON, nullable: true)]
    private ?array $readMethods = null;

    #[ORM\Column(nullable: true)]
    private ?int $vmax = null;

    #[ORM\Column(name: 'acceleration_0_100', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $acceleration0100 = null;

    #[ORM\Column(name: 'acceleration_0_200', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $acceleration0200 = null;

    #[ORM\Column(name: 'acceleration_100_200', type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $acceleration100200 = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(nullable: true)]
    private ?int $weight = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $hsn = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column]
    private int $sorting = 0;

    private mixed $uploadedImage = null;
    private mixed $uploadedGlbModel = null;

    /**
     * @var Collection<int, VehicleStage>
     */
    #[ORM\OneToMany(mappedBy: 'vehicleEngine', targetEntity: VehicleStage::class)]
    private Collection $stages;

    /**
     * @var Collection<int, Ecu>
     */
    #[ORM\ManyToMany(targetEntity: Ecu::class, inversedBy: 'vehicleEngines')]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $ecuRelations;

    /**
     * @var Collection<int, Gearbox>
     */
    #[ORM\ManyToMany(targetEntity: Gearbox::class, inversedBy: 'vehicleEngines')]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_domain_model_vehicleengine_gearbox_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $gearboxes;

    /**
     * @var Collection<int, EcuOption>
     */
    #[ORM\ManyToMany(targetEntity: EcuOption::class, inversedBy: 'vehicleEngines')]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_vehicleengine_ecuoption_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $options;

    public function __construct()
    {
        $this->stages = new ArrayCollection();
        $this->ecuRelations = new ArrayCollection();
        $this->gearboxes = new ArrayCollection();
        $this->options = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('VehicleEngine #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicleModel(): ?VehicleModel
    {
        return $this->vehicleModel;
    }

    public function setVehicleModel(?VehicleModel $vehicleModel): static
    {
        $this->vehicleModel = $vehicleModel;

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

    public function getBrandGroup(): string
    {
        $brandName = trim((string) ($this->vehicleModel?->getVehicleSeries()?->getVehicleBrand()?->getName() ?? ''));

        return '' !== $brandName ? $brandName : 'Unbekannter Hersteller';
    }

    public function getSeriesGroup(): string
    {
        $seriesName = trim((string) ($this->vehicleModel?->getVehicleSeries()?->getName() ?? ''));

        return '' !== $seriesName ? $seriesName : 'Ohne Baureihe';
    }

    public function getModelGroup(): string
    {
        $modelName = trim((string) ($this->vehicleModel?->getName() ?? ''));

        return '' !== $modelName ? $modelName : 'Ohne Modell';
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
        $mediaAssetPath = trim((string) ($this->imageMediaAsset?->getFilePath() ?? ''));
        if ('' !== $mediaAssetPath) {
            return self::normalizeMediaPath($mediaAssetPath);
        }

        return self::normalizeMediaPath($this->image);
    }

    public function setImage(?string $image): static
    {
        $this->image = self::normalizeMediaPath($image);

        return $this;
    }

    public function getGlbModel(): ?string
    {
        $mediaAssetPath = trim((string) ($this->glbMediaAsset?->getFilePath() ?? ''));
        if ('' !== $mediaAssetPath) {
            return self::normalizeMediaPath($mediaAssetPath);
        }

        return self::normalizeMediaPath($this->glbModel);
    }

    public function setGlbModel(?string $glbModel): static
    {
        $this->glbModel = self::normalizeMediaPath($glbModel);

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

    public function getGlbMediaAsset(): ?MediaAsset
    {
        return $this->glbMediaAsset;
    }

    public function setGlbMediaAsset(?MediaAsset $glbMediaAsset): static
    {
        $this->glbMediaAsset = $glbMediaAsset;

        return $this;
    }

    private static function normalizeMediaPath(?string $path): ?string
    {
        if (null === $path) {
            return null;
        }

        $value = trim($path);
        if ('' === $value) {
            return null;
        }

        $value = preg_replace('/^[\s\\\'"“”„‚‹›«»]+|[\s\\\'"“”„‚‹›«»]+$/u', '', $value) ?? $value;
        $value = trim($value);

        return '' === $value ? null : $value;
    }

    public function getMediaDisplayMode(): string
    {
        if (!self::isValidMediaDisplayMode($this->mediaDisplayMode)) {
            return self::MEDIA_DISPLAY_IMAGE;
        }

        return $this->mediaDisplayMode;
    }

    public function setMediaDisplayMode(string $mediaDisplayMode): static
    {
        $normalized = strtolower(trim($mediaDisplayMode));
        $this->mediaDisplayMode = self::isValidMediaDisplayMode($normalized)
            ? $normalized
            : self::MEDIA_DISPLAY_IMAGE;

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

    public function getUploadedGlbModel(): mixed
    {
        return $this->uploadedGlbModel;
    }

    public function setUploadedGlbModel(mixed $uploadedGlbModel): static
    {
        $this->uploadedGlbModel = $uploadedGlbModel;

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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getHorsepower(): ?int
    {
        return $this->horsepower;
    }

    public function setHorsepower(?int $horsepower): static
    {
        $this->horsepower = $horsepower;

        return $this;
    }

    public function getTorque(): ?int
    {
        return $this->torque;
    }

    public function setTorque(?int $torque): static
    {
        $this->torque = $torque;

        return $this;
    }

    public function getEcus(): ?int
    {
        return $this->ecus;
    }

    public function setEcus(?int $ecus): static
    {
        $this->ecus = $ecus;

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

    public function getDisplacement(): ?string
    {
        return $this->displacement;
    }

    public function setDisplacement(?string $displacement): static
    {
        $this->displacement = $displacement;

        return $this;
    }

    public function getCompression(): ?string
    {
        return $this->compression;
    }

    public function setCompression(?string $compression): static
    {
        $this->compression = $compression;

        return $this;
    }

    public function getBoreStroke(): ?string
    {
        return $this->boreStroke;
    }

    public function setBoreStroke(?string $boreStroke): static
    {
        $this->boreStroke = $boreStroke;

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

    public function getReadMethods(): ?array
    {
        return $this->readMethods;
    }

    /**
     * @param array<int, string>|null $readMethods
     */
    public function setReadMethods(?array $readMethods): static
    {
        $this->readMethods = $readMethods;

        return $this;
    }

    public function getVmax(): ?int
    {
        return $this->vmax;
    }

    public function setVmax(?int $vmax): static
    {
        $this->vmax = $vmax;

        return $this;
    }

    public function getAcceleration0100(): ?string
    {
        return $this->acceleration0100;
    }

    public function setAcceleration0100(?string $acceleration0100): static
    {
        $this->acceleration0100 = $acceleration0100;

        return $this;
    }

    public function getAcceleration0200(): ?string
    {
        return $this->acceleration0200;
    }

    public function setAcceleration0200(?string $acceleration0200): static
    {
        $this->acceleration0200 = $acceleration0200;

        return $this;
    }

    public function getAcceleration100200(): ?string
    {
        return $this->acceleration100200;
    }

    public function setAcceleration100200(?string $acceleration100200): static
    {
        $this->acceleration100200 = $acceleration100200;

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

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getHsn(): ?string
    {
        return $this->hsn;
    }

    public function setHsn(?string $hsn): static
    {
        $this->hsn = $hsn;

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
     * @return Collection<int, VehicleStage>
     */
    public function getStages(): Collection
    {
        return $this->stages;
    }

    public function addStage(VehicleStage $stage): static
    {
        if (!$this->stages->contains($stage)) {
            $this->stages->add($stage);
            $stage->setVehicleEngine($this);
        }

        return $this;
    }

    public function removeStage(VehicleStage $stage): static
    {
        if ($this->stages->removeElement($stage) && $stage->getVehicleEngine() === $this) {
            $stage->setVehicleEngine(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Ecu>
     */
    public function getEcuRelations(): Collection
    {
        return $this->ecuRelations;
    }

    public function addEcuRelation(Ecu $ecu): static
    {
        if (!$this->ecuRelations->contains($ecu)) {
            $this->ecuRelations->add($ecu);
        }

        return $this;
    }

    public function removeEcuRelation(Ecu $ecu): static
    {
        $this->ecuRelations->removeElement($ecu);

        return $this;
    }

    /**
     * @return Collection<int, Gearbox>
     */
    public function getGearboxes(): Collection
    {
        return $this->gearboxes;
    }

    public function addGearbox(Gearbox $gearbox): static
    {
        if (!$this->gearboxes->contains($gearbox)) {
            $this->gearboxes->add($gearbox);
            $gearbox->addVehicleEngine($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, EcuOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(EcuOption $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
        }

        return $this;
    }

    public function removeOption(EcuOption $option): static
    {
        $this->options->removeElement($option);

        return $this;
    }

    public function removeGearbox(Gearbox $gearbox): static
    {
        if ($this->gearboxes->removeElement($gearbox)) {
            $gearbox->removeVehicleEngine($this);
        }

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public static function getMediaDisplayModeChoices(): array
    {
        return [
            'Bild' => self::MEDIA_DISPLAY_IMAGE,
            '3D Modell (GLB)' => self::MEDIA_DISPLAY_GLB,
        ];
    }

    private static function isValidMediaDisplayMode(string $mode): bool
    {
        return in_array($mode, array_values(self::getMediaDisplayModeChoices()), true);
    }

    /** @return array<string, mixed> */
    public function getSearchableArray(): array
    {
        $model = $this->vehicleModel;
        $series = $model?->getVehicleSeries();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'fuel' => $this->fuel,
            'displacement' => $this->displacement,
            'horsepower' => $this->horsepower,
            'torque' => $this->torque,
            'modelName' => $model?->getName() ?? '',
            'seriesName' => $series?->getName() ?? '',
            'brandName' => $series?->getVehicleBrand()?->getName() ?? '',
            'active' => $this->active,
            'vehicleTypeName' => $series?->getVehicleBrand()?->getVehicleType()?->getName() ?? '',
            'dataPoolKey' => $this->vehicleDataPool?->getPoolKey() ?? '',
            'performanceBrandName' => $this->performanceBrand?->getName() ?? '',
        ];
    }
}
