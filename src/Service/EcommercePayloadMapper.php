<?php

namespace App\Service;

use App\Entity\MediaAsset;
use App\Entity\ShopCategory;
use App\Entity\ShopDownload;
use App\Entity\ShopManufacturer;
use App\Entity\ShopProduct;
use App\Entity\ShopProductDeliveryScope;
use App\Entity\ShopProductLink;
use App\Entity\ShopProductOption;
use App\Entity\ShopProductOptionValue;
use App\Entity\ShopShippingMethod;
use App\Entity\ShopTag;
use App\Entity\ShopTax;
use App\Entity\ShopVideo;

final class EcommercePayloadMapper
{
    /**
     * @param list<ShopProductLink> $links
     * @param list<ShopProductOption> $options
     * @param list<ShopProductDeliveryScope> $deliveryScopes
     * @param list<ShopVideo> $videos
     * @param list<ShopDownload> $downloads
     * @return array<string, mixed>
     */
    public function mapProduct(ShopProduct $product, bool $detail = false, array $links = [], array $options = [], array $deliveryScopes = [], array $videos = [], array $downloads = []): array
    {
        $payload = [
            'uid' => $product->getUid(),
            'title' => $product->getTitle(),
            'slug' => $product->getSlug(),
            'sku' => $product->getSku(),
            'active' => $product->isActive(),
            'apiActive' => $product->isApiActive(),
            'hidden' => $product->isHidden(),
            'deleted' => $product->isDeleted(),
            'sorting' => $product->getSorting(),
            'tstamp' => $product->getTstamp(),
            'starttime' => $product->getStarttime(),
            'endtime' => $product->getEndtime(),
            'price' => $this->normalizeDecimal($product->getPrice()),
            'specialPrice' => $this->normalizeDecimal($product->getSpecialPrice()),
            'effectivePrice' => $this->resolveEffectivePrice($product),
            'currency' => 'EUR',
            'stock' => $product->getStock(),
            'badge' => $product->getBadge(),
            'productType' => $product->getProductType(),
            'shortDescription' => $product->getShortDescription(),
            'manufacturer' => $this->mapManufacturer($product->getManufacturer()),
            'categories' => $this->mapCategories($product),
            'images' => $this->mapMediaAssets($product),
        ];

        if (!$detail) {
            return $payload;
        }

        return [
            ...$payload,
            'description' => $product->getDescription(),
            'headline' => $product->getHeadline(),
            'featuresHeadline' => $product->getFeaturesHeadline(),
            'featuresText' => $product->getFeaturesText(),
            'recommendationHeadline' => $product->getRecommendationHeadline(),
            'recommendationText' => $product->getRecommendationText(),
            'startupHelpHeadline' => $product->getStartupHelpHeadline(),
            'startupHelpText' => $product->getStartupHelpText(),
            'operatingCosts' => $product->getOperatingCosts(),
            'extensions' => $product->getExtensions(),
            'weight' => $this->normalizeNullableDecimal($product->getWeight()),
            'shippingFree' => $product->isShippingFree(),
            'metaTitle' => $product->getMetaTitle(),
            'metaDescription' => $product->getMetaDescription(),
            'metaKeywords' => $product->getMetaKeywords(),
            'canonicalUrl' => $product->getCanonicalUrl(),
            'tax' => $this->mapTax($product->getTax()),
            'tags' => $this->mapTags($product),
            'relatedProducts' => $this->mapRelatedProducts($product),
            'shippingMethods' => $this->mapShippingMethods($product),
            'links' => array_map(fn (ShopProductLink $link): array => $this->mapProductLink($link), $links),
            'options' => array_map(fn (ShopProductOption $option): array => $this->mapOption($option), $options),
            'deliveryScope' => array_map(fn (ShopProductDeliveryScope $scope): array => $this->mapDeliveryScope($scope), $deliveryScopes),
            'videos' => array_map(fn (ShopVideo $video): array => $this->mapVideo($video), $videos),
            'downloads' => array_map(fn (ShopDownload $download): array => $this->mapDownload($download), $downloads),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDownload(ShopDownload $download): array
    {
        return [
            'uid' => $download->getUid(),
            'title' => $download->getTitle(),
            'description' => $download->getDescription(),
            'file' => $this->normalizePublicPath($download->getFile()),
            'sorting' => $download->getSorting(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapOption(ShopProductOption $option): array
    {
        $values = array_filter(
            $option->getOptionValues()->toArray(),
            static fn (ShopProductOptionValue $value): bool => !$value->isDeleted() && !$value->isHidden()
        );
        usort($values, static fn (ShopProductOptionValue $a, ShopProductOptionValue $b): int => $a->getSorting() <=> $b->getSorting());

        return [
            'uid' => $option->getUid(),
            'title' => $option->getTitle(),
            'type' => $option->getType(),
            'required' => $option->isRequired(),
            'sorting' => $option->getSorting(),
            'values' => array_values(array_map(fn (ShopProductOptionValue $value): array => [
                'uid' => $value->getUid(),
                'title' => $value->getTitle(),
                'priceModifier' => $this->normalizeDecimal($value->getPriceModifier()),
                'specialPrice' => $this->normalizeDecimal($value->getSpecialPrice()),
                'description' => $value->getDescription(),
                'image' => $this->normalizePublicPath($value->getImage()),
                'sorting' => $value->getSorting(),
            ], $values)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDeliveryScope(ShopProductDeliveryScope $scope): array
    {
        return [
            'uid' => $scope->getUid(),
            'title' => $scope->getTitle(),
            'description' => $scope->getDescription(),
            'sorting' => $scope->getSorting(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapVideo(ShopVideo $video): array
    {
        return [
            'uid' => $video->getUid(),
            'title' => $video->getTitle(),
            'description' => $video->getDescription(),
            'url' => $video->getVideoUrl(),
            'file' => $this->normalizePublicPath($video->getFile()),
            'sorting' => $video->getSorting(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mapCategory(ShopCategory $category): array
    {
        return [
            'uid' => $category->getUid(),
            'title' => $category->getTitle(),
            'slug' => $category->getSlug(),
            'description' => $category->getDescription(),
            'image' => $this->normalizePublicPath($category->getImage()),
            'parent' => $category->getParent() instanceof ShopCategory ? [
                'uid' => $category->getParent()->getUid(),
                'title' => $category->getParent()->getTitle(),
                'slug' => $category->getParent()->getSlug(),
            ] : null,
            'sorting' => $category->getSorting(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function mapManufacturer(?ShopManufacturer $manufacturer): ?array
    {
        if (!$manufacturer instanceof ShopManufacturer || !$this->isVisibleTimedRecord($manufacturer)) {
            return null;
        }

        return [
            'uid' => $manufacturer->getUid(),
            'title' => $manufacturer->getTitle(),
            'slug' => $manufacturer->getSlug(),
            'logo' => $this->normalizePublicPath($manufacturer->getLogoMediaAsset()?->getFilePath() ?? $manufacturer->getLogo()),
            'description' => $manufacturer->getDescription(),
            'website' => $manufacturer->getWebsite(),
            'sorting' => $manufacturer->getSorting(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapCategories(ShopProduct $product): array
    {
        $categories = [];
        foreach ($product->getCategories() as $category) {
            if ($category instanceof ShopCategory && $this->isVisibleTimedRecord($category)) {
                $categories[] = [
                    'uid' => $category->getUid(),
                    'title' => $category->getTitle(),
                    'slug' => $category->getSlug(),
                ];
            }
        }

        usort($categories, static fn (array $a, array $b): int => ($a['title'] ?? '') <=> ($b['title'] ?? ''));

        return $categories;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapMediaAssets(ShopProduct $product): array
    {
        $assets = [];
        foreach ($product->getMediaAssets() as $asset) {
            if (!$asset instanceof MediaAsset || !$asset->isActive()) {
                continue;
            }

            $url = $this->normalizePublicPath($asset->getFilePath());
            if (null === $url) {
                continue;
            }

            $assets[] = [
                'id' => $asset->getId(),
                'name' => $asset->getName(),
                'url' => $url,
                'type' => $asset->getMediaType(),
                'mimeType' => $asset->getMimeType(),
                'sorting' => $asset->getSorting(),
            ];
        }

        usort($assets, static fn (array $a, array $b): int => [(int) ($a['sorting'] ?? 0), (string) ($a['name'] ?? '')] <=> [(int) ($b['sorting'] ?? 0), (string) ($b['name'] ?? '')]);

        return $assets;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapTax(?ShopTax $tax): ?array
    {
        if (!$tax instanceof ShopTax || $tax->isDeleted() || $tax->isHidden()) {
            return null;
        }

        return [
            'uid' => $tax->getUid(),
            'title' => $tax->getTitle(),
            'rate' => $this->normalizeDecimal($tax->getRate()),
            'country' => $tax->getCountry(),
            'isDefault' => $tax->isDefault(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapTags(ShopProduct $product): array
    {
        $tags = [];
        foreach ($product->getTags() as $tag) {
            if (!$tag instanceof ShopTag || $tag->isDeleted() || $tag->isHidden() || $tag->getSysLanguageUid() !== 0) {
                continue;
            }

            $tags[] = [
                'uid' => $tag->getUid(),
                'title' => $tag->getTitle(),
                'slug' => $tag->getSlug(),
                'color' => $tag->getColor(),
            ];
        }

        usort($tags, static fn (array $a, array $b): int => ($a['title'] ?? '') <=> ($b['title'] ?? ''));

        return $tags;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapRelatedProducts(ShopProduct $product): array
    {
        $products = [];
        foreach ($product->getRelatedProducts() as $relatedProduct) {
            if (!$relatedProduct instanceof ShopProduct || !$this->isVisibleProduct($relatedProduct)) {
                continue;
            }

            $products[] = [
                'uid' => $relatedProduct->getUid(),
                'title' => $relatedProduct->getTitle(),
                'slug' => $relatedProduct->getSlug(),
                'sku' => $relatedProduct->getSku(),
                'price' => $this->normalizeDecimal($relatedProduct->getPrice()),
                'specialPrice' => $this->normalizeDecimal($relatedProduct->getSpecialPrice()),
                'effectivePrice' => $this->resolveEffectivePrice($relatedProduct),
            ];
        }

        usort($products, static fn (array $a, array $b): int => ($a['title'] ?? '') <=> ($b['title'] ?? ''));

        return $products;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapShippingMethods(ShopProduct $product): array
    {
        $methods = [];
        foreach ($product->getShippingMethods() as $method) {
            if (!$method instanceof ShopShippingMethod || $method->isDeleted() || $method->isHidden() || !$method->isActive()) {
                continue;
            }

            $methods[] = [
                'uid' => $method->getUid(),
                'title' => $method->getTitle(),
                'description' => $method->getDescription(),
                'provider' => $method->getProvider(),
                'logo' => $this->normalizePublicPath($method->getLogo()),
                'price' => $this->normalizeDecimal($method->getPrice()),
                'minWeight' => $this->normalizeNullableDecimal($method->getMinWeight()),
                'maxWeight' => $this->normalizeNullableDecimal($method->getMaxWeight()),
                'sorting' => $method->getSortOrder(),
            ];
        }

        usort($methods, static fn (array $a, array $b): int => [(int) ($a['sorting'] ?? 0), (string) ($a['title'] ?? '')] <=> [(int) ($b['sorting'] ?? 0), (string) ($b['title'] ?? '')]);

        return $methods;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapProductLink(ShopProductLink $link): array
    {
        return [
            'uid' => $link->getUid(),
            'title' => $link->getTitle(),
            'url' => $link->getUrl(),
            'sorting' => $link->getSorting(),
        ];
    }

    private function resolveEffectivePrice(ShopProduct $product): string
    {
        $specialPrice = $this->normalizeDecimal($product->getSpecialPrice());

        return (float) $specialPrice > 0.0 ? $specialPrice : $this->normalizeDecimal($product->getPrice());
    }

    private function normalizeDecimal(string $value): string
    {
        $normalized = trim($value);

        return '' === $normalized ? '0.00' : $normalized;
    }

    private function normalizeNullableDecimal(?string $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return '' === $normalized ? null : $normalized;
    }

    public function normalizePublicPath(?string $path): ?string
    {
        $trimmed = trim((string) ($path ?? ''));
        if ('' === $trimmed) {
            return null;
        }

        if (preg_match('#^https?://#i', $trimmed) === 1) {
            return $trimmed;
        }

        if (str_starts_with($trimmed, '//') || str_contains($trimmed, '://')) {
            return null;
        }

        return str_starts_with($trimmed, '/') ? $trimmed : '/' . ltrim($trimmed, '/');
    }

    private function isVisibleProduct(ShopProduct $product): bool
    {
        return !$product->isDeleted()
            && !$product->isHidden()
            && $product->isActive()
            && $product->isApiActive()
            && $product->getSysLanguageUid() === 0
            && $this->isInTimeWindow($product->getStarttime(), $product->getEndtime());
    }

    private function isVisibleTimedRecord(ShopCategory|ShopManufacturer $record): bool
    {
        return !$record->isDeleted()
            && !$record->isHidden()
            && $record->getSysLanguageUid() === 0
            && $this->isInTimeWindow($record->getStarttime(), $record->getEndtime());
    }

    private function isInTimeWindow(int $starttime, int $endtime): bool
    {
        $now = time();

        return (0 === $starttime || $starttime <= $now)
            && (0 === $endtime || $endtime >= $now);
    }
}
