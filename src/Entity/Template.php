<?php

namespace App\Entity;

use App\Repository\TemplateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TemplateRepository::class)]
class Template
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $beschreibung = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $vorschaubilder = null;

    #[ORM\Column]
    private bool $aktiv = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var array<int, mixed>
     */
    private array $uploadedImages = [];

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name ?? ('Template #' . $this->id);
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

    public function getBeschreibung(): ?string
    {
        return $this->beschreibung;
    }

    public function setBeschreibung(?string $beschreibung): static
    {
        $this->beschreibung = $beschreibung;

        return $this;
    }

    public function getVorschaubilder(): ?string
    {
        return $this->vorschaubilder;
    }

    public function setVorschaubilder(?string $vorschaubilder): static
    {
        $this->vorschaubilder = $vorschaubilder;

        return $this;
    }

    public function isAktiv(): bool
    {
        return $this->aktiv;
    }

    public function setAktiv(bool $aktiv): static
    {
        $this->aktiv = $aktiv;

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

    /**
     * @return array<int, mixed>
     */
    public function getUploadedImages(): array
    {
        return $this->uploadedImages;
    }

    /**
     * @param array<int, mixed> $uploadedImages
     */
    public function setUploadedImages(array $uploadedImages): static
    {
        $this->uploadedImages = $uploadedImages;

        return $this;
    }
}
