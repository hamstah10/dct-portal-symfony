<?php

namespace App\Entity;

use App\Repository\ScraperRunRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScraperRunRepository::class)]
#[ORM\Table(name: 'scraper_run')]
class ScraperRun
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $spider = '';

    #[ORM\Column(length: 255)]
    private string $type = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $parameters = '';

    #[ORM\Column(name: 'exit_code', nullable: true)]
    private ?int $exitCode = null;

    #[ORM\Column(nullable: true)]
    private ?bool $success = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $stdout = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $stderr = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $jsonPath = null;

    #[ORM\Column(name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpider(): string
    {
        return $this->spider;
    }

    public function setSpider(string $spider): static
    {
        $this->spider = $spider;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getParameters(): string
    {
        return $this->parameters;
    }

    public function setParameters(string $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    public function setExitCode(?int $exitCode): static
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    public function isSuccess(): ?bool
    {
        return $this->success;
    }

    public function setSuccess(?bool $success): static
    {
        $this->success = $success;

        return $this;
    }

    public function getStdout(): ?string
    {
        return $this->stdout;
    }

    public function setStdout(?string $stdout): static
    {
        $this->stdout = $stdout;

        return $this;
    }

    public function getStderr(): ?string
    {
        return $this->stderr;
    }

    public function setStderr(?string $stderr): static
    {
        $this->stderr = $stderr;

        return $this;
    }

    public function getJsonPath(): ?string
    {
        return $this->jsonPath;
    }

    public function setJsonPath(?string $jsonPath): static
    {
        $this->jsonPath = $jsonPath;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
