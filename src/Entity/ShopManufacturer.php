<?php

namespace App\Entity;

use App\Repository\ShopManufacturerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopManufacturerRepository::class)]
#[ORM\Table(name: 'tx_tuningtoolshop_domain_model_manufacturer')]
class ShopManufacturer implements ManualUidEntityInterface
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
    private int $starttime = 0;

    #[ORM\Column]
    private int $endtime = 0;

    #[ORM\Column]
    private int $sorting = 0;

    #[ORM\Column(name: 'sys_language_uid')]
    private int $sysLanguageUid = 0;

    #[ORM\Column(name: 'l10n_parent')]
    private int $l10nParent = 0;

    #[ORM\Column(name: 'l10n_state', length: 255, nullable: true)]
    private ?string $l10nState = null;

    #[ORM\Column(name: 'l10n_diffsource', type: Types::TEXT, nullable: true)]
    private ?string $l10nDiffsource = null;

    #[ORM\Column(length: 255)]
    private string $title = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\ManyToOne(targetEntity: MediaAsset::class)]
    #[ORM\JoinColumn(name: 'logo_media_asset_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaAsset $logoMediaAsset = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(name: 'manufacturer_page')]
    private int $manufacturerPage = 0;

    /**
     * @var Collection<int, ShopProduct>
     */
    #[ORM\OneToMany(mappedBy: 'manufacturer', targetEntity: ShopProduct::class)]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('Hersteller #' . $this->uid);
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

    public function getStarttime(): int
    {
        return $this->starttime;
    }

    public function setStarttime(int $starttime): static
    {
        $this->starttime = $starttime;

        return $this;
    }

    public function getEndtime(): int
    {
        return $this->endtime;
    }

    public function setEndtime(int $endtime): static
    {
        $this->endtime = $endtime;

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

    public function getSysLanguageUid(): int
    {
        return $this->sysLanguageUid;
    }

    public function setSysLanguageUid(int $sysLanguageUid): static
    {
        $this->sysLanguageUid = $sysLanguageUid;

        return $this;
    }

    public function getL10nParent(): int
    {
        return $this->l10nParent;
    }

    public function setL10nParent(int $l10nParent): static
    {
        $this->l10nParent = $l10nParent;

        return $this;
    }

    public function getL10nState(): ?string
    {
        return $this->l10nState;
    }

    public function setL10nState(?string $l10nState): static
    {
        $this->l10nState = $l10nState;

        return $this;
    }

    public function getL10nDiffsource(): ?string
    {
        return $this->l10nDiffsource;
    }

    public function setL10nDiffsource(?string $l10nDiffsource): static
    {
        $this->l10nDiffsource = $l10nDiffsource;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLogoMediaAsset(): ?MediaAsset
    {
        return $this->logoMediaAsset;
    }

    public function setLogoMediaAsset(?MediaAsset $logoMediaAsset): static
    {
        $this->logoMediaAsset = $logoMediaAsset;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

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

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getManufacturerPage(): int
    {
        return $this->manufacturerPage;
    }

    public function setManufacturerPage(int $manufacturerPage): static
    {
        $this->manufacturerPage = $manufacturerPage;

        return $this;
    }

    /**
     * @return Collection<int, ShopProduct>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(ShopProduct $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setManufacturer($this);
        }

        return $this;
    }

    public function removeProduct(ShopProduct $product): static
    {
        if ($this->products->removeElement($product) && $product->getManufacturer() === $this) {
            $product->setManufacturer(null);
        }

        return $this;
    }
}
