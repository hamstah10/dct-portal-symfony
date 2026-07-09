<?php

namespace App\Entity;

use App\Repository\ProjectIdeaCardRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectIdeaCardRepository::class)]
#[ORM\Table(name: 'project_idea_card')]
#[ORM\Index(name: 'IDX_PROJECT_IDEA_STATUS_POSITION', columns: ['status', 'position'])]
class ProjectIdeaCard
{
    public const STATUS_NEW = 'new';
    public const STATUS_REVIEW = 'review';
    public const STATUS_PLANNED = 'planned';
    public const STATUS_ARCHIVED = 'archived';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $taskCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $assignedTo = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $targetDate = null;

    /**
     * @var list<string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $tags = null;

    /**
     * @var list<string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $images = null;

    #[ORM\Column(length: 32)]
    private string $status = self::STATUS_NEW;

    #[ORM\Column]
    private int $position = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->status = self::STATUS_NEW;
    }

    public function __toString(): string
    {
        return '' !== trim($this->title) ? $this->title : 'Idee';
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            'Neu' => self::STATUS_NEW,
            'Bewertung' => self::STATUS_REVIEW,
            'Geplant' => self::STATUS_PLANNED,
            'Archiv' => self::STATUS_ARCHIVED,
        ];
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
        $this->title = trim($title);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $description = null === $description ? null : trim($description);
        $this->description = '' !== (string) $description ? $description : null;

        return $this;
    }

    public function getTaskCode(): ?string
    {
        return $this->taskCode;
    }

    public function setTaskCode(?string $taskCode): static
    {
        $taskCode = null === $taskCode ? null : trim($taskCode);
        $this->taskCode = '' !== (string) $taskCode ? $taskCode : null;

        return $this;
    }

    public function getAssignedTo(): ?string
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?string $assignedTo): static
    {
        $assignedTo = null === $assignedTo ? null : trim($assignedTo);
        $this->assignedTo = '' !== (string) $assignedTo ? $assignedTo : null;

        return $this;
    }

    public function getTargetDate(): ?\DateTimeImmutable
    {
        return $this->targetDate;
    }

    public function setTargetDate(?\DateTimeImmutable $targetDate): static
    {
        $this->targetDate = $targetDate;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getTags(): array
    {
        return array_values(array_filter(array_map('strval', $this->tags ?? [])));
    }

    /**
     * @param list<string>|null $tags
     */
    public function setTags(?array $tags): static
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            static fn (mixed $tag): string => trim((string) $tag),
            $tags ?? []
        ))));
        $this->tags = [] !== $normalized ? $normalized : null;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getImages(): array
    {
        return array_values(array_filter(array_map('strval', $this->images ?? [])));
    }

    /**
     * @param list<string>|null $images
     */
    public function setImages(?array $images): static
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            static fn (mixed $path): string => trim((string) $path),
            $images ?? []
        ))));
        $this->images = [] !== $normalized ? $normalized : null;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $normalized = strtolower(trim($status));
        $validStatuses = array_values(self::getStatusChoices());
        $this->status = \in_array($normalized, $validStatuses, true) ? $normalized : self::STATUS_NEW;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = max(0, $position);

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
