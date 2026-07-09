<?php

namespace App\Entity;

use App\Repository\ToolNewsSourceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolNewsSourceRepository::class)]
class ToolNewsSource
{
    public const TYPE_NEWS = 'news';
    public const TYPE_BLOG = 'blog';
    public const TYPE_FORUM = 'forum';
    public const TYPE_OTHER = 'other';

    /** @var list<string> */
    public const TYPES = [self::TYPE_NEWS, self::TYPE_BLOG, self::TYPE_FORUM, self::TYPE_OTHER];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ToolProvider $provider = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 500)]
    private ?string $url = null;

    #[ORM\Column(length: 20)]
    private string $type = self::TYPE_NEWS;

    #[ORM\Column(length: 150)]
    private ?string $spiderName = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastRunAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSuccessAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lastError = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name ?? ('News-Quelle #' . $this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProvider(): ?ToolProvider
    {
        return $this->provider;
    }

    public function setProvider(?ToolProvider $provider): static
    {
        $this->provider = $provider;

        return $this;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = in_array($type, self::TYPES, true) ? $type : self::TYPE_OTHER;

        return $this;
    }

    public function getSpiderName(): ?string
    {
        return $this->spiderName;
    }

    public function setSpiderName(string $spiderName): static
    {
        $this->spiderName = $spiderName;

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

    public function getLastRunAt(): ?\DateTimeImmutable
    {
        return $this->lastRunAt;
    }

    public function setLastRunAt(?\DateTimeImmutable $lastRunAt): static
    {
        $this->lastRunAt = $lastRunAt;

        return $this;
    }

    public function getLastSuccessAt(): ?\DateTimeImmutable
    {
        return $this->lastSuccessAt;
    }

    public function setLastSuccessAt(?\DateTimeImmutable $lastSuccessAt): static
    {
        $this->lastSuccessAt = $lastSuccessAt;

        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $lastError): static
    {
        $this->lastError = $lastError;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
