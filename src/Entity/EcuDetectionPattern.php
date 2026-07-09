<?php

namespace App\Entity;

use App\Repository\EcuDetectionPatternRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcuDetectionPatternRepository::class)]
class EcuDetectionPattern
{
    public const TYPE_MANUFACTURER = 'manufacturer';
    public const TYPE_ECU_FAMILY = 'ecu_family';
    public const TYPE_ECU_NAME = 'ecu_name';
    public const TYPE_MCU = 'mcu';
    public const TYPE_READ_METHOD = 'read_method';
    public const TYPE_PROTOCOL = 'protocol';
    public const TYPE_VEHICLE_HINT = 'vehicle_hint';

    /** @var list<string> */
    public const TYPES = [
        self::TYPE_MANUFACTURER,
        self::TYPE_ECU_FAMILY,
        self::TYPE_ECU_NAME,
        self::TYPE_MCU,
        self::TYPE_READ_METHOD,
        self::TYPE_PROTOCOL,
        self::TYPE_VEHICLE_HINT,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private string $type = self::TYPE_ECU_NAME;

    #[ORM\Column(length: 255)]
    private ?string $pattern = null;

    #[ORM\Column]
    private bool $isRegex = true;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $normalizedValue = null;

    #[ORM\Column(type: 'float')]
    private float $scoreWeight = 1.0;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name ?? ('Erkennungsmuster #' . $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = in_array($type, self::TYPES, true) ? $type : self::TYPE_ECU_NAME;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): static
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function isRegex(): bool
    {
        return $this->isRegex;
    }

    public function setIsRegex(bool $isRegex): static
    {
        $this->isRegex = $isRegex;

        return $this;
    }

    public function getNormalizedValue(): ?string
    {
        return $this->normalizedValue;
    }

    public function setNormalizedValue(?string $normalizedValue): static
    {
        $this->normalizedValue = $normalizedValue;

        return $this;
    }

    public function getScoreWeight(): float
    {
        return $this->scoreWeight;
    }

    public function setScoreWeight(float $scoreWeight): static
    {
        $this->scoreWeight = $scoreWeight;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
