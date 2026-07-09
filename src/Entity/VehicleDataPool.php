<?php

namespace App\Entity;

use App\Repository\VehicleDataPoolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleDataPoolRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_vehicle_data_pool')]
class VehicleDataPool
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 120, unique: true)]
    private string $poolKey = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $active = true;

    /**
     * @var Collection<int, VehicleType>
     */
    #[ORM\ManyToMany(targetEntity: VehicleType::class, inversedBy: 'dataPools')]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_vehicle_data_pool_type_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $vehicleTypes;

    /**
     * @var Collection<int, VehicleDataContext>
     */
    #[ORM\ManyToMany(targetEntity: VehicleDataContext::class, mappedBy: 'allowedDataPools')]
    private Collection $contexts;

    public function __construct()
    {
        $this->vehicleTypes = new ArrayCollection();
        $this->contexts = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('VehicleDataPool #' . ($this->id ?? 0));
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

    public function getPoolKey(): string
    {
        return $this->poolKey;
    }

    public function setPoolKey(string $poolKey): static
    {
        $this->poolKey = trim($poolKey);

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

    /**
     * @return Collection<int, VehicleType>
     */
    public function getVehicleTypes(): Collection
    {
        return $this->vehicleTypes;
    }

    public function addVehicleType(VehicleType $vehicleType): static
    {
        if (!$this->vehicleTypes->contains($vehicleType)) {
            $this->vehicleTypes->add($vehicleType);
        }

        return $this;
    }

    public function removeVehicleType(VehicleType $vehicleType): static
    {
        $this->vehicleTypes->removeElement($vehicleType);

        return $this;
    }

    /**
     * @return Collection<int, VehicleDataContext>
     */
    public function getContexts(): Collection
    {
        return $this->contexts;
    }
}
