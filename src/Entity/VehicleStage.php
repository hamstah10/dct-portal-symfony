<?php

namespace App\Entity;

use App\Repository\VehicleStageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleStageRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_vehiclestage')]
class VehicleStage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stages')]
    #[ORM\JoinColumn(name: 'relation_to_vehicleengine', referencedColumnName: 'uid', nullable: true)]
    private ?VehicleEngine $vehicleEngine = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_data_pool', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $vehicleDataPool = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(nullable: true)]
    private ?int $hp = null;

    #[ORM\Column(nullable: true)]
    private ?int $nm = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $images = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $videos = null;

    #[ORM\Column(nullable: true)]
    private ?int $ecu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'icon_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $iconMediaAsset = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(nullable: true)]
    private ?int $originalPs = null;

    #[ORM\Column(nullable: true)]
    private ?int $originalNm = null;

    #[ORM\Column(nullable: true)]
    private ?int $rpmStart = null;

    #[ORM\Column(nullable: true)]
    private ?int $rpmEnd = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $engineType = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(name: 'dyno_chart', type: Types::JSON, nullable: true)]
    private ?array $dynoChart = null;

    /**
     * @var array<int, mixed>
     */
    private array $uploadedImages = [];

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('VehicleStage #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicleEngine(): ?VehicleEngine
    {
        return $this->vehicleEngine;
    }

    public function setVehicleEngine(?VehicleEngine $vehicleEngine): static
    {
        $this->vehicleEngine = $vehicleEngine;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getHp(): ?int
    {
        return $this->hp;
    }

    public function setHp(?int $hp): static
    {
        $this->hp = $hp;

        return $this;
    }

    public function getPower(): ?int
    {
        return $this->getHp();
    }

    public function setPower(?int $power): static
    {
        return $this->setHp($power);
    }

    public function getNm(): ?int
    {
        return $this->nm;
    }

    public function setNm(?int $nm): static
    {
        $this->nm = $nm;

        return $this;
    }

    public function getTorque(): ?int
    {
        return $this->getNm();
    }

    public function setTorque(?int $torque): static
    {
        return $this->setNm($torque);
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

    public function getVideos(): ?string
    {
        return $this->videos;
    }

    public function setVideos(?string $videos): static
    {
        $this->videos = $videos;

        return $this;
    }

    public function getEcu(): ?int
    {
        return $this->ecu;
    }

    public function setEcu(?int $ecu): static
    {
        $this->ecu = $ecu;

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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getOriginalPs(): ?int
    {
        return $this->originalPs;
    }

    public function setOriginalPs(?int $originalPs): static
    {
        $this->originalPs = $originalPs;

        return $this;
    }

    public function getOriginalNm(): ?int
    {
        return $this->originalNm;
    }

    public function setOriginalNm(?int $originalNm): static
    {
        $this->originalNm = $originalNm;

        return $this;
    }

    public function getRpmStart(): ?int
    {
        return $this->rpmStart;
    }

    public function setRpmStart(?int $rpmStart): static
    {
        $this->rpmStart = $rpmStart;

        return $this;
    }

    public function getRpmEnd(): ?int
    {
        return $this->rpmEnd;
    }

    public function setRpmEnd(?int $rpmEnd): static
    {
        $this->rpmEnd = $rpmEnd;

        return $this;
    }

    public function getEngineType(): ?string
    {
        return $this->engineType;
    }

    public function setEngineType(?string $engineType): static
    {
        $this->engineType = $engineType;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDynoChart(): ?array
    {
        return $this->dynoChart;
    }

    /**
     * @param array<string, mixed>|null $dynoChart
     */
    public function setDynoChart(?array $dynoChart): static
    {
        $this->dynoChart = $dynoChart;

        return $this;
    }

    public function getDynoChartJson(): ?string
    {
        if (null === $this->dynoChart) {
            return null;
        }

        return json_encode($this->dynoChart, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES) ?: null;
    }

    public function setDynoChartJson(?string $dynoChartJson): static
    {
        $value = trim((string) $dynoChartJson);
        if ('' === $value) {
            $this->dynoChart = null;

            return $this;
        }

        $decoded = json_decode($value, true);
        $this->dynoChart = is_array($decoded) ? $decoded : $this->dynoChart;

        return $this;
    }
}
