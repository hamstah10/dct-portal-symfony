<?php

namespace App\Entity;

use App\Repository\EcuOptionHintRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcuOptionHintRepository::class)]
#[ORM\Table(name: 'tx_chiptuningdatabase_ecu_option_hint')]
#[ORM\Index(columns: ['module_slug'], name: 'IDX_ECU_OPTION_HINT_MODULE_SLUG')]
#[ORM\Index(columns: ['brand_slug'], name: 'IDX_ECU_OPTION_HINT_BRAND_SLUG')]
#[ORM\Index(columns: ['manufacturer'], name: 'IDX_ECU_OPTION_HINT_MANUFACTURER')]
#[ORM\Index(columns: ['model'], name: 'IDX_ECU_OPTION_HINT_MODEL')]
#[ORM\Index(columns: ['active'], name: 'IDX_ECU_OPTION_HINT_ACTIVE')]
#[ORM\UniqueConstraint(name: 'UNIQ_ECU_OPTION_HINT_SIGNATURE', columns: ['signature_hash'])]
#[ORM\HasLifecycleCallbacks]
class EcuOptionHint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $moduleName = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $moduleSlug = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $moduleUrl = null;

    #[ORM\Column(length: 255)]
    private string $brandName = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $brandSlug = null;

    #[ORM\Column(length: 255)]
    private string $manufacturer = '';

    #[ORM\Column(length: 255)]
    private string $model = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notesDe = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $raw = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sourceFile = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $scrapedAt = null;

    #[ORM\Column(length: 64)]
    private string $signatureHash = '';

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __toString(): string
    {
        $summary = trim(sprintf('%s %s %s', $this->brandName, $this->manufacturer, $this->model));

        return '' !== $summary ? $summary : ('EcuOptionHint #' . ($this->id ?? 0));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function setModuleName(string $moduleName): static
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    public function getModuleSlug(): ?string
    {
        return $this->moduleSlug;
    }

    public function setModuleSlug(?string $moduleSlug): static
    {
        $this->moduleSlug = $moduleSlug;

        return $this;
    }

    public function getModuleUrl(): ?string
    {
        return $this->moduleUrl;
    }

    public function setModuleUrl(?string $moduleUrl): static
    {
        $this->moduleUrl = $moduleUrl;

        return $this;
    }

    public function getBrandName(): string
    {
        return $this->brandName;
    }

    public function setBrandName(string $brandName): static
    {
        $this->brandName = $brandName;

        return $this;
    }

    public function getBrandSlug(): ?string
    {
        return $this->brandSlug;
    }

    public function setBrandSlug(?string $brandSlug): static
    {
        $this->brandSlug = $brandSlug;

        return $this;
    }

    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(string $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

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

    public function getNotesDe(): ?string
    {
        return $this->notesDe;
    }

    public function setNotesDe(?string $notesDe): static
    {
        $this->notesDe = $notesDe;

        return $this;
    }

    public function getRaw(): ?string
    {
        return $this->raw;
    }

    public function setRaw(?string $raw): static
    {
        $this->raw = $raw;

        return $this;
    }

    public function getSourceFile(): ?string
    {
        return $this->sourceFile;
    }

    public function setSourceFile(?string $sourceFile): static
    {
        $this->sourceFile = $sourceFile;

        return $this;
    }

    public function getScrapedAt(): ?\DateTimeImmutable
    {
        return $this->scrapedAt;
    }

    public function setScrapedAt(?\DateTimeImmutable $scrapedAt): static
    {
        $this->scrapedAt = $scrapedAt;

        return $this;
    }

    public function getSignatureHash(): string
    {
        return $this->signatureHash;
    }

    public function setSignatureHash(string $signatureHash): static
    {
        $this->signatureHash = $signatureHash;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
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

    public function getNotesPreview(): string
    {
        $text = trim((string) ($this->notesDe ?? $this->notes ?? ''));
        if ('' === $text) {
            return '-';
        }

        if (mb_strlen($text) <= 120) {
            return $text;
        }

        return mb_substr($text, 0, 117) . '...';
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
}
