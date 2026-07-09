<?php

namespace App\Entity;

use App\Repository\ShopProductLinkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopProductLinkRepository::class)]
#[ORM\Table(name: 'tx_tuningtoolshop_domain_model_productlink')]
class ShopProductLink implements ManualUidEntityInterface
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

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $url = null;

    #[ORM\ManyToOne(targetEntity: ShopProduct::class)]
    #[ORM\JoinColumn(name: 'product', referencedColumnName: 'uid', nullable: true)]
    private ?ShopProduct $product = null;

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('Produktlink #' . $this->uid);
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

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
}
