<?php

namespace App\Entity;

use App\Repository\MediaAssetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaAssetRepository::class)]
#[ORM\Table(name: 'media_asset')]
class MediaAsset
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_ICON = 'icon';
    public const TYPE_VIDEO = 'video';
    public const TYPE_MODEL = 'model';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_FILE = 'file';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'file_path', length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\ManyToOne(inversedBy: 'assets')]
    #[ORM\JoinColumn(name: 'folder_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaFolder $folder = null;

    #[ORM\Column(name: 'media_type', length: 30)]
    private string $mediaType = self::TYPE_FILE;

    #[ORM\Column(name: 'mime_type', length: 255, nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(name: 'file_size', nullable: true)]
    private ?int $fileSize = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private int $sorting = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $annotations = null;

    #[ORM\Column(name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    private mixed $uploadedFile = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return '' !== $this->name ? $this->name : ('MediaAsset #' . ($this->id ?? 0));
    }

    /**
     * @return array<string, string>
     */
    public static function getMediaTypeChoices(): array
    {
        return [
            'Bild' => self::TYPE_IMAGE,
            'Icon' => self::TYPE_ICON,
            'Video' => self::TYPE_VIDEO,
            '3D Modell (GLB/GLTF)' => self::TYPE_MODEL,
            'Dokument' => self::TYPE_DOCUMENT,
            'Datei' => self::TYPE_FILE,
        ];
    }

    public static function normalizeMediaType(string $mediaType): string
    {
        $normalized = strtolower(trim($mediaType));

        return in_array($normalized, self::getMediaTypeChoices(), true)
            ? $normalized
            : self::TYPE_FILE;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getFolder(): ?MediaFolder
    {
        return $this->folder;
    }

    public function setFolder(?MediaFolder $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    public function getMediaType(): string
    {
        return self::normalizeMediaType($this->mediaType);
    }

    public function setMediaType(string $mediaType): static
    {
        $this->mediaType = self::normalizeMediaType($mediaType);

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;

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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAnnotations(): array
    {
        return is_array($this->annotations) ? $this->annotations : [];
    }

    /**
     * @param array<int, array<string, mixed>>|null $annotations
     */
    public function setAnnotations(?array $annotations): static
    {
        if (null === $annotations || [] === $annotations) {
            $this->annotations = null;

            return $this;
        }

        $valid = [];
        foreach ($annotations as $annotation) {
            if (!is_array($annotation) || !isset($annotation['type'])) {
                continue;
            }
            $valid[] = $annotation;
        }

        $this->annotations = [] !== $valid ? $valid : null;

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

    public function getUploadedFile(): mixed
    {
        return $this->uploadedFile;
    }

    public function setUploadedFile(mixed $uploadedFile): static
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }
}
