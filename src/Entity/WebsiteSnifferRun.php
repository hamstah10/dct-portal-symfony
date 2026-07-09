<?php

namespace App\Entity;

use App\Repository\WebsiteSnifferRunRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebsiteSnifferRunRepository::class)]
#[ORM\Table(name: 'website_sniffer_run')]
class WebsiteSnifferRun
{
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'runs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?WebsiteSniffer $sniffer = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $runAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_RUNNING;

    #[ORM\Column(length: 1024)]
    private string $sourceDirectory = '';

    #[ORM\Column]
    private int $pagesCount = 0;

    #[ORM\Column]
    private int $processedPages = 0;

    #[ORM\Column]
    private int $totalPages = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    /**
     * @var Collection<int, WebsiteSnifferPage>
     */
    #[ORM\OneToMany(mappedBy: 'run', targetEntity: WebsiteSnifferPage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['relativePath' => 'ASC'])]
    private Collection $pages;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->runAt = $now;
        $this->startedAt = $now;
        $this->status = self::STATUS_RUNNING;
        $this->pages = new ArrayCollection();
    }

    public function __toString(): string
    {
        $date = $this->runAt?->format('d.m.Y H:i:s') ?? '-';

        return sprintf('%s (%s)', $date, strtoupper($this->status));
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            'Running' => self::STATUS_RUNNING,
            'Success' => self::STATUS_SUCCESS,
            'Failed' => self::STATUS_FAILED,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSniffer(): ?WebsiteSniffer
    {
        return $this->sniffer;
    }

    public function setSniffer(?WebsiteSniffer $sniffer): static
    {
        $this->sniffer = $sniffer;

        return $this;
    }

    public function getRunAt(): ?\DateTimeImmutable
    {
        return $this->runAt;
    }

    public function setRunAt(\DateTimeImmutable $runAt): static
    {
        $this->runAt = $runAt;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

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
        $this->status = in_array($normalized, $validValues, true) ? $normalized : self::STATUS_RUNNING;

        return $this;
    }

    public function getSourceDirectory(): string
    {
        return $this->sourceDirectory;
    }

    public function setSourceDirectory(string $sourceDirectory): static
    {
        $this->sourceDirectory = trim($sourceDirectory);

        return $this;
    }

    public function getPagesCount(): int
    {
        return $this->pagesCount;
    }

    public function setPagesCount(int $pagesCount): static
    {
        $this->pagesCount = max(0, $pagesCount);

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

    public function getProcessedPages(): int
    {
        return $this->processedPages;
    }

    public function setProcessedPages(int $processedPages): static
    {
        $this->processedPages = max(0, $processedPages);

        return $this;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function setTotalPages(int $totalPages): static
    {
        $this->totalPages = max(0, $totalPages);

        return $this;
    }

    public function getProgressPercent(): int
    {
        if ($this->totalPages <= 0) {
            return self::STATUS_SUCCESS === $this->status ? 100 : 0;
        }

        $value = (int) floor(($this->processedPages / $this->totalPages) * 100);

        return max(0, min(100, $value));
    }

    /**
     * @return Collection<int, WebsiteSnifferPage>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(WebsiteSnifferPage $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setRun($this);
        }

        return $this;
    }

    public function removePage(WebsiteSnifferPage $page): static
    {
        if ($this->pages->removeElement($page)) {
            if ($page->getRun() === $this) {
                $page->setRun(null);
            }
        }

        return $this;
    }

    public function getDurationInSeconds(): ?int
    {
        if (null === $this->startedAt || null === $this->finishedAt) {
            return null;
        }

        return max(0, $this->finishedAt->getTimestamp() - $this->startedAt->getTimestamp());
    }
}
