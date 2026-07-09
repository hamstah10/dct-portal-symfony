<?php

namespace App\Entity;

use App\Repository\WatermarkAreaConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WatermarkAreaConfigRepository::class)]
#[ORM\Table(name: 'tfd_watermark_area_config')]
#[ORM\UniqueConstraint(name: 'uniq_area_portal', columns: ['area', 'portal_key'])]
class WatermarkAreaConfig
{
    public const AREA_VEHICLE_STAGE  = 'vehicle_stage';
    public const AREA_ECU_HINT       = 'ecu_hint';
    public const AREA_VEHICLE_ENGINE = 'vehicle_engine';

    public const AREAS = [
        'Leistungsstufen (VehicleStage)'  => self::AREA_VEHICLE_STAGE,
        'ECU-Einbauhinweise'              => self::AREA_ECU_HINT,
        'Fahrzeug-Bilder (VehicleEngine)' => self::AREA_VEHICLE_ENGINE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private string $area = '';

    #[ORM\Column(name: 'portal_key', length: 80, nullable: true)]
    private ?string $portalKey = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?WatermarkPreset $preset = null;

    #[ORM\Column]
    private bool $enabled = false;

    public function getId(): ?int { return $this->id; }

    public function getArea(): string { return $this->area; }
    public function setArea(string $area): static { $this->area = $area; return $this; }

    public function getPortalKey(): ?string { return $this->portalKey; }
    public function setPortalKey(?string $portalKey): static { $this->portalKey = $portalKey ? strtolower(trim($portalKey)) : null; return $this; }

    public function getAreaLabel(): string
    {
        return array_flip(self::AREAS)[$this->area] ?? $this->area;
    }

    public function getPreset(): ?WatermarkPreset { return $this->preset; }
    public function setPreset(?WatermarkPreset $preset): static { $this->preset = $preset; return $this; }

    public function isEnabled(): bool { return $this->enabled; }
    public function setEnabled(bool $enabled): static { $this->enabled = $enabled; return $this; }
}
