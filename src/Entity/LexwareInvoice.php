<?php

namespace App\Entity;

use App\Repository\LexwareInvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Kunde;

#[ORM\Entity(repositoryClass: LexwareInvoiceRepository::class)]
#[ORM\Table(name: 'lexware_invoice')]
class LexwareInvoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lexwareInvoices')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Kunde $kunde = null;

    #[ORM\Column(length: 64, unique: true)]
    private string $lexwareInvoiceId = '';

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $voucherNumber = null;

    #[ORM\Column(length: 20)]
    private string $status = 'draft';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKunde(): ?Kunde
    {
        return $this->kunde;
    }

    public function setKunde(?Kunde $kunde): static
    {
        $this->kunde = $kunde;

        return $this;
    }

    public function getLexwareInvoiceId(): string
    {
        return $this->lexwareInvoiceId;
    }

    public function setLexwareInvoiceId(string $lexwareInvoiceId): static
    {
        $this->lexwareInvoiceId = $lexwareInvoiceId;

        return $this;
    }

    public function getVoucherNumber(): ?string
    {
        return $this->voucherNumber;
    }

    public function setVoucherNumber(?string $voucherNumber): static
    {
        $this->voucherNumber = $voucherNumber;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
