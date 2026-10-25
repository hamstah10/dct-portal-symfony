<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BrandAssistantBatchJobItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BrandAssistantBatchJobItemRepository::class)]
#[ORM\Table(name: 'brand_assistant_batch_job_item')]
class BrandAssistantBatchJobItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private BrandAssistantBatchJob $job;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'vehicle_engine_id', referencedColumnName: 'uid', nullable: false, onDelete: 'CASCADE')]
    private VehicleEngine $vehicleEngine;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $result = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $processedAt = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $extractedData = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJob(): BrandAssistantBatchJob
    {
        return $this->job;
    }

    public function setJob(BrandAssistantBatchJob $job): static
    {
        $this->job = $job;
        return $this;
    }

    public function getVehicleEngine(): VehicleEngine
    {
        return $this->vehicleEngine;
    }

    public function setVehicleEngine(VehicleEngine $vehicleEngine): static
    {
        $this->vehicleEngine = $vehicleEngine;
        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): static
    {
        $this->error = $error;
        return $this;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?\DateTimeImmutable $processedAt): static
    {
        $this->processedAt = $processedAt;
        return $this;
    }

    public function getExtractedData(): ?array
    {
        return $this->extractedData;
    }

    public function setExtractedData(?array $extractedData): static
    {
        $this->extractedData = $extractedData;
        return $this;
    }
}
