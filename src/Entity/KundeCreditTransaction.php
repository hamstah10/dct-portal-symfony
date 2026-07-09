<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\KundeCreditTransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KundeCreditTransactionRepository::class)]
class KundeCreditTransaction
{
    public const TYPE_ADMIN_TOPUP = 'admin_topup';
    public const TYPE_CUSTOMER_TOPUP = 'customer_topup';
    public const TYPE_MANUAL_ADJUSTMENT = 'manual_adjustment';
    public const TYPE_USAGE = 'usage';
    public const TYPE_REVERSAL = 'reversal';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'creditTransactions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Kunde $kunde = null;

    #[ORM\Column]
    private int $amount = 0;

    #[ORM\Column(length: 40)]
    private string $transactionType = self::TYPE_ADMIN_TOPUP;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $paymentReference = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $priceNet = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $priceGross = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $taxRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $paymentFee = null;

    #[ORM\Column]
    private int $balanceBefore = 0;

    #[ORM\Column]
    private int $balanceAfter = 0;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'reversal_of_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?self $reversalOf = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        $amountLabel = $this->amount >= 0 ? '+' . $this->amount : (string) $this->amount;

        return sprintf('%s Credits (%s)', $amountLabel, $this->transactionType);
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

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    public function setTransactionType(string $transactionType): static
    {
        $normalized = trim(strtolower($transactionType));
        $this->transactionType = '' !== $normalized ? $normalized : self::TYPE_ADMIN_TOPUP;

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

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $normalized = trim((string) ($paymentMethod ?? ''));
        $normalized = '' !== $normalized ? strtolower($normalized) : '';
        $this->paymentMethod = '' !== $normalized ? $normalized : null;

        return $this;
    }

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(?string $paymentReference): static
    {
        $normalized = null !== $paymentReference ? trim($paymentReference) : null;
        $this->paymentReference = '' !== ($normalized ?? '') ? $normalized : null;

        return $this;
    }

    public function getPriceNet(): ?string
    {
        return $this->priceNet;
    }

    public function setPriceNet(?string $priceNet): static
    {
        $this->priceNet = null !== $priceNet && '' !== trim($priceNet) ? $priceNet : null;

        return $this;
    }

    public function getPriceGross(): ?string
    {
        return $this->priceGross;
    }

    public function setPriceGross(?string $priceGross): static
    {
        $this->priceGross = null !== $priceGross && '' !== trim($priceGross) ? $priceGross : null;

        return $this;
    }

    public function getTaxRate(): ?string
    {
        return $this->taxRate;
    }

    public function setTaxRate(?string $taxRate): static
    {
        $this->taxRate = null !== $taxRate && '' !== trim($taxRate) ? $taxRate : null;

        return $this;
    }

    public function getPaymentFee(): ?string
    {
        return $this->paymentFee;
    }

    public function setPaymentFee(?string $paymentFee): static
    {
        $this->paymentFee = null !== $paymentFee && '' !== trim($paymentFee) ? $paymentFee : null;

        return $this;
    }

    public function getBalanceBefore(): int
    {
        return $this->balanceBefore;
    }

    public function setBalanceBefore(int $balanceBefore): static
    {
        $this->balanceBefore = $balanceBefore;

        return $this;
    }

    public function getBalanceAfter(): int
    {
        return $this->balanceAfter;
    }

    public function setBalanceAfter(int $balanceAfter): static
    {
        $this->balanceAfter = $balanceAfter;

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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getReversalOf(): ?self
    {
        return $this->reversalOf;
    }

    public function setReversalOf(?self $reversalOf): static
    {
        $this->reversalOf = $reversalOf;

        return $this;
    }
}
