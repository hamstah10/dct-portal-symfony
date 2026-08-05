<?php

namespace App\Entity;

use App\Repository\AdminIssueCommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminIssueCommentRepository::class)]
#[ORM\Table(name: 'admin_issue_comment')]
#[ORM\Index(name: 'idx_admin_issue_comment_issue_id', columns: ['issue_id'])]
class AdminIssueComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AdminIssue::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'issue_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?AdminIssue $issue = null;

    #[ORM\Column(nullable: false)]
    private ?int $authorId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $authorName = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $text = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIssue(): ?AdminIssue
    {
        return $this->issue;
    }

    public function setIssue(?AdminIssue $issue): static
    {
        $this->issue = $issue;
        return $this;
    }

    public function getAuthorId(): ?int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $authorId): static
    {
        $this->authorId = $authorId;
        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(?string $authorName): static
    {
        $this->authorName = $authorName;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
