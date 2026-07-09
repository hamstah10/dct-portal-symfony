<?php

namespace App\Entity;

use App\Repository\ShopOrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopOrderRepository::class)]
#[ORM\Table(name: 'tx_tuningtoolshop_domain_model_order')]
class ShopOrder implements ManualUidEntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private int $uid = 0;

    #[ORM\Column]
    private int $pid = 0;

    #[ORM\Column]
    private int $tstamp = 0;

    #[ORM\Column]
    private int $crdate = 0;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $deleted = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $hidden = false;

    #[ORM\Column(name: 'order_number', length: 255, nullable: true)]
    private ?string $orderNumber = null;

    #[ORM\Column]
    private int $status = 0;

    #[ORM\Column(name: 'payment_status')]
    private int $paymentStatus = 0;

    #[ORM\Column(name: 'export_status')]
    private int $exportStatus = 0;

    #[ORM\ManyToOne(targetEntity: ShopPaymentMethod::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'payment_method', referencedColumnName: 'uid', nullable: true)]
    private ?ShopPaymentMethod $paymentMethod = null;

    #[ORM\ManyToOne(targetEntity: ShopShippingMethod::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'shipping_method', referencedColumnName: 'uid', nullable: true)]
    private ?ShopShippingMethod $shippingMethod = null;

    #[ORM\Column(name: 'transaction_id', length: 255, nullable: true)]
    private ?string $transactionId = null;

    #[ORM\Column(name: 'frontend_user_id')]
    private int $frontendUserId = 0;

    #[ORM\Column(name: 'items_json', type: Types::TEXT, nullable: true)]
    private ?string $itemsJson = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $discount = '0.00';

    #[ORM\Column(name: 'shipping_cost', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $shippingCost = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $subtotal = '0.00';

    #[ORM\Column(name: 'tax_amount', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $taxAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $total = '0.00';

    #[ORM\Column(name: 'created_at')]
    private int $createdAt = 0;

    #[ORM\Column(name: 'customer_first_name', length: 255, nullable: true)]
    private ?string $customerFirstName = null;

    #[ORM\Column(name: 'customer_last_name', length: 255, nullable: true)]
    private ?string $customerLastName = null;

    #[ORM\Column(name: 'customer_name', length: 255, nullable: true)]
    private ?string $customerName = null;

    #[ORM\Column(name: 'customer_company', length: 255, nullable: true)]
    private ?string $customerCompany = null;

    #[ORM\Column(name: 'customer_email', length: 255, nullable: true)]
    private ?string $customerEmail = null;

    #[ORM\Column(name: 'billing_address', length: 255, nullable: true)]
    private ?string $billingAddress = null;

    #[ORM\Column(name: 'billing_street', length: 255, nullable: true)]
    private ?string $billingStreet = null;

    #[ORM\Column(name: 'billing_city', length: 255, nullable: true)]
    private ?string $billingCity = null;

    #[ORM\Column(name: 'billing_zip', length: 50, nullable: true)]
    private ?string $billingZip = null;

    #[ORM\Column(name: 'billing_country', length: 50, nullable: true)]
    private ?string $billingCountry = null;

    #[ORM\Column(name: 'shipping_address', length: 255, nullable: true)]
    private ?string $shippingAddress = null;

    #[ORM\Column(name: 'shipping_street', length: 255, nullable: true)]
    private ?string $shippingStreet = null;

    #[ORM\Column(name: 'shipping_city', length: 255, nullable: true)]
    private ?string $shippingCity = null;

    #[ORM\Column(name: 'shipping_zip', length: 50, nullable: true)]
    private ?string $shippingZip = null;

    #[ORM\Column(name: 'shipping_country', length: 50, nullable: true)]
    private ?string $shippingCountry = null;

    #[ORM\Column(name: 'shipping_first_name', length: 255, nullable: true)]
    private ?string $shippingFirstName = null;

    #[ORM\Column(name: 'shipping_last_name', length: 255, nullable: true)]
    private ?string $shippingLastName = null;

    #[ORM\Column(name: 'shipping_company', length: 255, nullable: true)]
    private ?string $shippingCompany = null;

    #[ORM\Column(name: 'shipping_same_as_billing', type: Types::BOOLEAN)]
    private bool $shippingSameAsBilling = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(name: 'lexoffice_invoice_id', length: 255, nullable: true)]
    private ?string $lexofficeInvoiceId = null;

    #[ORM\Column(name: 'lexoffice_invoice_pdf', length: 255, nullable: true)]
    private ?string $lexofficeInvoicePdf = null;

    public function __toString(): string
    {
        return $this->orderNumber ?? ('Bestellung #' . $this->uid);
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function setUid(int $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function setPid(int $pid): static
    {
        $this->pid = $pid;

        return $this;
    }

    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    public function setTstamp(int $tstamp): static
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    public function getCrdate(): int
    {
        return $this->crdate;
    }

    public function setCrdate(int $crdate): static
    {
        $this->crdate = $crdate;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPaymentStatus(): int
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(int $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getExportStatus(): int
    {
        return $this->exportStatus;
    }

    public function setExportStatus(int $exportStatus): static
    {
        $this->exportStatus = $exportStatus;

        return $this;
    }

    public function getPaymentMethod(): ?ShopPaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?ShopPaymentMethod $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getShippingMethod(): ?ShopShippingMethod
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(?ShopShippingMethod $shippingMethod): static
    {
        $this->shippingMethod = $shippingMethod;

        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): static
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getFrontendUserId(): int
    {
        return $this->frontendUserId;
    }

    public function setFrontendUserId(int $frontendUserId): static
    {
        $this->frontendUserId = $frontendUserId;

        return $this;
    }

    public function getItemsJson(): ?string
    {
        return $this->itemsJson;
    }

    public function setItemsJson(?string $itemsJson): static
    {
        $this->itemsJson = $itemsJson;

        return $this;
    }

    public function getDiscount(): string
    {
        return $this->discount;
    }

    public function setDiscount(string $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getShippingCost(): string
    {
        return $this->shippingCost;
    }

    public function setShippingCost(string $shippingCost): static
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    public function getSubtotal(): string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getTaxAmount(): string
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(string $taxAmount): static
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    public function getTotal(): string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCustomerFirstName(): ?string
    {
        return $this->customerFirstName;
    }

    public function setCustomerFirstName(?string $customerFirstName): static
    {
        $this->customerFirstName = $customerFirstName;

        return $this;
    }

    public function getCustomerLastName(): ?string
    {
        return $this->customerLastName;
    }

    public function setCustomerLastName(?string $customerLastName): static
    {
        $this->customerLastName = $customerLastName;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(?string $customerName): static
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerCompany(): ?string
    {
        return $this->customerCompany;
    }

    public function setCustomerCompany(?string $customerCompany): static
    {
        $this->customerCompany = $customerCompany;

        return $this;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(?string $customerEmail): static
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    public function getBillingAddress(): ?string
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?string $billingAddress): static
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getBillingStreet(): ?string
    {
        return $this->billingStreet;
    }

    public function setBillingStreet(?string $billingStreet): static
    {
        $this->billingStreet = $billingStreet;

        return $this;
    }

    public function getBillingCity(): ?string
    {
        return $this->billingCity;
    }

    public function setBillingCity(?string $billingCity): static
    {
        $this->billingCity = $billingCity;

        return $this;
    }

    public function getBillingZip(): ?string
    {
        return $this->billingZip;
    }

    public function setBillingZip(?string $billingZip): static
    {
        $this->billingZip = $billingZip;

        return $this;
    }

    public function getBillingCountry(): ?string
    {
        return $this->billingCountry;
    }

    public function setBillingCountry(?string $billingCountry): static
    {
        $this->billingCountry = $billingCountry;

        return $this;
    }

    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?string $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getShippingStreet(): ?string
    {
        return $this->shippingStreet;
    }

    public function setShippingStreet(?string $shippingStreet): static
    {
        $this->shippingStreet = $shippingStreet;

        return $this;
    }

    public function getShippingCity(): ?string
    {
        return $this->shippingCity;
    }

    public function setShippingCity(?string $shippingCity): static
    {
        $this->shippingCity = $shippingCity;

        return $this;
    }

    public function getShippingZip(): ?string
    {
        return $this->shippingZip;
    }

    public function setShippingZip(?string $shippingZip): static
    {
        $this->shippingZip = $shippingZip;

        return $this;
    }

    public function getShippingCountry(): ?string
    {
        return $this->shippingCountry;
    }

    public function setShippingCountry(?string $shippingCountry): static
    {
        $this->shippingCountry = $shippingCountry;

        return $this;
    }

    public function getShippingFirstName(): ?string
    {
        return $this->shippingFirstName;
    }

    public function setShippingFirstName(?string $shippingFirstName): static
    {
        $this->shippingFirstName = $shippingFirstName;

        return $this;
    }

    public function getShippingLastName(): ?string
    {
        return $this->shippingLastName;
    }

    public function setShippingLastName(?string $shippingLastName): static
    {
        $this->shippingLastName = $shippingLastName;

        return $this;
    }

    public function getShippingCompany(): ?string
    {
        return $this->shippingCompany;
    }

    public function setShippingCompany(?string $shippingCompany): static
    {
        $this->shippingCompany = $shippingCompany;

        return $this;
    }

    public function isShippingSameAsBilling(): bool
    {
        return $this->shippingSameAsBilling;
    }

    public function setShippingSameAsBilling(bool $shippingSameAsBilling): static
    {
        $this->shippingSameAsBilling = $shippingSameAsBilling;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

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

    public function getLexofficeInvoiceId(): ?string
    {
        return $this->lexofficeInvoiceId;
    }

    public function setLexofficeInvoiceId(?string $lexofficeInvoiceId): static
    {
        $this->lexofficeInvoiceId = $lexofficeInvoiceId;

        return $this;
    }

    public function getLexofficeInvoicePdf(): ?string
    {
        return $this->lexofficeInvoicePdf;
    }

    public function setLexofficeInvoicePdf(?string $lexofficeInvoicePdf): static
    {
        $this->lexofficeInvoicePdf = $lexofficeInvoicePdf;

        return $this;
    }
}
