<?php

namespace App\Entity;

use App\Repository\EinbauHinweisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EinbauHinweisRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_einbau_hinweis')]
#[ORM\HasLifecycleCallbacks]
class EinbauHinweis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'ecu_option_master_id', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?EcuOptionMaster $ecuOptionMaster = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'active_ecu_id', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?Ecu $activeEcu = null;

    /**
     * @var Collection<int, Ecu>
     */
    #[ORM\ManyToMany(targetEntity: Ecu::class)]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_einbau_hinweis_active_ecu_mm')]
    #[ORM\JoinColumn(name: 'einbau_hinweis_id', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'ecu_id', referencedColumnName: 'uid')]
    private Collection $activeEcus;

    /**
     * @var Collection<int, VehicleEngine>
     */
    #[ORM\ManyToMany(targetEntity: VehicleEngine::class)]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_einbau_hinweis_engine_mm')]
    #[ORM\JoinColumn(name: 'einbau_hinweis_id', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'vehicle_engine_id', referencedColumnName: 'uid')]
    private Collection $vehicleEngines;

    /**
     * @var Collection<int, VehicleModel>
     */
    #[ORM\ManyToMany(targetEntity: VehicleModel::class)]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_einbau_hinweis_model_mm')]
    #[ORM\JoinColumn(name: 'einbau_hinweis_id', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'vehicle_model_id', referencedColumnName: 'uid')]
    private Collection $vehicleModels;

    /**
     * @var Collection<int, MediaAsset>
     */
    #[ORM\ManyToMany(targetEntity: MediaAsset::class)]
    #[ORM\JoinTable(name: 'tx_chiptuningdatabase_einbau_hinweis_media_mm')]
    #[ORM\JoinColumn(name: 'einbau_hinweis_id', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'media_asset_id', referencedColumnName: 'id')]
    #[ORM\OrderBy(['sorting' => 'ASC', 'id' => 'ASC'])]
    private Collection $images;

    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->activeEcus = new ArrayCollection();
        $this->vehicleEngines = new ArrayCollection();
        $this->vehicleModels = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('EinbauHinweis #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEcuOptionMaster(): ?EcuOptionMaster
    {
        return $this->ecuOptionMaster;
    }

    public function setEcuOptionMaster(?EcuOptionMaster $ecuOptionMaster): static
    {
        $this->ecuOptionMaster = $ecuOptionMaster;

        return $this;
    }

    public function getActiveEcu(): ?Ecu
    {
        return $this->activeEcu;
    }

    public function setActiveEcu(?Ecu $activeEcu): static
    {
        $this->activeEcu = $activeEcu;

        return $this;
    }

    /**
     * @return Collection<int, Ecu>
     */
    public function getActiveEcus(): Collection
    {
        return $this->activeEcus;
    }

    public function addActiveEcu(Ecu $ecu): static
    {
        if (!$this->activeEcus->contains($ecu)) {
            $this->activeEcus->add($ecu);
        }

        return $this;
    }

    public function removeActiveEcu(Ecu $ecu): static
    {
        $this->activeEcus->removeElement($ecu);

        return $this;
    }

    /**
     * @return Collection<int, VehicleEngine>
     */
    public function getVehicleEngines(): Collection
    {
        return $this->vehicleEngines;
    }

    public function addVehicleEngine(VehicleEngine $engine): static
    {
        if (!$this->vehicleEngines->contains($engine)) {
            $this->vehicleEngines->add($engine);
        }

        return $this;
    }

    public function removeVehicleEngine(VehicleEngine $engine): static
    {
        $this->vehicleEngines->removeElement($engine);

        return $this;
    }

    /**
     * @return Collection<int, VehicleModel>
     */
    public function getVehicleModels(): Collection
    {
        return $this->vehicleModels;
    }

    public function addVehicleModel(VehicleModel $model): static
    {
        if (!$this->vehicleModels->contains($model)) {
            $this->vehicleModels->add($model);
        }

        return $this;
    }

    public function removeVehicleModel(VehicleModel $model): static
    {
        $this->vehicleModels->removeElement($model);

        return $this;
    }

    /**
     * @return Collection<int, MediaAsset>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(MediaAsset $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
        }

        return $this;
    }

    public function removeImage(MediaAsset $image): static
    {
        $this->images->removeElement($image);

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        if (null === $this->createdAt) {
            $this->createdAt = $now;
        }
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getBrandsLabel(): string
    {
        $brands = [];
        foreach ($this->vehicleEngines as $engine) {
            $brand = $engine->getVehicleModel()?->getVehicleSeries()?->getVehicleBrand();
            if ($brand !== null && !in_array($brand->getName(), $brands, true)) {
                $brands[] = $brand->getName();
            }
        }
        foreach ($this->vehicleModels as $model) {
            $brand = $model->getVehicleSeries()?->getVehicleBrand();
            if ($brand !== null && !in_array($brand->getName(), $brands, true)) {
                $brands[] = $brand->getName();
            }
        }
        return implode(', ', $brands) ?: '–';
    }
}
