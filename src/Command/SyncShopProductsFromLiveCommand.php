<?php

namespace App\Command;

use App\Entity\ShopCategory;
use App\Entity\ShopManufacturer;
use App\Entity\ShopProduct;
use App\Repository\ShopCategoryRepository;
use App\Repository\ShopManufacturerRepository;
use App\Repository\ShopProductRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:shop:sync-live',
    description: 'Sync product core data (title, sku, price, stock) from the live shop and hide products no longer listed.',
)]
class SyncShopProductsFromLiveCommand extends Command
{
    private const BASE_URL = 'https://www.tuningfiles-download.com';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ShopProductRepository $productRepository,
        private readonly ShopCategoryRepository $categoryRepository,
        private readonly ShopManufacturerRepository $manufacturerRepository,
        private readonly HttpClientInterface $httpClient,
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only report what would change');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');

        $liveSlugs = $this->collectLiveSlugs();
        $output->writeln(sprintf('Live-Produkte gefunden: %d', count($liveSlugs)));

        $live = [];
        foreach ($liveSlugs as $slug) {
            $page = $this->fetchPage(self::BASE_URL . '/tuning-tool/' . $slug);
            if (null === $page) {
                $output->writeln(sprintf('<comment>%s: Seite nicht lesbar, übersprungen</comment>', $slug));
                continue;
            }
            $live[$slug] = $page;
        }

        if (count($live) < 20) {
            $output->writeln('<error>Zu wenige Live-Produkte gelesen, Abbruch ohne Änderungen.</error>');

            return Command::FAILURE;
        }

        $now = time();
        $updated = 0;
        $created = 0;
        $hidden = 0;

        foreach ($this->productRepository->findBy(['deleted' => false, 'sysLanguageUid' => 0]) as $product) {
            $slug = (string) $product->getSlug();
            if (!isset($live[$slug])) {
                if (!$product->isHidden()) {
                    $output->writeln(sprintf('ausblenden: uid %d (%s)', $product->getUid(), $slug));
                    if (!$dryRun) {
                        $product->setHidden(true)->setTstamp($now);
                    }
                    ++$hidden;
                }
                continue;
            }

            $data = $live[$slug];
            $changes = [];
            if ($product->getTitle() !== $data['title']) {
                $changes[] = sprintf('Titel "%s" -> "%s"', $product->getTitle(), $data['title']);
            }
            if ((string) $product->getSku() !== (string) $data['sku']) {
                $changes[] = sprintf('Art.-Nr. "%s" -> "%s"', $product->getSku(), $data['sku']);
            }
            if ((string) $product->getPrice() !== $data['price']) {
                $changes[] = sprintf('Preis %s -> %s', $product->getPrice(), $data['price']);
            }
            if ($product->getStock() !== $data['stock']) {
                $changes[] = sprintf('Lager %d -> %d', $product->getStock(), $data['stock']);
            }
            if ($product->isHidden()) {
                $changes[] = 'wieder einblenden';
            }

            if ([] === $changes) {
                continue;
            }

            $output->writeln(sprintf('uid %d (%s): %s', $product->getUid(), $slug, implode('; ', $changes)));
            if (!$dryRun) {
                $product
                    ->setTitle($data['title'])
                    ->setSku($data['sku'])
                    ->setPrice($data['price'])
                    ->setStock($data['stock'])
                    ->setHidden(false)
                    ->setTstamp($now);
            }
            ++$updated;
        }

        $existingSlugs = array_map(static fn (ShopProduct $p): string => (string) $p->getSlug(), $this->productRepository->findBy(['deleted' => false]));
        foreach ($live as $slug => $data) {
            if (in_array($slug, $existingSlugs, true)) {
                continue;
            }

            $output->writeln(sprintf('neu: %s (%s, %s €)', $data['title'], $slug, $data['price']));
            if ($dryRun) {
                ++$created;
                continue;
            }

            $product = $this->createProduct($slug, $data, $now);
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->linkCategories($product, $data['categories']);
            ++$created;
        }

        if (!$dryRun) {
            $this->entityManager->flush();
        }

        $output->writeln(sprintf('<info>Fertig: %d aktualisiert, %d neu, %d ausgeblendet%s</info>', $updated, $created, $hidden, $dryRun ? ' (Dry-Run, nichts geschrieben)' : ''));

        return Command::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function collectLiveSlugs(): array
    {
        $start = $this->fetchText(self::BASE_URL . '/shop');
        if (null === $start) {
            return [];
        }

        $listingPaths = ['/shop'];
        if (preg_match_all('~href="(/shop/[^"#?]+)"~', $start, $links)) {
            $listingPaths = array_values(array_unique(array_merge($listingPaths, $links[1])));
        }

        $slugs = [];
        foreach ($listingPaths as $path) {
            $html = $path === '/shop' ? $start : $this->fetchText(self::BASE_URL . $path);
            if (null === $html) {
                continue;
            }
            if (preg_match_all('~href="/tuning-tool/([^"#?]+)"~', $html, $m)) {
                foreach ($m[1] as $slug) {
                    $slugs[$slug] = true;
                }
            }
        }

        return array_keys($slugs);
    }

    /**
     * @return array{title: string, sku: string, price: string, stock: int, categories: list<string>, manufacturer: string}|null
     */
    private function fetchPage(string $url): ?array
    {
        $html = $this->fetchText($url);
        if (null === $html || !preg_match('#<h1[^>]*>(.*?)</h1>#s', $html, $h1)) {
            return null;
        }

        $title = trim(html_entity_decode(strip_tags($h1[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $sku = preg_match('#Art\.-Nr\.:\s*([^<\s]+)#u', $html, $s) ? trim($s[1]) : '';
        $price = preg_match('#([\d.]+,\d{2})\s*€(?:\s*<[^>]*>)*\s*zzgl#', $html, $p)
            || preg_match('#([\d.]+,\d{2})\s*€#', $html, $p)
            ? str_replace(',', '.', str_replace('.', '', $p[1]))
            : '0.00';
        $stock = preg_match('#Auf Lager \((\d+) verf#', $html, $st) ? (int) $st[1] : 0;

        $categories = [];
        if (preg_match('#Kategorien:(.*?)(?:Beschreibung|Lieferumfang)#s', $html, $cat)) {
            foreach (preg_split('#\s*<[^>]+>\s*#', $cat[1]) ?: [] as $name) {
                $name = trim(html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ('' !== $name) {
                    $categories[] = $name;
                }
            }
        }

        return ['title' => $title, 'sku' => $sku, 'price' => $price, 'stock' => $stock, 'categories' => $categories, 'manufacturer' => $title];
    }

    /**
     * @param array{title: string, sku: string, price: string, stock: int, categories: list<string>, manufacturer: string} $data
     */
    private function createProduct(string $slug, array $data, int $now): ShopProduct
    {
        $product = new ShopProduct();
        $product
            ->setUid($this->nextUid())
            ->setPid(0)
            ->setTstamp($now)
            ->setCrdate($now)
            ->setDeleted(false)
            ->setHidden(false)
            ->setSysLanguageUid(0)
            ->setSlug($slug)
            ->setTitle($data['title'])
            ->setSku($data['sku'])
            ->setPrice($data['price'])
            ->setStock($data['stock'])
            ->setManufacturer($this->matchManufacturer($data['title']));

        return $product;
    }

    /**
     * @param list<string> $categoryNames
     */
    private function linkCategories(ShopProduct $product, array $categoryNames): void
    {
        foreach ($categoryNames as $name) {
            $category = $this->matchCategory($name);
            if (!$category instanceof ShopCategory) {
                continue;
            }

            $this->connection->insert('tx_tuningtoolshop_product_category_mm', [
                'uid_local' => $product->getUid(),
                'uid_foreign' => $category->getUid(),
            ]);
        }
    }

    private function matchCategory(string $name): ?ShopCategory
    {
        $needle = $this->normalize($name);
        foreach ($this->categoryRepository->findAll() as $category) {
            if ($this->normalize((string) $category->getTitle()) === $needle) {
                return $category;
            }
        }

        return null;
    }

    private function matchManufacturer(string $title): ?ShopManufacturer
    {
        $haystack = $this->normalize($title, true);
        foreach ($this->manufacturerRepository->findAll() as $manufacturer) {
            $needle = $this->normalize((string) $manufacturer->getTitle(), true);
            if ('' !== $needle && str_contains($haystack, $needle)) {
                return $manufacturer;
            }
        }

        return null;
    }

    private function nextUid(): int
    {
        return (int) $this->connection->fetchOne('SELECT COALESCE(MAX(uid), 0) + 1 FROM tx_tuningtoolshop_domain_model_product');
    }

    private function normalize(string $value, bool $dropSpaces = false): string
    {
        $value = mb_strtolower(trim($value));

        return $dropSpaces ? (string) preg_replace('/[^a-z0-9]/u', '', $value) : (string) preg_replace('/\s+/u', ' ', $value);
    }

    private function fetchText(string $url): ?string
    {
        try {
            $response = $this->httpClient->request('GET', $url, ['timeout' => 20]);

            return 200 === $response->getStatusCode() ? $response->getContent(false) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
