<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SuperadminDashboardLinkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuperadminDashboardLinkRepository::class)]
#[ORM\Table(name: 'superadmin_dashboard_link')]
#[ORM\Index(name: 'IDX_SUPERADMIN_DASHBOARD_LINK_ACTIVE_SORT', columns: ['active', 'sort_order'])]
#[ORM\HasLifecycleCallbacks]
class SuperadminDashboardLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $title = '';

    #[ORM\Column(length: 2048)]
    private string $url = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $iconName = null;

    #[ORM\Column(name: 'sort_order')]
    private int $sortOrder = 0;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(name: 'open_in_new_tab')]
    private bool $openInNewTab = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function __toString(): string
    {
        return $this->title;
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
        $this->title = mb_substr(trim($title), 0, 120);

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = mb_substr(trim($url), 0, 2048);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $description = null !== $description ? trim($description) : null;
        $this->description = '' === $description ? null : mb_substr((string) $description, 0, 255);

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $category = null !== $category ? trim($category) : null;
        $this->category = '' === $category ? null : mb_substr((string) $category, 0, 80);

        return $this;
    }

    public function getIconName(): ?string
    {
        return $this->iconName;
    }

    public function setIconName(?string $iconName): static
    {
        $iconName = null !== $iconName ? trim($iconName) : null;
        $this->iconName = '' === $iconName ? null : mb_substr((string) $iconName, 0, 80);

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

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

    public function isOpenInNewTab(): bool
    {
        return $this->openInNewTab;
    }

    public function setOpenInNewTab(bool $openInNewTab): static
    {
        $this->openInNewTab = $openInNewTab;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function initializeTimestamps(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt ??= $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
