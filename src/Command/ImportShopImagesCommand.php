<?php

namespace App\Command;

use App\Entity\MediaAsset;
use App\Entity\ShopProduct;
use App\Repository\ShopProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:shop:import-images',
    description: 'Download product gallery images from the legacy shop product pages.',
)]
class ImportShopImagesCommand extends Command
{
    private const BASE_URL = 'https://www.tuningfiles-download.com';
    private const PRODUCT_PATH = '/tuning-tool/';
    private const UPLOAD_DIR = 'uploads/media/scraped';
    private const MAX_IMAGES = 10;
    private const MAX_IMAGE_BYTES = 8 * 1024 * 1024;
    private const MIN_IMAGE_WIDTH = 100;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ShopProductRepository $productRepository,
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only list what would be imported')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Also import for products that already have media')
            ->addOption('product', null, InputOption::VALUE_REQUIRED, 'Only this product uid');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');
        $force = (bool) $input->getOption('force');
        $onlyUid = $input->getOption('product');

        $products = $this->productRepository->findBy(['deleted' => false, 'sysLanguageUid' => 0], ['uid' => 'ASC']);

        $stats = ['imported' => 0, 'images' => 0, 'skipped_media' => 0, 'not_on_site' => 0, 'no_images' => 0, 'failed' => 0];

        foreach ($products as $product) {
            if (null !== $onlyUid && (string) $product->getUid() !== (string) $onlyUid) {
                continue;
            }

            $slug = (string) $product->getSlug();
            if ('' === $slug) {
                ++$stats['not_on_site'];
                continue;
            }

            if (!$force && $product->getMediaAssets()->count() > 0) {
                ++$stats['skipped_media'];
                continue;
            }

            $pageUrl = self::BASE_URL . self::PRODUCT_PATH . rawurlencode($slug);
            $html = $this->fetchText($pageUrl);
            if (null === $html) {
                $output->writeln(sprintf('<comment>uid %d (%s): Seite nicht erreichbar</comment>', $product->getUid(), $slug));
                ++$stats['not_on_site'];
                continue;
            }

            $imageUrls = $this->extractGalleryImages($html, $product);
            if ([] === $imageUrls) {
                $output->writeln(sprintf('<comment>uid %d (%s): keine Galerie-Bilder gefunden</comment>', $product->getUid(), $slug));
                ++$stats['no_images'];
                continue;
            }

            if ($dryRun) {
                $output->writeln(sprintf('uid %d (%s): %d Bild(er)', $product->getUid(), $slug, count($imageUrls)));
                $stats['images'] += count($imageUrls);
                ++$stats['imported'];
                continue;
            }

            $created = 0;
            foreach ($imageUrls as $index => $imageUrl) {
                $asset = $this->downloadAsset($imageUrl, $product, $index + 1);
                if (null === $asset) {
                    ++$stats['failed'];
                    continue;
                }

                $product->addMediaAsset($asset);
                ++$created;
            }

            if (0 === $created) {
                continue;
            }

            $this->entityManager->flush();
            $output->writeln(sprintf('uid %d (%s): %d Bild(er) importiert', $product->getUid(), $slug, $created));
            $stats['images'] += $created;
            ++$stats['imported'];
        }

        $output->writeln(sprintf(
            '<info>Fertig: %d Produkte, %d Bilder. Übersprungen (hat schon Medien): %d, ohne Seite: %d, ohne Bilder: %d, Fehler: %d</info>',
            $stats['imported'],
            $stats['images'],
            $stats['skipped_media'],
            $stats['not_on_site'],
            $stats['no_images'],
            $stats['failed'],
        ));

        return Command::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function extractGalleryImages(string $html, ShopProduct $product): array
    {
        $productName = $this->normalize((string) $product->getTitle());
        $names = [$productName];
        if (preg_match('#<h1[^>]*>(.*?)</h1>#si', $html, $h1)) {
            $names[] = $this->normalize(strip_tags($h1[1]));
        }

        $urls = [];
        if (!preg_match_all('#<img[^>]*>#i', $html, $tags)) {
            return [];
        }

        foreach ($tags[0] as $tag) {
            if (!preg_match('#\ssrc="([^"]+)"#i', $tag, $src) || !str_contains($src[1], '/_processed_/')) {
                continue;
            }

            $width = preg_match('#\swidth="(\d+)"#i', $tag, $w) ? (int) $w[1] : 0;
            if ($width < self::MIN_IMAGE_WIDTH) {
                continue;
            }

            $alt = preg_match('#\salt="([^"]*)"#i', $tag, $a) ? $this->normalize(html_entity_decode($a[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
            if (!in_array($alt, $names, true)) {
                continue;
            }

            $url = self::BASE_URL . '/' . ltrim(html_entity_decode($src[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), '/');
            if (!in_array($url, $urls, true)) {
                $urls[] = $url;
            }
        }

        return array_slice($urls, 0, self::MAX_IMAGES);
    }

    private function downloadAsset(string $imageUrl, ShopProduct $product, int $position): ?MediaAsset
    {
        try {
            $response = $this->httpClient->request('GET', $imageUrl, ['timeout' => 20]);
            if (200 !== $response->getStatusCode()) {
                return null;
            }

            $contentType = $response->getHeaders(false)['content-type'][0] ?? '';
            if (!str_starts_with($contentType, 'image/')) {
                return null;
            }

            $content = $response->getContent(false);
        } catch (\Throwable) {
            return null;
        }

        if ('' === $content || strlen($content) > self::MAX_IMAGE_BYTES) {
            return null;
        }

        $absoluteDir = $this->projectDir . '/public/' . self::UPLOAD_DIR;
        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            return null;
        }

        $fileName = sprintf('shop-%d-%d-%s.%s', $product->getUid(), $position, bin2hex(random_bytes(4)), $this->extension($contentType, $imageUrl));
        if (false === @file_put_contents($absoluteDir . '/' . $fileName, $content)) {
            return null;
        }

        $asset = new MediaAsset();
        $asset->setName(sprintf('%s (%d)', $product->getTitle(), $position));
        $asset->setFilePath(self::UPLOAD_DIR . '/' . $fileName);
        $asset->setMimeType($contentType);
        $asset->setFileSize(strlen($content));
        $asset->setMediaType(MediaAsset::TYPE_IMAGE);
        $asset->setSorting($position * 10);
        $this->entityManager->persist($asset);

        return $asset;
    }

    private function fetchText(string $url): ?string
    {
        try {
            $response = $this->httpClient->request('GET', $url, ['timeout' => 20]);
            if (200 !== $response->getStatusCode()) {
                return null;
            }

            return $response->getContent(false);
        } catch (\Throwable) {
            return null;
        }
    }

    private function extension(string $contentType, string $url): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
        ];

        if (isset($map[$contentType])) {
            return $map[$contentType];
        }

        $pathExt = strtolower((string) pathinfo((string) parse_url($url, \PHP_URL_PATH), \PATHINFO_EXTENSION));

        return in_array($pathExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true) ? $pathExt : 'jpg';
    }

    private function normalize(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', mb_strtolower(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));
    }
}
