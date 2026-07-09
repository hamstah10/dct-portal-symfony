<?php

namespace App\Entity;

use App\Repository\SupportTicketMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupportTicketMessageRepository::class)]
#[ORM\Table(name: 'support_ticket_message')]
class SupportTicketMessage
{
    public const SENDER_ADMIN = 'admin';
    public const SENDER_CUSTOMER = 'customer';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?SupportTicket $ticket = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Kunde $kunde = null;

    #[ORM\Column(name: 'sender_type', length: 20)]
    private string $sender = self::SENDER_ADMIN;

    #[ORM\Column(length: 255)]
    private string $senderName = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $message = '';

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->sender = self::SENDER_ADMIN;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): ?SupportTicket
    {
        return $this->ticket;
    }

    public function setTicket(?SupportTicket $ticket): static
    {
        $this->ticket = $ticket;

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

    public function getSender(): string
    {
        return $this->sender;
    }

    public function setSender(string $sender): static
    {
        $normalized = strtolower(trim($sender));
        if (in_array($normalized, [self::SENDER_ADMIN, self::SENDER_CUSTOMER], true)) {
            $this->sender = $normalized;
        } else {
            $this->sender = self::SENDER_ADMIN;
        }

        return $this;
    }

    public function getSenderType(): string
    {
        return $this->sender;
    }

    public function setSenderType(string $senderType): static
    {
        return $this->setSender($senderType);
    }

    public function getSenderName(): string
    {
        return $this->senderName;
    }

    public function setSenderName(string $senderName): static
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

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
}
