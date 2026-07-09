<?php

namespace App\Entity;

use App\Repository\SuperadminCalendarEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuperadminCalendarEntryRepository::class)]
#[ORM\Table(name: 'superadmin_calendar_entry')]
class SuperadminCalendarEntry
{
    public const CUSTOMER_TYPE_EXISTING = 'existing';
    public const CUSTOMER_TYPE_NEW = 'new';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Firma $firma = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Kunde $kunde = null;

    #[ORM\Column(length: 20)]
    private string $customerType = self::CUSTOMER_TYPE_EXISTING;

    #[ORM\Column(length: 190, nullable: true)]
    private ?string $newCustomerName = null;

    #[ORM\Column(length: 190, nullable: true)]
    private ?string $newCustomerEmail = null;

    #[ORM\Column(length: 255)]
    private string $reason = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $startsAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endsAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->startsAt = $now;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCustomerType(): string
    {
        return $this->customerType;
    }

    public function setCustomerType(string $customerType): static
    {
        $this->customerType = in_array($customerType, [self::CUSTOMER_TYPE_EXISTING, self::CUSTOMER_TYPE_NEW], true)
            ? $customerType
            : self::CUSTOMER_TYPE_EXISTING;

        return $this;
    }

    public function getNewCustomerName(): ?string
    {
        return $this->newCustomerName;
    }

    public function setNewCustomerName(?string $newCustomerName): static
    {
        $this->newCustomerName = $newCustomerName;

        return $this;
    }

    public function getNewCustomerEmail(): ?string
    {
        return $this->newCustomerEmail;
    }

    public function setNewCustomerEmail(?string $newCustomerEmail): static
    {
        $this->newCustomerEmail = $newCustomerEmail;

        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getStartsAt(): \DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

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
}
