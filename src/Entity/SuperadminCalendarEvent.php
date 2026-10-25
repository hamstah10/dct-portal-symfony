<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/** Allgemeiner Termin im Operations-Kalender (nicht an einen Kunden gebunden). */
#[ORM\Entity]
#[ORM\Table(name: 'superadmin_calendar_event')]
#[ORM\Index(name: 'IDX_SUPERADMIN_CALENDAR_EVENT_STARTS', columns: ['starts_at'])]
class SuperadminCalendarEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Optionale Firmen-Zuordnung. NULL = Termin gilt für alle Firmen. */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Firma $firma = null;

    #[ORM\Column(length: 190)]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $guests = [];

    #[ORM\Column(name: 'calendar_id', length: 20)]
    private string $calendarId = 'support';

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(name: 'all_day', type: Types::BOOLEAN)]
    private bool $allDay = false;

    #[ORM\Column(name: 'starts_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $startsAt;

    #[ORM\Column(name: 'ends_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $endsAt;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->startsAt = $now;
        $this->endsAt = $now;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int { return $this->id; }

    public function getFirma(): ?Firma { return $this->firma; }

    public function setFirma(?Firma $firma): static { $this->firma = $firma; return $this; }

    public function getTitle(): string { return $this->title; }

    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getDescription(): ?string { return $this->description; }

    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getLocation(): ?string { return $this->location; }

    public function setLocation(?string $location): static { $this->location = $location; return $this; }

    /** @return list<string> */
    public function getGuests(): array { return $this->guests; }

    /** @param list<string> $guests */
    public function setGuests(array $guests): static { $this->guests = array_values($guests); return $this; }

    public function getCalendarId(): string { return $this->calendarId; }

    public function setCalendarId(string $calendarId): static { $this->calendarId = $calendarId; return $this; }

    public function getColor(): ?string { return $this->color; }

    public function setColor(?string $color): static { $this->color = $color; return $this; }

    public function isAllDay(): bool { return $this->allDay; }

    public function setAllDay(bool $allDay): static { $this->allDay = $allDay; return $this; }

    public function getStartsAt(): \DateTimeImmutable { return $this->startsAt; }

    public function setStartsAt(\DateTimeImmutable $startsAt): static { $this->startsAt = $startsAt; return $this; }

    public function getEndsAt(): \DateTimeImmutable { return $this->endsAt; }

    public function setEndsAt(\DateTimeImmutable $endsAt): static { $this->endsAt = $endsAt; return $this; }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }
}
