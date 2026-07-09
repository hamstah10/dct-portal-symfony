<?php

namespace App\Entity;

use App\Repository\SupportEmailMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupportEmailMessageRepository::class)]
#[ORM\Table(name: 'support_email_message')]
#[ORM\Index(name: 'IDX_SUPPORT_EMAIL_ACCOUNT', columns: ['email_konto_id'])]
#[ORM\Index(name: 'IDX_SUPPORT_EMAIL_FOLDER', columns: ['folder'])]
#[ORM\Index(name: 'IDX_SUPPORT_EMAIL_IS_READ', columns: ['is_read'])]
#[ORM\Index(name: 'IDX_SUPPORT_EMAIL_IS_STARRED', columns: ['is_starred'])]
#[ORM\Index(name: 'IDX_SUPPORT_EMAIL_RECEIVED_AT', columns: ['received_at'])]
class SupportEmailMessage
{
    public const FOLDER_INBOX = 'inbox';
    public const FOLDER_SENT = 'sent';
    public const FOLDER_DRAFT = 'draft';
    public const FOLDER_SPAM = 'spam';
    public const FOLDER_TRASH = 'trash';
    public const FOLDER_ARCHIVE = 'archive';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?EmailKonto $emailKonto = null;

    #[ORM\Column(length: 30)]
    private string $folder = self::FOLDER_INBOX;

    #[ORM\Column(length: 255)]
    private string $subject = '';

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $fromName = null;

    #[ORM\Column(length: 190, nullable: true)]
    private ?string $fromEmail = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $toEmail = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $ccEmail = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bccEmail = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $bodyText = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bodyHtml = null;

    #[ORM\Column]
    private bool $isRead = false;

    #[ORM\Column]
    private bool $isStarred = false;

    #[ORM\Column]
    private bool $isDraft = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $externalMessageId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $inReplyToMessageId = null;

    /**
     * @var array<int, string>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $labels = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $receivedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->folder = self::FOLDER_INBOX;
    }

    public function __toString(): string
    {
        $subject = trim($this->subject);
        if ('' !== $subject) {
            return $subject;
        }

        return 'E-Mail';
    }

    /**
     * @return array<string, string>
     */
    public static function getFolderChoices(): array
    {
        return [
            'Inbox' => self::FOLDER_INBOX,
            'Sent' => self::FOLDER_SENT,
            'Draft' => self::FOLDER_DRAFT,
            'Spam' => self::FOLDER_SPAM,
            'Trash' => self::FOLDER_TRASH,
            'Archive' => self::FOLDER_ARCHIVE,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailKonto(): ?EmailKonto
    {
        return $this->emailKonto;
    }

    public function setEmailKonto(?EmailKonto $emailKonto): static
    {
        $this->emailKonto = $emailKonto;

        return $this;
    }

    public function getFolder(): string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): static
    {
        $normalized = strtolower(trim($folder));
        $validFolders = array_values(self::getFolderChoices());
        $this->folder = in_array($normalized, $validFolders, true) ? $normalized : self::FOLDER_INBOX;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = trim($subject);

        return $this;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(?string $fromName): static
    {
        $this->fromName = null === $fromName ? null : trim($fromName);

        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): static
    {
        $this->fromEmail = null === $fromEmail ? null : trim($fromEmail);

        return $this;
    }

    public function getToEmail(): ?string
    {
        return $this->toEmail;
    }

    public function setToEmail(?string $toEmail): static
    {
        $this->toEmail = null === $toEmail ? null : trim($toEmail);

        return $this;
    }

    public function getCcEmail(): ?string
    {
        return $this->ccEmail;
    }

    public function setCcEmail(?string $ccEmail): static
    {
        $this->ccEmail = null === $ccEmail ? null : trim($ccEmail);

        return $this;
    }

    public function getBccEmail(): ?string
    {
        return $this->bccEmail;
    }

    public function setBccEmail(?string $bccEmail): static
    {
        $this->bccEmail = null === $bccEmail ? null : trim($bccEmail);

        return $this;
    }

    public function getBodyText(): string
    {
        return $this->bodyText;
    }

    public function setBodyText(string $bodyText): static
    {
        $this->bodyText = $bodyText;

        return $this;
    }

    public function getBodyHtml(): ?string
    {
        return $this->bodyHtml;
    }

    public function setBodyHtml(?string $bodyHtml): static
    {
        $this->bodyHtml = $bodyHtml;

        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function isStarred(): bool
    {
        return $this->isStarred;
    }

    public function setIsStarred(bool $isStarred): static
    {
        $this->isStarred = $isStarred;

        return $this;
    }

    public function isDraft(): bool
    {
        return $this->isDraft;
    }

    public function setIsDraft(bool $isDraft): static
    {
        $this->isDraft = $isDraft;

        return $this;
    }

    public function getExternalMessageId(): ?string
    {
        return $this->externalMessageId;
    }

    public function setExternalMessageId(?string $externalMessageId): static
    {
        $this->externalMessageId = null === $externalMessageId ? null : trim($externalMessageId);

        return $this;
    }

    public function getInReplyToMessageId(): ?string
    {
        return $this->inReplyToMessageId;
    }

    public function setInReplyToMessageId(?string $inReplyToMessageId): static
    {
        $this->inReplyToMessageId = null === $inReplyToMessageId ? null : trim($inReplyToMessageId);

        return $this;
    }

    /**
     * @return array<int, string>|null
     */
    public function getLabels(): ?array
    {
        return $this->labels;
    }

    /**
     * @param array<int, string>|null $labels
     */
    public function setLabels(?array $labels): static
    {
        if (null === $labels) {
            $this->labels = null;

            return $this;
        }

        $cleanLabels = [];
        foreach ($labels as $label) {
            $value = trim((string) $label);
            if ('' === $value) {
                continue;
            }

            if (!in_array($value, $cleanLabels, true)) {
                $cleanLabels[] = $value;
            }
        }

        $this->labels = [] === $cleanLabels ? null : $cleanLabels;

        return $this;
    }

    public function getReceivedAt(): ?\DateTimeImmutable
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(?\DateTimeImmutable $receivedAt): static
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;

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
