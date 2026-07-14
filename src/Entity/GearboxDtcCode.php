<?php

namespace App\Entity;

use App\Repository\GearboxDtcCodeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * DSG-spezifische Fehlercodes (OBDII/VAG) für die DSG-Seite im Superadmin.
 */
#[ORM\Entity(repositoryClass: GearboxDtcCodeRepository::class)]
#[ORM\Table(name: 'tfd_gearbox_dtc_code')]
class GearboxDtcCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'obd_code', length: 20)]
    private string $obdCode = '';

    #[ORM\Column(name: 'vag_code', length: 20, nullable: true)]
    private ?string $vagCode = null;

    #[ORM\Column(length: 255)]
    private string $description = '';

    #[ORM\Column]
    private int $sorting = 0;

    #[ORM\Column(options: ['default' => true])]
    private bool $active = true;

    public function __toString(): string
    {
        return '' !== $this->obdCode ? $this->obdCode : ('GearboxDtcCode #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObdCode(): string
    {
        return $this->obdCode;
    }

    public function setObdCode(string $obdCode): static
    {
        $this->obdCode = $obdCode;

        return $this;
    }

    public function getVagCode(): ?string
    {
        return $this->vagCode;
    }

    public function setVagCode(?string $vagCode): static
    {
        $vagCode = trim((string) ($vagCode ?? ''));
        $this->vagCode = '' !== $vagCode ? $vagCode : null;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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
}
