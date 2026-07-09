<?php

namespace App\Entity;

use App\Repository\ShopProductOptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopProductOptionRepository::class)]
#[ORM\Table(name: 'tx_tuningtoolshop_domain_model_productoption')]
class ShopProductOption implements ManualUidEntityInterface
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

    #[ORM\Column]
    private int $sorting = 0;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 50)]
    private string $type = '';

    #[ORM\Column(name: 'is_required', type: Types::BOOLEAN)]
    private bool $isRequired = false;

    #[ORM\Column(name: '`values`')]
    private int $valuesCount = 0;

    #[ORM\ManyToOne(targetEntity: ShopProduct::class)]
    #[ORM\JoinColumn(name: 'product', referencedColumnName: 'uid', nullable: true)]
    private ?ShopProduct $product = null;

    /**
     * @var Collection<int, ShopProductOptionValue>
     */
    #[ORM\OneToMany(mappedBy: 'productOption', targetEntity: ShopProductOptionValue::class)]
    private Collection $optionValues;

    public function __construct()
    {
        $this->optionValues = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('Option #' . $this->uid);
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

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): static
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    public function getValuesCount(): int
    {
        return $this->valuesCount;
    }

    public function setValuesCount(int $valuesCount): static
    {
        $this->valuesCount = $valuesCount;

        return $this;
    }

    public function getProduct(): ?ShopProduct
    {
        return $this->product;
    }

    public function setProduct(?ShopProduct $product): static
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Collection<int, ShopProductOptionValue>
     */
    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    public function addOptionValue(ShopProductOptionValue $value): static
    {
        if (!$this->optionValues->contains($value)) {
            $this->optionValues->add($value);
            $value->setProductOption($this);
        }

        return $this;
    }

    public function removeOptionValue(ShopProductOptionValue $value): static
    {
        if ($this->optionValues->removeElement($value) && $value->getProductOption() === $this) {
            $value->setProductOption(null);
        }

        return $this;
    }
}
