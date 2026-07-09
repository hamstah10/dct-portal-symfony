<?php

namespace App\Entity;

use App\Repository\AiProjectResourceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AiProjectResourceRepository::class)]
#[ORM\Table(name: 'ai_project_resource')]
class AiProjectResource
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_INDEXED = 'indexed';
    public const STATUS_UNSUPPORTED = 'unsupported';
    public const STATUS_ERROR = 'error';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'resources')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?AiProject $project = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $mediaAsset = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(name: 'file_path', length: 512, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(name: 'media_type', length: 30)]
    private string $mediaType = MediaAsset::TYPE_FILE;

    #[ORM\Column(name: 'mime_type', length: 255, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(name: 'file_size', nullable: true)]
    private ?int $fileSize = null;

    #[ORM\Column(name: 'indexing_status', length: 20)]
    private string $indexingStatus = self::STATUS_PENDING;

    #[ORM\Column(name: 'extracted_text', type: Types::TEXT, nullable: true)]
    private ?string $extractedText = null;

    #[ORM\Column(name: 'error_message', type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(name: 'last_indexed_at', nullable: true)]
    private ?\DateTimeImmutable $lastIndexedAt = null;

    #[ORM\Column(name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('Projekt Ressource #' . ($this->id ?? 0));
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            'Ausstehend' => self::STATUS_PENDING,
            'Indexiert' => self::STATUS_INDEXED,
            'Nicht unterstuetzt' => self::STATUS_UNSUPPORTED,
            'Fehler' => self::STATUS_ERROR,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?AiProject
    {
        return $this->project;
    }

    public function setProject(?AiProject $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getMediaAsset(): ?MediaAsset
    {
        return $this->mediaAsset;
    }

    public function setMediaAsset(?MediaAsset $mediaAsset): static
    {
        $this->mediaAsset = $mediaAsset;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = trim($title);

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = null === $filePath ? null : trim($filePath);

        return $this;
    }

    public function getMediaType(): string
    {
        return MediaAsset::normalizeMediaType($this->mediaType);
    }

    public function setMediaType(string $mediaType): static
    {
        $this->mediaType = MediaAsset::normalizeMediaType($mediaType);

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = null === $mimeType ? null : trim($mimeType);

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getIndexingStatus(): string
    {
        return $this->indexingStatus;
    }

    public function setIndexingStatus(string $indexingStatus): static
    {
        $normalized = strtolower(trim($indexingStatus));
        $allowed = array_values(self::getStatusChoices());
        $this->indexingStatus = \in_array($normalized, $allowed, true) ? $normalized : self::STATUS_PENDING;

        return $this;
    }

    public function getExtractedText(): ?string
    {
        return $this->extractedText;
    }

    public function setExtractedText(?string $extractedText): static
    {
        $this->extractedText = $extractedText;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = null === $errorMessage ? null : trim($errorMessage);

        return $this;
    }

    public function getLastIndexedAt(): ?\DateTimeImmutable
    {
        return $this->lastIndexedAt;
    }

    public function setLastIndexedAt(?\DateTimeImmutable $lastIndexedAt): static
    {
        $this->lastIndexedAt = $lastIndexedAt;

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

    public function isSearchableDocument(): bool
    {
        return \in_array($this->getMediaType(), [MediaAsset::TYPE_DOCUMENT, MediaAsset::TYPE_FILE], true);
    }
}
