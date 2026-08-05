<?php

namespace App\Entity;

use App\Repository\AdminIssueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminIssueRepository::class)]
#[ORM\Table(name: 'admin_issue')]
#[ORM\Index(name: 'idx_admin_issue_status', columns: ['status'])]
#[ORM\Index(name: 'idx_admin_issue_created_at', columns: ['created_at'])]
class AdminIssue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    #[ORM\Column(length: 50)]
    private string $category = 'other';

    #[ORM\Column(length: 50)]
    private string $status = 'open';

    #[ORM\Column(length: 50)]
    private string $priority = 'normal';

    #[ORM\Column(type: Types::JSON)]
    private array $labels = [];

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'assigned_to_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?User $assignedTo = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pageUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pageTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $selector = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $entityType = null;

    #[ORM\Column(nullable: true)]
    private ?int $entityId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entityLabel = null;


    #[ORM\Column(nullable: true)]
    private ?int $stageId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $field = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $currentValue = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $screenshotDataUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $audioUrl = null;

    /**
     * @var Collection<int, AdminIssueComment>
     */
    #[ORM\OneToMany(targetEntity: AdminIssueComment::class, mappedBy: 'issue', cascade: ['remove'])]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $comments;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $resolvedAt = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
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
        $this->title = $title;
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

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        if ($status === 'resolved' && !$this->resolvedAt) {
            $this->resolvedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function setLabels(array $labels): static
    {
        $this->labels = $labels;
        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): static
    {
        $this->assignedTo = $assignedTo;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getPageUrl(): ?string
    {
        return $this->pageUrl;
    }

    public function setPageUrl(?string $pageUrl): static
    {
        $this->pageUrl = $pageUrl;
        return $this;
    }

    public function getPageTitle(): ?string
    {
        return $this->pageTitle;
    }

    public function setPageTitle(?string $pageTitle): static
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    public function getSelector(): ?string
    {
        return $this->selector;
    }

    public function setSelector(?string $selector): static
    {
        $this->selector = $selector;
        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(?string $entityType): static
    {
        $this->entityType = $entityType;
        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): static
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getEntityLabel(): ?string
    {
        return $this->entityLabel;
    }

    public function setEntityLabel(?string $entityLabel): static
    {
        $this->entityLabel = $entityLabel;
        return $this;
    }


    public function getStageId(): ?int
    {
        return $this->stageId;
    }

    public function setStageId(?int $stageId): static
    {
        $this->stageId = $stageId;
        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): static
    {
        $this->field = $field;
        return $this;
    }

    public function getCurrentValue(): ?string
    {
        return $this->currentValue;
    }

    public function setCurrentValue(?string $currentValue): static
    {
        $this->currentValue = $currentValue;
        return $this;
    }

    public function getScreenshotDataUrl(): ?string
    {
        return $this->screenshotDataUrl;
    }

    public function setScreenshotDataUrl(?string $screenshotDataUrl): static
    {
        $this->screenshotDataUrl = $screenshotDataUrl;
        return $this;
    }

    public function getAudioUrl(): ?string
    {
        return $this->audioUrl;
    }

    public function setAudioUrl(?string $audioUrl): static
    {
        $this->audioUrl = $audioUrl;
        return $this;
    }

    /**
     * @return Collection<int, AdminIssueComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(AdminIssueComment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setIssue($this);
        }
        return $this;
    }

    public function removeComment(AdminIssueComment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getIssue() === $this) {
                $comment->setIssue(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getResolvedAt(): ?\DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?\DateTimeImmutable $resolvedAt): static
    {
        $this->resolvedAt = $resolvedAt;
        return $this;
    }
}
