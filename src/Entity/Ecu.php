<?php

namespace App\Entity;

use App\Repository\EcuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcuRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_domain_model_ecu')]
class Ecu
{
    public const CONTROL_UNIT_KIND_TCU = 'TCU';
    public const CONTROL_UNIT_KIND_MCU = 'MCU';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(name: 'control_unit_kind', length: 10, nullable: true)]
    private ?string $controlUnitKind = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_data_pool', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleDataPool $vehicleDataPool = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'master_ecu', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?EcuMaster $masterEcu = null;

    #[ORM\ManyToOne(inversedBy: 'ecus')]
    #[ORM\JoinColumn(name: 'manufacturer', referencedColumnName: 'uid', nullable: true)]
    private ?EcuManufacturer $manufacturer = null;

    #[ORM\ManyToOne(inversedBy: 'ecus')]
    #[ORM\JoinColumn(name: 'tool_manufacturer', referencedColumnName: 'uid', nullable: true)]
    private ?ToolManufacturer $toolManufacturer = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_engine', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?VehicleEngine $vehicleEngine = null;

    /**
     * @var Collection<int, ToolMethode>
     */
    #[ORM\ManyToMany(targetEntity: ToolMethode::class)]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $toolMethods;

    /**
     * @var Collection<int, EcuToolEntry>
     */
    #[ORM\OneToMany(mappedBy: 'ecu', targetEntity: EcuToolEntry::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'id' => 'ASC'])]
    private Collection $toolEntries;

    /**
     * @var Collection<int, EcuOption>
     */
    #[ORM\ManyToMany(targetEntity: EcuOption::class, inversedBy: 'ecus')]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_ecuoption_ecu_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $options;

    /**
     * @var Collection<int, VehicleEngine>
     */
    #[ORM\ManyToMany(targetEntity: VehicleEngine::class, mappedBy: 'ecuRelations')]
    private Collection $vehicleEngines;

    /**
     * @var Collection<int, Gearbox>
     */
    #[ORM\ManyToMany(targetEntity: Gearbox::class, mappedBy: 'ecus')]
    private Collection $gearboxes;

    private mixed $uploadedImage = null;

    public function __construct()
    {
        $this->toolMethods = new ArrayCollection();
        $this->toolEntries = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->vehicleEngines = new ArrayCollection();
        $this->gearboxes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('ECU #' . ($this->id ?? 0));
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        $derived = $this->deriveControlUnitKindFromType();
        if (null !== $derived) {
            $this->controlUnitKind = $derived;
        }

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public static function getControlUnitKindChoices(): array
    {
        return [
            'TCU' => self::CONTROL_UNIT_KIND_TCU,
            'MCU' => self::CONTROL_UNIT_KIND_MCU,
        ];
    }

    public function getControlUnitKind(): ?string
    {
        if (null !== $this->controlUnitKind) {
            return $this->controlUnitKind;
        }

        return $this->deriveControlUnitKindFromType();
    }

    public function setControlUnitKind(?string $controlUnitKind): static
    {
        if (null !== $controlUnitKind && !in_array($controlUnitKind, self::getControlUnitKindChoices(), true)) {
            $controlUnitKind = null;
        }

        $this->controlUnitKind = $controlUnitKind;

        return $this;
    }

    private function deriveControlUnitKindFromType(): ?string
    {
        $type = strtoupper(trim((string) ($this->type ?? '')));
        if ('' === $type) {
            return null;
        }

        if (preg_match('/\\bTCU\\b/', $type)) {
            return self::CONTROL_UNIT_KIND_TCU;
        }

        if (preg_match('/\\bMCU\\b/', $type)) {
            return self::CONTROL_UNIT_KIND_MCU;
        }

        if (str_contains($type, 'TCU')) {
            return self::CONTROL_UNIT_KIND_TCU;
        }

        if (str_contains($type, 'MCU')) {
            return self::CONTROL_UNIT_KIND_MCU;
        }

        return null;
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

    public function getVehicleDataPool(): ?VehicleDataPool
    {
        return $this->vehicleDataPool;
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

    public function setVehicleDataPool(?VehicleDataPool $vehicleDataPool): static
    {
        $this->vehicleDataPool = $vehicleDataPool;

        return $this;
    }

    public function getMasterEcu(): ?EcuMaster
    {
        return $this->masterEcu;
    }

    public function setMasterEcu(?EcuMaster $masterEcu): static
    {
        $this->masterEcu = $masterEcu;

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

    public function getManufacturer(): ?EcuManufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?EcuManufacturer $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getToolManufacturer(): ?ToolManufacturer
    {
        return $this->toolManufacturer;
    }

    public function setToolManufacturer(?ToolManufacturer $toolManufacturer): static
    {
        $this->toolManufacturer = $toolManufacturer;

        return $this;
    }

    /**
     * @return Collection<int, EcuToolEntry>
     */
    public function getToolEntries(): Collection
    {
        return $this->toolEntries;
    }

    public function addToolEntry(EcuToolEntry $toolEntry): static
    {
        if (!$this->toolEntries->contains($toolEntry)) {
            $this->toolEntries->add($toolEntry);
            $toolEntry->setEcu($this);
        }

        return $this;
    }

    public function removeToolEntry(EcuToolEntry $toolEntry): static
    {
        if ($this->toolEntries->removeElement($toolEntry)) {
            if ($toolEntry->getEcu() === $this) {
                $toolEntry->setEcu(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ToolMethode>
     */
    public function getToolMethods(): Collection
    {
        return $this->toolMethods;
    }

    public function addToolMethod(ToolMethode $toolMethod): static
    {
        if (!$this->toolMethods->contains($toolMethod)) {
            $this->toolMethods->add($toolMethod);
        }

        return $this;
    }

    public function removeToolMethod(ToolMethode $toolMethod): static
    {
        $this->toolMethods->removeElement($toolMethod);

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

    /**
     * @return Collection<int, VehicleEngine>
     */
    public function getVehicleEngines(): Collection
    {
        return $this->vehicleEngines;
    }

    /**
     * @return Collection<int, Gearbox>
     */
    public function getGearboxes(): Collection
    {
        return $this->gearboxes;
    }
}
