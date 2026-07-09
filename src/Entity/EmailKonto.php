<?php

namespace App\Entity;

use App\Repository\EmailKontoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailKontoRepository::class)]
#[ORM\Table(name: 'email_konto')]
#[ORM\Index(name: 'IDX_EMAIL_KONTO_ACTIVE', columns: ['active'])]
#[ORM\Index(name: 'IDX_EMAIL_KONTO_FETCH_ENABLED', columns: ['fetch_enabled'])]
#[ORM\HasLifecycleCallbacks]
class EmailKonto
{
    public const ENCRYPTION_NONE = 'none';
    public const ENCRYPTION_SSL = 'ssl';
    public const ENCRYPTION_TLS = 'tls';
    public const ENCRYPTION_STARTTLS = 'starttls';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Firma $firma = null;

    #[ORM\Column(length: 150)]
    private string $name = '';

    #[ORM\Column(length: 190)]
    private string $emailAddress = '';

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $fromName = null;

    #[ORM\Column(length: 255)]
    private string $imapHost = '';

    #[ORM\Column]
    private int $imapPort = 993;

    #[ORM\Column(length: 20)]
    private string $imapEncryption = self::ENCRYPTION_SSL;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imapUsername = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $imapPassword = null;

    #[ORM\Column(length: 120)]
    private string $imapFolder = 'INBOX';

    #[ORM\Column(length: 255)]
    private string $smtpHost = '';

    #[ORM\Column]
    private int $smtpPort = 587;

    #[ORM\Column(length: 20)]
    private string $smtpEncryption = self::ENCRYPTION_TLS;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $smtpUsername = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $smtpPassword = null;

    /**
     * @var Collection<int, EmailLabel>
     */
    #[ORM\ManyToMany(targetEntity: EmailLabel::class, inversedBy: 'emailKonten')]
    #[ORM\JoinTable(name: 'email_konto_label')]
    private Collection $labels;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private bool $fetchEnabled = true;

    #[ORM\Column]
    private bool $sendEnabled = true;

    #[ORM\Column]
    private int $sorting = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->labels = new ArrayCollection();
    }

    public function __toString(): string
    {
        $name = trim($this->name);
        if ('' !== $name) {
            return $name;
        }

        $mail = trim($this->emailAddress);
        if ('' !== $mail) {
            return $mail;
        }

        return 'E-Mail Konto';
    }

    /**
     * @return array<string, string>
     */
    public static function getEncryptionChoices(): array
    {
        return [
            'Keine' => self::ENCRYPTION_NONE,
            'SSL' => self::ENCRYPTION_SSL,
            'TLS' => self::ENCRYPTION_TLS,
            'STARTTLS' => self::ENCRYPTION_STARTTLS,
        ];
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        if (null === $this->createdAt) {
            $this->createdAt = $now;
        }

        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = trim($name);

        return $this;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): static
    {
        $this->emailAddress = trim($emailAddress);

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

    public function getImapHost(): string
    {
        return $this->imapHost;
    }

    public function setImapHost(string $imapHost): static
    {
        $this->imapHost = trim($imapHost);

        return $this;
    }

    public function getImapPort(): int
    {
        return $this->imapPort;
    }

    public function setImapPort(int $imapPort): static
    {
        $this->imapPort = max(1, $imapPort);

        return $this;
    }

    public function getImapEncryption(): string
    {
        return $this->imapEncryption;
    }

    public function setImapEncryption(string $imapEncryption): static
    {
        $normalized = strtolower(trim($imapEncryption));
        $validValues = array_values(self::getEncryptionChoices());
        $this->imapEncryption = in_array($normalized, $validValues, true) ? $normalized : self::ENCRYPTION_SSL;

        return $this;
    }

    public function getImapUsername(): ?string
    {
        return $this->imapUsername;
    }

    public function setImapUsername(?string $imapUsername): static
    {
        $this->imapUsername = null === $imapUsername ? null : trim($imapUsername);

        return $this;
    }

    public function getImapPassword(): ?string
    {
        return $this->imapPassword;
    }

    public function setImapPassword(?string $imapPassword): static
    {
        $this->imapPassword = null === $imapPassword ? null : trim($imapPassword);

        return $this;
    }

    public function getImapFolder(): string
    {
        return $this->imapFolder;
    }

    public function setImapFolder(string $imapFolder): static
    {
        $folder = trim($imapFolder);
        $this->imapFolder = '' === $folder ? 'INBOX' : $folder;

        return $this;
    }

    public function getSmtpHost(): string
    {
        return $this->smtpHost;
    }

    public function setSmtpHost(string $smtpHost): static
    {
        $this->smtpHost = trim($smtpHost);

        return $this;
    }

    public function getSmtpPort(): int
    {
        return $this->smtpPort;
    }

    public function setSmtpPort(int $smtpPort): static
    {
        $this->smtpPort = max(1, $smtpPort);

        return $this;
    }

    public function getSmtpEncryption(): string
    {
        return $this->smtpEncryption;
    }

    public function setSmtpEncryption(string $smtpEncryption): static
    {
        $normalized = strtolower(trim($smtpEncryption));
        $validValues = array_values(self::getEncryptionChoices());
        $this->smtpEncryption = in_array($normalized, $validValues, true) ? $normalized : self::ENCRYPTION_TLS;

        return $this;
    }

    public function getSmtpUsername(): ?string
    {
        return $this->smtpUsername;
    }

    public function setSmtpUsername(?string $smtpUsername): static
    {
        $this->smtpUsername = null === $smtpUsername ? null : trim($smtpUsername);

        return $this;
    }

    public function getSmtpPassword(): ?string
    {
        return $this->smtpPassword;
    }

    public function setSmtpPassword(?string $smtpPassword): static
    {
        $this->smtpPassword = null === $smtpPassword ? null : trim($smtpPassword);

        return $this;
    }

    /**
     * @return Collection<int, EmailLabel>
     */
    public function getLabels(): Collection
    {
        return $this->labels;
    }

    public function addLabel(EmailLabel $label): static
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
        }

        return $this;
    }

    public function removeLabel(EmailLabel $label): static
    {
        $this->labels->removeElement($label);

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function isFetchEnabled(): bool
    {
        return $this->fetchEnabled;
    }

    public function setFetchEnabled(bool $fetchEnabled): static
    {
        $this->fetchEnabled = $fetchEnabled;

        return $this;
    }

    public function isSendEnabled(): bool
    {
        return $this->sendEnabled;
    }

    public function setSendEnabled(bool $sendEnabled): static
    {
        $this->sendEnabled = $sendEnabled;

        return $this;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = null === $notes ? null : trim($notes);

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
