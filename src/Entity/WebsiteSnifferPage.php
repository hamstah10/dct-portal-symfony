<?php

namespace App\Entity;

use App\Repository\WebsiteSnifferPageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebsiteSnifferPageRepository::class)]
#[ORM\Table(name: 'website_sniffer_page')]
class WebsiteSnifferPage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?WebsiteSnifferRun $run = null;

    #[ORM\Column(length: 1024)]
    private string $relativePath = '';

    #[ORM\Column(length: 1024)]
    private string $filePath = '';

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $h1 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $htmlContent = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $parsedData = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $screenshotPath = null;

    #[ORM\Column(nullable: true)]
    private ?int $bodyTextLength = null;

    #[ORM\Column(nullable: true)]
    private ?int $fileSize = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return '' !== $this->relativePath ? $this->relativePath : 'Seite';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRun(): ?WebsiteSnifferRun
    {
        return $this->run;
    }

    public function setRun(?WebsiteSnifferRun $run): static
    {
        $this->run = $run;

        return $this;
    }

    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    public function setRelativePath(string $relativePath): static
    {
        $this->relativePath = trim($relativePath);

        return $this;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = trim($filePath);

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = null === $title ? null : trim($title);

        return $this;
    }

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function setH1(?string $h1): static
    {
        $this->h1 = null === $h1 ? null : trim($h1);

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): static
    {
        $this->metaDescription = null === $metaDescription ? null : trim($metaDescription);

        return $this;
    }

    public function getHtmlContent(): ?string
    {
        return $this->htmlContent;
    }

    public function setHtmlContent(?string $htmlContent): static
    {
        $this->htmlContent = $htmlContent;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getParsedData(): ?array
    {
        return $this->parsedData;
    }

    /**
     * @param array<string, mixed>|null $parsedData
     */
    public function setParsedData(?array $parsedData): static
    {
        $this->parsedData = $parsedData;

        return $this;
    }

    public function getScreenshotPath(): ?string
    {
        return $this->screenshotPath;
    }

    public function setScreenshotPath(?string $screenshotPath): static
    {
        $this->screenshotPath = null === $screenshotPath ? null : trim($screenshotPath);

        return $this;
    }

    public function getBodyTextLength(): ?int
    {
        return $this->bodyTextLength;
    }

    public function setBodyTextLength(?int $bodyTextLength): static
    {
        $this->bodyTextLength = null === $bodyTextLength ? null : max(0, $bodyTextLength);

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): static
    {
        $this->fileSize = null === $fileSize ? null : max(0, $fileSize);

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
