<?php

namespace App\Entity;

use App\Repository\ShopProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopProductRepository::class)]
#[ORM\Table(name: 'tx_tuningtoolshop_domain_model_product')]
class ShopProduct implements ManualUidEntityInterface
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
    private ?string $sku = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column(name: 'special_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $specialPrice = '0.00';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'short_description', type: Types::TEXT, nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $headline = null;

    #[ORM\Column(name: 'lieferumfang')]
    private int $deliveryScopeCount = 0;

    #[ORM\ManyToOne(targetEntity: ShopManufacturer::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'manufacturer', referencedColumnName: 'uid', nullable: true)]
    private ?ShopManufacturer $manufacturer = null;

    #[ORM\Column(name: 'categories')]
    private int $categoriesCount = 0;

    #[ORM\Column(name: 'images')]
    private int $imagesCount = 0;

    #[ORM\Column(name: 'videos')]
    private int $videosCount = 0;

    #[ORM\Column(name: 'documents')]
    private int $documentsCount = 0;

    #[ORM\Column(name: 'links')]
    private int $linksCount = 0;

    #[ORM\Column]
    private int $stock = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3, nullable: true)]
    private ?string $weight = null;

    #[ORM\Column(name: 'is_active', type: Types::BOOLEAN)]
    private bool $isActive = false;

    #[ORM\ManyToOne(targetEntity: ShopTax::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'tax', referencedColumnName: 'uid', nullable: true)]
    private ?ShopTax $tax = null;

    #[ORM\Column(name: 'shipping_methods')]
    private int $shippingMethodsCount = 0;

    #[ORM\Column(name: 'product_type', length: 50, nullable: true)]
    private ?string $productType = null;

    #[ORM\Column(name: 'operating_costs', type: Types::TEXT, nullable: true)]
    private ?string $operatingCosts = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $extensions = null;

    #[ORM\Column(name: 'startup_help_headline', length: 255, nullable: true)]
    private ?string $startupHelpHeadline = null;

    #[ORM\Column(name: 'startup_help_text', type: Types::TEXT, nullable: true)]
    private ?string $startupHelpText = null;

    #[ORM\Column(name: 'features_headline', length: 255, nullable: true)]
    private ?string $featuresHeadline = null;

    #[ORM\Column(name: 'features_text', type: Types::TEXT, nullable: true)]
    private ?string $featuresText = null;

    #[ORM\Column(name: 'recommendation_headline', length: 255, nullable: true)]
    private ?string $recommendationHeadline = null;

    #[ORM\Column(name: 'recommendation_text', type: Types::TEXT, nullable: true)]
    private ?string $recommendationText = null;

    #[ORM\Column(name: 'meta_title', length: 255, nullable: true)]
    private ?string $metaTitle = null;

    #[ORM\Column(name: 'meta_description', type: Types::TEXT, nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column(name: 'meta_keywords', type: Types::TEXT, nullable: true)]
    private ?string $metaKeywords = null;

    #[ORM\Column(name: 'canonical_url', length: 2048, nullable: true)]
    private ?string $canonicalUrl = null;

    #[ORM\Column(name: 'related_products')]
    private int $relatedProductsCount = 0;

    #[ORM\Column(name: 'tags')]
    private int $tagsCount = 0;

    #[ORM\Column(name: 'shipping_free', type: Types::BOOLEAN)]
    private bool $shippingFree = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $badge = null;

    #[ORM\Column(name: 'options')]
    private int $optionsCount = 0;

    #[ORM\Column(name: 'lexoffice_product_id', length: 255, nullable: true)]
    private ?string $lexofficeProductId = null;

    #[ORM\ManyToMany(targetEntity: ShopCategory::class)]
    #[ORM\JoinTable(name: 'tx_tuningtoolshop_product_category_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: ShopTag::class)]
    #[ORM\JoinTable(name: 'tx_tuningtoolshop_product_tag_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $tags;

    #[ORM\ManyToMany(targetEntity: self::class)]
    #[ORM\JoinTable(name: 'tx_tuningtoolshop_product_relatedproducts_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $relatedProducts;

    #[ORM\ManyToMany(targetEntity: ShopShippingMethod::class)]
    #[ORM\JoinTable(name: 'tx_tuningtoolshop_product_shippingmethod_mm')]
    #[ORM\JoinColumn(name: 'uid_local', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'uid_foreign', referencedColumnName: 'uid')]
    private Collection $shippingMethods;

    #[ORM\ManyToMany(targetEntity: MediaAsset::class)]
    #[ORM\JoinTable(name: 'shop_product_media')]
    #[ORM\JoinColumn(name: 'product_uid', referencedColumnName: 'uid', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'media_asset_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $mediaAssets;

    /**
     * @var array<int, mixed>
     */
    private array $uploadedImages = [];

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->relatedProducts = new ArrayCollection();
        $this->shippingMethods = new ArrayCollection();
        $this->mediaAssets = new ArrayCollection();
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : ('Produkt #' . $this->uid);
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

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): static
    {
        $this->sku = $sku;

        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getSpecialPrice(): string
    {
        return $this->specialPrice;
    }

    public function setSpecialPrice(string $specialPrice): static
    {
        $this->specialPrice = $specialPrice;

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

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function setHeadline(?string $headline): static
    {
        $this->headline = $headline;

        return $this;
    }

    public function getDeliveryScopeCount(): int
    {
        return $this->deliveryScopeCount;
    }

    public function setDeliveryScopeCount(int $deliveryScopeCount): static
    {
        $this->deliveryScopeCount = $deliveryScopeCount;

        return $this;
    }

    public function getManufacturer(): ?ShopManufacturer
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?ShopManufacturer $manufacturer): static
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getCategoriesCount(): int
    {
        return $this->categoriesCount;
    }

    public function setCategoriesCount(int $categoriesCount): static
    {
        $this->categoriesCount = $categoriesCount;

        return $this;
    }

    public function getImagesCount(): int
    {
        return $this->imagesCount;
    }

    public function setImagesCount(int $imagesCount): static
    {
        $this->imagesCount = $imagesCount;

        return $this;
    }

    public function getVideosCount(): int
    {
        return $this->videosCount;
    }

    public function setVideosCount(int $videosCount): static
    {
        $this->videosCount = $videosCount;

        return $this;
    }

    public function getDocumentsCount(): int
    {
        return $this->documentsCount;
    }

    public function setDocumentsCount(int $documentsCount): static
    {
        $this->documentsCount = $documentsCount;

        return $this;
    }

    public function getLinksCount(): int
    {
        return $this->linksCount;
    }

    public function setLinksCount(int $linksCount): static
    {
        $this->linksCount = $linksCount;

        return $this;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTax(): ?ShopTax
    {
        return $this->tax;
    }

    public function setTax(?ShopTax $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getShippingMethodsCount(): int
    {
        return $this->shippingMethodsCount;
    }

    public function setShippingMethodsCount(int $shippingMethodsCount): static
    {
        $this->shippingMethodsCount = $shippingMethodsCount;

        return $this;
    }

    public function getProductType(): ?string
    {
        return $this->productType;
    }

    public function setProductType(?string $productType): static
    {
        $this->productType = $productType;

        return $this;
    }

    public function getOperatingCosts(): ?string
    {
        return $this->operatingCosts;
    }

    public function setOperatingCosts(?string $operatingCosts): static
    {
        $this->operatingCosts = $operatingCosts;

        return $this;
    }

    public function getExtensions(): ?string
    {
        return $this->extensions;
    }

    public function setExtensions(?string $extensions): static
    {
        $this->extensions = $extensions;

        return $this;
    }

    public function getStartupHelpHeadline(): ?string
    {
        return $this->startupHelpHeadline;
    }

    public function setStartupHelpHeadline(?string $startupHelpHeadline): static
    {
        $this->startupHelpHeadline = $startupHelpHeadline;

        return $this;
    }

    public function getStartupHelpText(): ?string
    {
        return $this->startupHelpText;
    }

    public function setStartupHelpText(?string $startupHelpText): static
    {
        $this->startupHelpText = $startupHelpText;

        return $this;
    }

    public function getFeaturesHeadline(): ?string
    {
        return $this->featuresHeadline;
    }

    public function setFeaturesHeadline(?string $featuresHeadline): static
    {
        $this->featuresHeadline = $featuresHeadline;

        return $this;
    }

    public function getFeaturesText(): ?string
    {
        return $this->featuresText;
    }

    public function setFeaturesText(?string $featuresText): static
    {
        $this->featuresText = $featuresText;

        return $this;
    }

    public function getRecommendationHeadline(): ?string
    {
        return $this->recommendationHeadline;
    }

    public function setRecommendationHeadline(?string $recommendationHeadline): static
    {
        $this->recommendationHeadline = $recommendationHeadline;

        return $this;
    }

    public function getRecommendationText(): ?string
    {
        return $this->recommendationText;
    }

    public function setRecommendationText(?string $recommendationText): static
    {
        $this->recommendationText = $recommendationText;

        return $this;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): static
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): static
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): static
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(?string $canonicalUrl): static
    {
        $this->canonicalUrl = $canonicalUrl;

        return $this;
    }

    public function getRelatedProductsCount(): int
    {
        return $this->relatedProductsCount;
    }

    public function setRelatedProductsCount(int $relatedProductsCount): static
    {
        $this->relatedProductsCount = $relatedProductsCount;

        return $this;
    }

    public function getTagsCount(): int
    {
        return $this->tagsCount;
    }

    public function setTagsCount(int $tagsCount): static
    {
        $this->tagsCount = $tagsCount;

        return $this;
    }

    public function isShippingFree(): bool
    {
        return $this->shippingFree;
    }

    public function setShippingFree(bool $shippingFree): static
    {
        $this->shippingFree = $shippingFree;

        return $this;
    }

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function setBadge(?string $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    public function getOptionsCount(): int
    {
        return $this->optionsCount;
    }

    public function setOptionsCount(int $optionsCount): static
    {
        $this->optionsCount = $optionsCount;

        return $this;
    }

    public function getLexofficeProductId(): ?string
    {
        return $this->lexofficeProductId;
    }

    public function setLexofficeProductId(?string $lexofficeProductId): static
    {
        $this->lexofficeProductId = $lexofficeProductId;

        return $this;
    }

    /**
     * @return Collection<int, ShopCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(ShopCategory $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(ShopCategory $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return Collection<int, ShopTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(ShopTag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(ShopTag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, ShopProduct>
     */
    public function getRelatedProducts(): Collection
    {
        return $this->relatedProducts;
    }

    public function addRelatedProduct(self $product): static
    {
        if (!$this->relatedProducts->contains($product)) {
            $this->relatedProducts->add($product);
        }

        return $this;
    }

    public function removeRelatedProduct(self $product): static
    {
        $this->relatedProducts->removeElement($product);

        return $this;
    }

    /**
     * @return Collection<int, ShopShippingMethod>
     */
    public function getShippingMethods(): Collection
    {
        return $this->shippingMethods;
    }

    public function addShippingMethod(ShopShippingMethod $method): static
    {
        if (!$this->shippingMethods->contains($method)) {
            $this->shippingMethods->add($method);
        }

        return $this;
    }

    public function removeShippingMethod(ShopShippingMethod $method): static
    {
        $this->shippingMethods->removeElement($method);

        return $this;
    }

    /**
     * @return Collection<int, MediaAsset>
     */
    public function getMediaAssets(): Collection
    {
        return $this->mediaAssets;
    }

    public function addMediaAsset(MediaAsset $mediaAsset): static
    {
        if (!$this->mediaAssets->contains($mediaAsset)) {
            $this->mediaAssets->add($mediaAsset);
        }

        return $this;
    }

    public function removeMediaAsset(MediaAsset $mediaAsset): static
    {
        $this->mediaAssets->removeElement($mediaAsset);

        return $this;
    }

    /**
     * @return array<int, mixed>
     */
    public function getUploadedImages(): array
    {
        return $this->uploadedImages;
    }

    /**
     * @param array<int, mixed>|mixed $uploadedImages
     */
    public function setUploadedImages(mixed $uploadedImages): static
    {
        if (null === $uploadedImages) {
            $this->uploadedImages = [];

            return $this;
        }

        $this->uploadedImages = is_array($uploadedImages) ? array_values($uploadedImages) : [$uploadedImages];

        return $this;
    }
}
