<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tx_chiptuningdatabase_ecu_master')]
class EcuMaster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(name: 'control_unit_kind', length: 10, nullable: true)]
    private ?string $controlUnitKind = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $processor = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'manufacturer_master', referencedColumnName: 'uid', nullable: true, onDelete: 'SET NULL')]
    private ?EcuManufacturerMaster $manufacturerMaster = null;

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('EcuMaster #' . ($this->id ?? 0));
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getControlUnitKind(): ?string
    {
        return $this->controlUnitKind;
    }

    public function setControlUnitKind(?string $controlUnitKind): static
    {
        $this->controlUnitKind = $controlUnitKind;

        return $this;
    }

    public function getProcessor(): ?string
    {
        return $this->processor;
    }

    public function setProcessor(?string $processor): static
    {
        $this->processor = $processor;

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

    public function getManufacturerMaster(): ?EcuManufacturerMaster
    {
        return $this->manufacturerMaster;
    }

    public function setManufacturerMaster(?EcuManufacturerMaster $manufacturerMaster): static
    {
        $this->manufacturerMaster = $manufacturerMaster;

        return $this;
    }

    /** @return array<string, mixed> */
    public function getSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'controlUnitKind' => $this->controlUnitKind,
            'manufacturerName' => $this->manufacturerMaster?->getName() ?? '',
        ];
    }
}
