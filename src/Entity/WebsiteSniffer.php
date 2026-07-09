<?php

namespace App\Entity;

use App\Repository\WebsiteSnifferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebsiteSnifferRepository::class)]
#[ORM\Table(name: 'website_sniffer')]
class WebsiteSniffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private string $name = '';

    #[ORM\Column(length: 1024)]
    private string $directoryPath = '';

    #[ORM\Column]
    private bool $includeSubdirectories = true;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastRunAt = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $lastRunStatus = null;

    #[ORM\Column(nullable: true)]
    private ?int $lastRunPagesCount = null;

    /**
     * @var Collection<int, WebsiteSnifferRun>
     */
    #[ORM\OneToMany(mappedBy: 'sniffer', targetEntity: WebsiteSnifferRun::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['runAt' => 'DESC'])]
    private Collection $runs;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->runs = new ArrayCollection();
    }

    public function __toString(): string
    {
        $name = trim($this->name);
        if ('' !== $name) {
            return $name;
        }

        return null !== $this->id ? 'Sniffer #' . $this->id : 'Sniffer';
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
        $this->name = trim($name);

        return $this;
    }

    public function getDirectoryPath(): string
    {
        return $this->directoryPath;
    }

    public function setDirectoryPath(string $directoryPath): static
    {
        $this->directoryPath = trim($directoryPath);

        return $this;
    }

    public function isIncludeSubdirectories(): bool
    {
        return $this->includeSubdirectories;
    }

    public function setIncludeSubdirectories(bool $includeSubdirectories): static
    {
        $this->includeSubdirectories = $includeSubdirectories;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = null === $description ? null : trim($description);

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

    public function getLastRunAt(): ?\DateTimeImmutable
    {
        return $this->lastRunAt;
    }

    public function setLastRunAt(?\DateTimeImmutable $lastRunAt): static
    {
        $this->lastRunAt = $lastRunAt;

        return $this;
    }

    public function getLastRunStatus(): ?string
    {
        return $this->lastRunStatus;
    }

    public function setLastRunStatus(?string $lastRunStatus): static
    {
        $this->lastRunStatus = null === $lastRunStatus ? null : trim($lastRunStatus);

        return $this;
    }

    public function getLastRunPagesCount(): ?int
    {
        return $this->lastRunPagesCount;
    }

    public function setLastRunPagesCount(?int $lastRunPagesCount): static
    {
        $this->lastRunPagesCount = null === $lastRunPagesCount ? null : max(0, $lastRunPagesCount);

        return $this;
    }

    /**
     * @return Collection<int, WebsiteSnifferRun>
     */
    public function getRuns(): Collection
    {
        return $this->runs;
    }

    public function addRun(WebsiteSnifferRun $run): static
    {
        if (!$this->runs->contains($run)) {
            $this->runs->add($run);
            $run->setSniffer($this);
        }

        return $this;
    }

    public function removeRun(WebsiteSnifferRun $run): static
    {
        if ($this->runs->removeElement($run)) {
            if ($run->getSniffer() === $this) {
                $run->setSniffer(null);
            }
        }

        return $this;
    }

    public function getRunCount(): int
    {
        return $this->runs->count();
    }
}
