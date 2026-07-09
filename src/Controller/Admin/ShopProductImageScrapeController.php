<?php

namespace App\Controller\Admin;

use App\Entity\MediaAsset;
use App\Entity\ShopProduct;
use App\Repository\ShopProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\AI\Agent\Bridge\Firecrawl\Firecrawl;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ShopProductImageScrapeController extends AbstractController
{
    use HandlesImageUploadsTrait;

    private const MAX_IMAGES = 10;
    private const MAX_IMAGE_BYTES = 8 * 1024 * 1024;
    private const UPLOAD_DIR = 'uploads/media/scraped';

    public function __construct(
        private readonly Firecrawl $firecrawl,
        private readonly HttpClientInterface $httpClient,
        private readonly ShopProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[AdminRoute(path: '/ecommerce/produkte/{uid}/scrape-images', name: 'shop_product_scrape_images', options: ['requirements' => ['uid' => '\d+'], 'methods' => ['POST']])]
    public function scrapeImages(int $uid, Request $request): JsonResponse
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('shop_product_scrape_images_' . $uid, $csrfToken)) {
            return new JsonResponse(['success' => false, 'message' => 'Ungueltiges CSRF-Token.'], 400);
        }

        $product = $this->productRepository->find($uid);
        if (!$product instanceof ShopProduct) {
            return new JsonResponse(['success' => false, 'message' => 'Produkt nicht gefunden.'], 404);
        }

        $url = trim((string) $request->request->get('url', ''));
        if ('' === $url || false === filter_var($url, \FILTER_VALIDATE_URL)) {
            return new JsonResponse(['success' => false, 'message' => 'Bitte eine gueltige URL angeben.'], 422);
        }

        try {
            $scraped = $this->firecrawl->scrape($url);
        } catch (\Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => 'Die Seite konnte nicht abgerufen werden: ' . $e->getMessage()], 502);
        }

        $html = (string) ($scraped['html'] ?? '');
        if ('' === trim($html)) {
            return new JsonResponse(['success' => false, 'message' => 'Keine Inhalte von der Seite erhalten.'], 502);
        }

        $imageUrls = $this->extractImageUrls($html, $url);
        if ([] === $imageUrls) {
            return new JsonResponse(['success' => false, 'message' => 'Keine Bilder auf der Seite gefunden.'], 422);
        }

        $created = [];
        $failedCount = 0;
        foreach (array_slice($imageUrls, 0, self::MAX_IMAGES) as $imageUrl) {
            $asset = $this->downloadAndCreateAsset($imageUrl, $product);
            if (null !== $asset) {
                $product->addMediaAsset($asset);
                $created[] = [
                    'id' => $asset->getId(),
                    'url' => '/' . ltrim((string) $asset->getFilePath(), '/'),
                    'name' => $asset->getName(),
                ];
            } else {
                ++$failedCount;
            }
        }

        if ([] === $created) {
            return new JsonResponse(['success' => false, 'message' => 'Keines der gefundenen Bilder konnte heruntergeladen werden.'], 502);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'created' => $created,
            'skipped' => $failedCount,
            'message' => sprintf('%d Bild(er) importiert.', count($created)),
        ]);
    }

    /**
     * @return list<string>
     */
    private function extractImageUrls(string $html, string $baseUrl): array
    {
        $urls = [];

        if (preg_match_all('/<meta[^>]+property=["\']og:image(?::secure_url)?["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $candidate) {
                $urls[] = $candidate;
            }
        }

        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $candidate) {
                $urls[] = $candidate;
            }
        }

        $resolved = [];
        foreach ($urls as $candidate) {
            $absolute = $this->resolveUrl($candidate, $baseUrl);
            if (null !== $absolute && !in_array($absolute, $resolved, true)) {
                $resolved[] = $absolute;
            }
        }

        return $resolved;
    }

    private function resolveUrl(string $candidate, string $baseUrl): ?string
    {
        $candidate = trim($candidate);
        if ('' === $candidate || str_starts_with($candidate, 'data:')) {
            return null;
        }

        if (preg_match('#^https?://#i', $candidate)) {
            return $candidate;
        }

        $base = parse_url($baseUrl);
        if (!isset($base['scheme'], $base['host'])) {
            return null;
        }

        $origin = $base['scheme'] . '://' . $base['host'] . (isset($base['port']) ? ':' . $base['port'] : '');

        if (str_starts_with($candidate, '//')) {
            return $base['scheme'] . ':' . $candidate;
        }

        if (str_starts_with($candidate, '/')) {
            return $origin . $candidate;
        }

        $basePath = isset($base['path']) ? rtrim(dirname($base['path']), '/') : '';

        return $origin . $basePath . '/' . $candidate;
    }

    private function downloadAndCreateAsset(string $imageUrl, ShopProduct $product): ?MediaAsset
    {
        try {
            $response = $this->httpClient->request('GET', $imageUrl, ['timeout' => 15]);
            $headers = $response->getHeaders(false);
            $contentType = $headers['content-type'][0] ?? '';
            if (!str_starts_with($contentType, 'image/')) {
                return null;
            }

            $content = $response->getContent(false);
            if ('' === $content || strlen($content) > self::MAX_IMAGE_BYTES) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $extension = $this->guessExtension($contentType, $imageUrl);
        $absoluteDir = $this->projectDir . '/public/' . self::UPLOAD_DIR;
        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            return null;
        }

        $fileName = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), $extension);
        $absolutePath = $absoluteDir . '/' . $fileName;
        if (false === @file_put_contents($absolutePath, $content)) {
            return null;
        }

        $asset = new MediaAsset();
        $asset->setName($product->getTitle() . ' (gescraped)');
        $asset->setFilePath(self::UPLOAD_DIR . '/' . $fileName);
        $asset->setMimeType($contentType);
        $asset->setFileSize(strlen($content));
        $asset->setMediaType(MediaAsset::TYPE_IMAGE);
        $this->entityManager->persist($asset);

        return $asset;
    }

    private function guessExtension(string $contentType, string $url): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
            'image/svg+xml' => 'svg',
        ];

        if (isset($map[$contentType])) {
            return $map[$contentType];
        }

        $pathExt = strtolower((string) pathinfo((string) parse_url($url, \PHP_URL_PATH), \PATHINFO_EXTENSION));

        return in_array($pathExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'svg'], true) ? $pathExt : 'jpg';
    }
}
