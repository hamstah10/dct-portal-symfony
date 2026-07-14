<?php

namespace App\Entity;

use App\Repository\VehicleDataContextRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleDataContextRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_vehicle_data_context')]
class VehicleDataContext
{
    public const USAGE_INTERNAL = 'internal';
    public const USAGE_API = 'api';
    public const USAGE_PREVIEW = 'preview';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 120, unique: true)]
    private string $contextKey = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private string $usageType = self::USAGE_INTERNAL;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private bool $showPrices = true;

    #[ORM\Column]
    private bool $showHints = true;

    #[ORM\Column]
    private bool $showGearbox = true;

    #[ORM\Column]
    private bool $showDyno = true;

    #[ORM\Column]
    private bool $showEcoStage = true;

    #[ORM\Column(options: ['default' => true])]
    private bool $showOptionCategories = true;

    #[ORM\Column]
    private bool $allowVehicleEditing = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'default_vehicle_data_pool', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $defaultVehicleDataPool = null;

    /**
     * @var Collection<int, VehicleDataPool>
     */
    #[ORM\ManyToMany(targetEntity: VehicleDataPool::class, inversedBy: 'contexts')]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_vehicle_data_context_pool_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $allowedDataPools;

    /**
     * @var Collection<int, ApiToken>
     */
    #[ORM\OneToMany(mappedBy: 'vehicleDataContext', targetEntity: ApiToken::class)]
    private Collection $apiTokens;

    public function __construct()
    {
        $this->allowedDataPools = new ArrayCollection();
        $this->apiTokens = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('VehicleDataContext #' . ($this->id ?? 0));
    }

    /**
     * @return array<string, string>
     */
    public static function getUsageTypeChoices(): array
    {
        return [
            'Intern' => self::USAGE_INTERNAL,
            'API' => self::USAGE_API,
            'Preview' => self::USAGE_PREVIEW,
        ];
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

    public function getContextKey(): string
    {
        return $this->contextKey;
    }

    public function setContextKey(string $contextKey): static
    {
        $this->contextKey = trim($contextKey);

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

    public function getUsageType(): string
    {
        return $this->usageType;
    }

    public function setUsageType(string $usageType): static
    {
        $this->usageType = $usageType;

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

    public function isShowPrices(): bool
    {
        return $this->showPrices;
    }

    public function setShowPrices(bool $showPrices): static
    {
        $this->showPrices = $showPrices;

        return $this;
    }

    public function isShowHints(): bool
    {
        return $this->showHints;
    }

    public function setShowHints(bool $showHints): static
    {
        $this->showHints = $showHints;

        return $this;
    }

    public function isShowGearbox(): bool
    {
        return $this->showGearbox;
    }

    public function setShowGearbox(bool $showGearbox): static
    {
        $this->showGearbox = $showGearbox;

        return $this;
    }

    public function isShowDyno(): bool
    {
        return $this->showDyno;
    }

    public function setShowDyno(bool $showDyno): static
    {
        $this->showDyno = $showDyno;

        return $this;
    }

    public function isShowEcoStage(): bool
    {
        return $this->showEcoStage;
    }

    public function setShowEcoStage(bool $showEcoStage): static
    {
        $this->showEcoStage = $showEcoStage;

        return $this;
    }

    public function isShowOptionCategories(): bool
    {
        return $this->showOptionCategories;
    }

    public function setShowOptionCategories(bool $showOptionCategories): static
    {
        $this->showOptionCategories = $showOptionCategories;

        return $this;
    }

    public function isAllowVehicleEditing(): bool
    {
        return $this->allowVehicleEditing;
    }

    public function setAllowVehicleEditing(bool $allowVehicleEditing): static
    {
        $this->allowVehicleEditing = $allowVehicleEditing;

        return $this;
    }

    public function getDefaultVehicleDataPool(): ?VehicleDataPool
    {
        return $this->defaultVehicleDataPool;
    }

    public function setDefaultVehicleDataPool(?VehicleDataPool $defaultVehicleDataPool): static
    {
        $this->defaultVehicleDataPool = $defaultVehicleDataPool;

        return $this;
    }

    /**
     * @return Collection<int, VehicleDataPool>
     */
    public function getAllowedDataPools(): Collection
    {
        return $this->allowedDataPools;
    }

    public function addAllowedDataPool(VehicleDataPool $vehicleDataPool): static
    {
        if (!$this->allowedDataPools->contains($vehicleDataPool)) {
            $this->allowedDataPools->add($vehicleDataPool);
        }

        return $this;
    }

    public function removeAllowedDataPool(VehicleDataPool $vehicleDataPool): static
    {
        $this->allowedDataPools->removeElement($vehicleDataPool);

        return $this;
    }

    public function allowsDataPool(VehicleDataPool $vehicleDataPool): bool
    {
        return $this->allowedDataPools->contains($vehicleDataPool);
    }

    /**
     * @return Collection<int, ApiToken>
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }
}
