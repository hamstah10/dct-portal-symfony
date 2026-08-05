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

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false)]
    private ?User $author = null;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): static
    {
        $this->author = $author;
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
