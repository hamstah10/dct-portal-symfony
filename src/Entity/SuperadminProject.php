<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SuperadminProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuperadminProjectRepository::class)]
#[ORM\Table(name: 'superadmin_project')]
#[ORM\HasLifecycleCallbacks]
class SuperadminProject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 2000)]
    private string $url = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $loginUsername = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $loginPasswordEncrypted = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
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
        $this->name = trim($name);
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = trim($url);
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = null === $description ? null : trim($description);
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = null === $imagePath ? null : trim($imagePath);
        return $this;
    }

    public function getLoginUsername(): ?string
    {
        return $this->loginUsername;
    }

    public function setLoginUsername(?string $loginUsername): static
    {
        $this->loginUsername = null === $loginUsername || '' === trim($loginUsername) ? null : trim($loginUsername);
        return $this;
    }

    public function getLoginPasswordEncrypted(): ?string
    {
        return $this->loginPasswordEncrypted;
    }

    public function setLoginPasswordEncrypted(?string $loginPasswordEncrypted): static
    {
        $this->loginPasswordEncrypted = $loginPasswordEncrypted;
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
