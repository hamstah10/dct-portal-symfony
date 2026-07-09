<?php

namespace App\Entity;

use App\Repository\WatermarkPresetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WatermarkPresetRepository::class)]
#[ORM\Table(name: 'tfd_watermark_preset')]
class WatermarkPreset
{
    public const POSITION_TOP_LEFT      = 'top-left';
    public const POSITION_TOP_CENTER    = 'top-center';
    public const POSITION_TOP_RIGHT     = 'top-right';
    public const POSITION_MIDDLE_LEFT   = 'middle-left';
    public const POSITION_MIDDLE_CENTER = 'middle-center';
    public const POSITION_MIDDLE_RIGHT  = 'middle-right';
    public const POSITION_BOTTOM_LEFT   = 'bottom-left';
    public const POSITION_BOTTOM_CENTER = 'bottom-center';
    public const POSITION_BOTTOM_RIGHT  = 'bottom-right';
    public const POSITION_DIAGONAL      = 'diagonal';

    public const POSITIONS = [
        'Oben links'         => self::POSITION_TOP_LEFT,
        'Oben Mitte'         => self::POSITION_TOP_CENTER,
        'Oben rechts'        => self::POSITION_TOP_RIGHT,
        'Mitte links'        => self::POSITION_MIDDLE_LEFT,
        'Mitte'              => self::POSITION_MIDDLE_CENTER,
        'Mitte rechts'       => self::POSITION_MIDDLE_RIGHT,
        'Unten links'        => self::POSITION_BOTTOM_LEFT,
        'Unten Mitte'        => self::POSITION_BOTTOM_CENTER,
        'Unten rechts'       => self::POSITION_BOTTOM_RIGHT,
        'Diagonal (schräg)'  => self::POSITION_DIAGONAL,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(length: 30, options: ['default' => self::POSITION_BOTTOM_RIGHT])]
    private string $position = self::POSITION_BOTTOM_RIGHT;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2, options: ['default' => '0.50'])]
    private string $opacity = '0.50';

    #[ORM\Column(nullable: true)]
    private ?int $fontSize = 18;

    #[ORM\Column(length: 30, nullable: true, options: ['default' => '#ffffff'])]
    private ?string $textColor = '#ffffff';

    #[ORM\Column(nullable: true)]
    private ?int $imageWidth = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $fillWidth = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, options: ['default' => '3.00'])]
    private string $marginPercent = '3.00';

    #[ORM\Column]
    private bool $active = true;

    public function __toString(): string
    {
        return $this->name ?: ('WatermarkPreset #' . ($this->id ?? 0));
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getImagePath(): ?string { return $this->imagePath; }
    public function setImagePath(?string $imagePath): static { $this->imagePath = $imagePath; return $this; }

    public function getText(): ?string { return $this->text; }
    public function setText(?string $text): static { $this->text = $text; return $this; }

    public function getPosition(): string { return $this->position; }
    public function setPosition(string $position): static { $this->position = $position; return $this; }

    public function getOpacity(): string { return $this->opacity; }
    public function setOpacity(string $opacity): static { $this->opacity = $opacity; return $this; }

    public function getFontSize(): ?int { return $this->fontSize; }
    public function setFontSize(?int $fontSize): static { $this->fontSize = $fontSize; return $this; }

    public function getTextColor(): ?string { return $this->textColor; }
    public function setTextColor(?string $textColor): static { $this->textColor = $textColor; return $this; }

    public function getImageWidth(): ?int { return $this->imageWidth; }
    public function setImageWidth(?int $imageWidth): static { $this->imageWidth = $imageWidth; return $this; }

    public function isFillWidth(): bool { return $this->fillWidth; }
    public function setFillWidth(bool $fillWidth): static { $this->fillWidth = $fillWidth; return $this; }

    public function getMarginPercent(): string { return $this->marginPercent; }
    public function setMarginPercent(string $marginPercent): static { $this->marginPercent = $marginPercent; return $this; }

    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): static { $this->active = $active; return $this; }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'imagePath'     => $this->imagePath,
            'text'          => $this->text,
            'position'      => $this->position,
            'opacity'       => (float) $this->opacity,
            'fontSize'      => $this->fontSize,
            'textColor'     => $this->textColor,
            'imageWidth'    => $this->imageWidth,
            'fillWidth'     => $this->fillWidth,
            'marginPercent' => (float) $this->marginPercent,
        ];
    }
}
