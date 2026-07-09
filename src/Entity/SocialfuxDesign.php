<?php

namespace App\Entity;

use App\Repository\SocialfuxDesignRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SocialfuxDesignRepository::class)]
#[ORM\Table(name: 'socialfux_design')]
#[ORM\HasLifecycleCallbacks]
class SocialfuxDesign
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 100)]
    private string $canvasPreset = '';

    #[ORM\Column]
    private int $canvasWidth = 1080;

    #[ORM\Column]
    private int $canvasHeight = 1080;

    #[ORM\Column(length: 50)]
    private string $canvasBackground = '#ffffff';

    #[ORM\Column(type: Types::JSON)]
    private array $elements = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $thumbnail = null;

    #[ORM\Column]
    private bool $isDeleted = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getCanvasPreset(): string { return $this->canvasPreset; }
    public function setCanvasPreset(string $canvasPreset): static { $this->canvasPreset = $canvasPreset; return $this; }
    public function getCanvasWidth(): int { return $this->canvasWidth; }
    public function setCanvasWidth(int $canvasWidth): static { $this->canvasWidth = $canvasWidth; return $this; }
    public function getCanvasHeight(): int { return $this->canvasHeight; }
    public function setCanvasHeight(int $canvasHeight): static { $this->canvasHeight = $canvasHeight; return $this; }
    public function getCanvasBackground(): string { return $this->canvasBackground; }
    public function setCanvasBackground(string $canvasBackground): static { $this->canvasBackground = $canvasBackground; return $this; }
    public function getElements(): array { return $this->elements; }
    public function setElements(array $elements): static { $this->elements = $elements; return $this; }
    public function getThumbnail(): ?string { return $this->thumbnail; }
    public function setThumbnail(?string $thumbnail): static { $this->thumbnail = $thumbnail; return $this; }
    public function isDeleted(): bool { return $this->isDeleted; }
    public function setIsDeleted(bool $isDeleted): static { $this->isDeleted = $isDeleted; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'canvasPreset' => $this->canvasPreset,
            'canvasWidth' => $this->canvasWidth,
            'canvasHeight' => $this->canvasHeight,
            'canvasBackground' => $this->canvasBackground,
            'elements' => $this->elements,
            'thumbnail' => $this->thumbnail,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt->format('c'),
        ];
    }
}
