<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LeadRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeadRepository::class)]
#[ORM\Table(name: 'crm_lead')]
#[ORM\Index(columns: ['status'], name: 'idx_crm_lead_status')]
#[ORM\Index(columns: ['created_at'], name: 'idx_crm_lead_created_at')]
#[ORM\HasLifecycleCallbacks]
class Lead
{
    public const STATUS_NEW = 'neu';
    public const STATUS_CONTACTED = 'kontaktiert';
    public const STATUS_QUALIFIED = 'qualifiziert';
    public const STATUS_OFFER = 'angebot';
    public const STATUS_WON = 'gewonnen';
    public const STATUS_LOST = 'verloren';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firma = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $vorname = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $nachname = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $telefon = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $quelle = null;

    #[ORM\Column(length: 50)]
    private string $status = self::STATUS_NEW;

    #[ORM\Column]
    private bool $newsletter = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notiz = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $showcasePasswordHash = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $showcasePasswordExpiresAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $showcasePasswordGeneratedAt = null;

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
        $firma = trim((string) $this->firma);
        if ('' !== $firma) {
            return $firma;
        }

        $name = trim(sprintf('%s %s', (string) $this->vorname, (string) $this->nachname));
        if ('' !== $name) {
            return $name;
        }

        return 'Lead #' . ($this->id ?? 'neu');
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            'Neu' => self::STATUS_NEW,
            'Kontaktiert' => self::STATUS_CONTACTED,
            'Qualifiziert' => self::STATUS_QUALIFIED,
            'Angebot' => self::STATUS_OFFER,
            'Gewonnen' => self::STATUS_WON,
            'Verloren' => self::STATUS_LOST,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirma(): ?string
    {
        return $this->firma;
    }

    public function setFirma(?string $firma): static
    {
        $this->firma = self::normalizeNullableString($firma);

        return $this;
    }

    public function getVorname(): ?string
    {
        return $this->vorname;
    }

    public function setVorname(?string $vorname): static
    {
        $this->vorname = self::normalizeNullableString($vorname);

        return $this;
    }

    public function getNachname(): ?string
    {
        return $this->nachname;
    }

    public function setNachname(?string $nachname): static
    {
        $this->nachname = self::normalizeNullableString($nachname);

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $normalized = self::normalizeNullableString($email);
        $this->email = null !== $normalized ? mb_strtolower($normalized) : null;

        return $this;
    }

    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setTelefon(?string $telefon): static
    {
        $this->telefon = self::normalizeNullableString($telefon);

        return $this;
    }

    public function getQuelle(): ?string
    {
        return $this->quelle;
    }

    public function setQuelle(?string $quelle): static
    {
        $this->quelle = self::normalizeNullableString($quelle);

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = trim($status);

        return $this;
    }

    public function getNotiz(): ?string
    {
        return $this->notiz;
    }

    public function isNewsletter(): bool
    {
        return $this->newsletter;
    }

    public function setNewsletter(bool $newsletter): static
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    public function setNotiz(?string $notiz): static
    {
        $this->notiz = self::normalizeNullableString($notiz);

        return $this;
    }

    public function getShowcasePasswordHash(): ?string
    {
        return $this->showcasePasswordHash;
    }

    public function setShowcasePasswordHash(?string $showcasePasswordHash): static
    {
        $this->showcasePasswordHash = self::normalizeNullableString($showcasePasswordHash);

        return $this;
    }

    public function getShowcasePasswordExpiresAt(): ?\DateTimeImmutable
    {
        return $this->showcasePasswordExpiresAt;
    }

    public function setShowcasePasswordExpiresAt(?\DateTimeImmutable $showcasePasswordExpiresAt): static
    {
        $this->showcasePasswordExpiresAt = $showcasePasswordExpiresAt;

        return $this;
    }

    public function getShowcasePasswordGeneratedAt(): ?\DateTimeImmutable
    {
        return $this->showcasePasswordGeneratedAt;
    }

    public function setShowcasePasswordGeneratedAt(?\DateTimeImmutable $showcasePasswordGeneratedAt): static
    {
        $this->showcasePasswordGeneratedAt = $showcasePasswordGeneratedAt;

        return $this;
    }

    public function hasShowcasePassword(): bool
    {
        return null !== $this->showcasePasswordHash && '' !== trim($this->showcasePasswordHash);
    }

    public function hasActiveShowcaseAccess(?\DateTimeImmutable $reference = null): bool
    {
        if (!$this->hasShowcasePassword()) {
            return false;
        }

        $reference ??= new \DateTimeImmutable();

        return null === $this->showcasePasswordExpiresAt || $this->showcasePasswordExpiresAt >= $reference;
    }

    public function setGeneratedShowcasePassword(string $plainPassword, ?\DateTimeImmutable $expiresAt): static
    {
        $this->showcasePasswordHash = password_hash($plainPassword, PASSWORD_BCRYPT);
        $this->showcasePasswordGeneratedAt = new \DateTimeImmutable();
        $this->showcasePasswordExpiresAt = $expiresAt;

        return $this;
    }

    public function clearShowcasePassword(): static
    {
        $this->showcasePasswordHash = null;
        $this->showcasePasswordGeneratedAt = null;
        $this->showcasePasswordExpiresAt = null;

        return $this;
    }

    public function isValidShowcasePassword(string $plainPassword, ?\DateTimeImmutable $reference = null): bool
    {
        if (!$this->hasActiveShowcaseAccess($reference)) {
            return false;
        }

        return password_verify($plainPassword, (string) $this->showcasePasswordHash);
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

    #[ORM\PrePersist]
    public function ensureTimestampsOnPersist(): void
    {
        $now = new \DateTimeImmutable();
        if (null === $this->createdAt) {
            $this->createdAt = $now;
        }
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    private static function normalizeNullableString(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $normalized = trim($value);

        return '' === $normalized ? null : $normalized;
    }
}
