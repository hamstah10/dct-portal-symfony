<?php

namespace App\Entity;

use App\Repository\SupportTicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupportTicketRepository::class)]
#[ORM\Table(name: 'support_ticket')]
class SupportTicket
{
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Firma $firma = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Kunde $kunde = null;

    #[ORM\Column(length: 255)]
    private string $subject = '';

    #[ORM\Column(length: 30)]
    private string $status = self::STATUS_OPEN;

    #[ORM\Column(length: 20)]
    private string $priority = self::PRIORITY_NORMAL;

    #[ORM\Column]
    private int $messageCount = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastMessagePreview = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastMessageAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $closedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, SupportTicketMessage>
     */
    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: SupportTicketMessage::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $messages;

    /**
     * @var Collection<int, SupportTicketAttachment>
     */
    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: SupportTicketAttachment::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $attachments;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->status = self::STATUS_OPEN;
        $this->priority = self::PRIORITY_NORMAL;
        $this->messages = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    public function __toString(): string
    {
        $subject = trim($this->subject);
        if ('' === $subject) {
            $subject = 'Ticket';
        }

        return sprintf('%s - %s', $this->getTicketNumber(), $subject);
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            'Offen' => self::STATUS_OPEN,
            'In Bearbeitung' => self::STATUS_IN_PROGRESS,
            'Geschlossen' => self::STATUS_CLOSED,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getPriorityChoices(): array
    {
        return [
            'Niedrig' => self::PRIORITY_LOW,
            'Normal' => self::PRIORITY_NORMAL,
            'Hoch' => self::PRIORITY_HIGH,
            'Dringend' => self::PRIORITY_URGENT,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketNumber(): string
    {
        if (null === $this->id) {
            return 'T-NEU';
        }

        return sprintf('T-%06d', $this->id);
    }

    public function getFirma(): ?Firma
    {
        return $this->firma;
    }

    public function setFirma(?Firma $firma): static
    {
        $this->firma = $firma;

        return $this;
    }

    public function getKunde(): ?Kunde
    {
        return $this->kunde;
    }

    public function setKunde(?Kunde $kunde): static
    {
        $this->kunde = $kunde;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $normalized = strtolower(trim($status));
        $validValues = array_values(self::getStatusChoices());
        $this->status = in_array($normalized, $validValues, true) ? $normalized : self::STATUS_OPEN;

        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $normalized = strtolower(trim($priority));
        $validValues = array_values(self::getPriorityChoices());
        $this->priority = in_array($normalized, $validValues, true) ? $normalized : self::PRIORITY_NORMAL;

        return $this;
    }

    public function getMessageCount(): int
    {
        return $this->messageCount;
    }

    public function setMessageCount(int $messageCount): static
    {
        $this->messageCount = max(0, $messageCount);

        return $this;
    }

    public function getLastMessagePreview(): ?string
    {
        return $this->lastMessagePreview;
    }

    public function setLastMessagePreview(?string $lastMessagePreview): static
    {
        $this->lastMessagePreview = $lastMessagePreview;

        return $this;
    }

    public function getLastMessageAt(): ?\DateTimeImmutable
    {
        return $this->lastMessageAt;
    }

    public function setLastMessageAt(?\DateTimeImmutable $lastMessageAt): static
    {
        $this->lastMessageAt = $lastMessageAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeImmutable $closedAt): static
    {
        $this->closedAt = $closedAt;

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

    /**
     * @return Collection<int, SupportTicketMessage>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(SupportTicketMessage $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setTicket($this);
        }

        return $this;
    }

    public function removeMessage(SupportTicketMessage $message): static
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getTicket() === $this) {
                $message->setTicket(null);
            }
        }

        return $this;
    }

    public function registerNewMessage(string $messageText, \DateTimeImmutable $createdAt): void
    {
        $this->messageCount = max(0, $this->messageCount) + 1;
        $this->lastMessageAt = $createdAt;
        $this->updatedAt = $createdAt;
        $this->lastMessagePreview = self::buildPreview($messageText);
    }

    /**
     * @return Collection<int, SupportTicketAttachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(SupportTicketAttachment $attachment): static
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setTicket($this);
        }

        return $this;
    }

    public function removeAttachment(SupportTicketAttachment $attachment): static
    {
        if ($this->attachments->removeElement($attachment)) {
            if ($attachment->getTicket() === $this) {
                $attachment->setTicket(null);
            }
        }

        return $this;
    }

    private static function buildPreview(string $messageText): string
    {
        $text = trim(preg_replace('/\\s+/u', ' ', $messageText) ?? $messageText);
        if ('' === $text) {
            return '';
        }

        $maxLen = 160;
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            $preview = (string) mb_substr($text, 0, $maxLen);
            if (mb_strlen($text) > $maxLen) {
                $preview .= '...';
            }

            return $preview;
        }

        $preview = substr($text, 0, $maxLen);
        if (strlen($text) > $maxLen) {
            $preview .= '...';
        }

        return $preview;
    }
}
