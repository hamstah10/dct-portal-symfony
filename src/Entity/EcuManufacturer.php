<?php

namespace App\Entity;

use App\Repository\EcuManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcuManufacturerRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_domain_model_ecumanufacturer')]
class EcuManufacturer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'master_manufacturer', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?EcuManufacturerMaster $masterManufacturer = null;

    /**
     * @var Collection<int, Ecu>
     */
    #[ORM\OneToMany(mappedBy: 'manufacturer', targetEntity: Ecu::class)]
    private Collection $ecus;

    private mixed $uploadedLogo = null;

    public function __construct()
    {
        $this->ecus = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('EcuManufacturer #' . ($this->id ?? 0));
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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getUploadedLogo(): mixed
    {
        return $this->uploadedLogo;
    }

    public function setUploadedLogo(mixed $uploadedLogo): static
    {
        $this->uploadedLogo = $uploadedLogo;

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

    public function getMasterManufacturer(): ?EcuManufacturerMaster
    {
        return $this->masterManufacturer;
    }

    public function setMasterManufacturer(?EcuManufacturerMaster $masterManufacturer): static
    {
        $this->masterManufacturer = $masterManufacturer;

        return $this;
    }

    /**
     * @return Collection<int, Ecu>
     */
    public function getEcus(): Collection
    {
        return $this->ecus;
    }
}
