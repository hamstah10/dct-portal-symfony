<?php

namespace App\Command;

use App\Entity\ShopProduct;
use App\Entity\ShopProductDeliveryScope;
use App\Entity\ShopProductLink;
use App\Entity\ShopProductOption;
use App\Entity\ShopProductOptionValue;
use App\Entity\ShopVideo;
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
    name: 'app:shop:sync-details',
    description: 'Replace description, delivery scope, links, videos and options of live-listed products with the live shop data.',
)]
class SyncShopProductDetailsFromLiveCommand extends Command
{
    private const BASE_URL = 'https://www.tuningfiles-download.com';

    /** @var array<string, int> */
    private array $uidCounters = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ShopProductRepository $productRepository,
        private readonly HttpClientInterface $httpClient,
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only report what would change')
            ->addOption('product', null, InputOption::VALUE_REQUIRED, 'Only this product uid');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');
        $onlyUid = $input->getOption('product');

        $listing = $this->fetchText(self::BASE_URL . '/shop');
        if (null === $listing) {
            $output->writeln('<error>Live-Shop nicht erreichbar.</error>');

            return Command::FAILURE;
        }

        $slugs = $this->collectLiveSlugs($listing);
        if (count($slugs) < 20) {
            $output->writeln(sprintf('<error>Nur %d Live-Produkte gefunden, Abbruch.</error>', count($slugs)));

            return Command::FAILURE;
        }

        $products = [];
        foreach ($this->productRepository->findBy(['deleted' => false, 'sysLanguageUid' => 0]) as $product) {
            if (in_array((string) $product->getSlug(), $slugs, true)) {
                $products[(string) $product->getSlug()] = $product;
            }
        }

        foreach ($products as $slug => $product) {
            if (null !== $onlyUid && (string) $product->getUid() !== (string) $onlyUid) {
                continue;
            }

            $html = $this->fetchText(self::BASE_URL . '/tuning-tool/' . $slug);
            if (null === $html) {
                $output->writeln(sprintf('<comment>%s: Seite nicht lesbar, übersprungen</comment>', $slug));
                continue;
            }

            $details = $this->parseDetails($html);
            $output->writeln(sprintf(
                'uid %d (%s): Beschreibung %s, Lieferumfang %d, Links %d, Videos %d, Optionen %d (%d Werte)',
                $product->getUid(),
                $slug,
                null === $details['description'] ? 'fehlt' : strlen($details['description']) . ' Zeichen',
                count($details['scope']),
                count($details['links']),
                count($details['videos']),
                count($details['options']),
                array_sum(array_map(static fn (array $o): int => count($o['values']), $details['options'])),
            ));

            if ($dryRun) {
                continue;
            }

            $this->applyDetails($product, $details);
            $this->entityManager->flush();
        }

        $output->writeln(sprintf('<info>Fertig%s</info>', $dryRun ? ' (Dry-Run, nichts geschrieben)' : ''));

        return Command::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function collectLiveSlugs(string $startHtml): array
    {
        $listingPaths = ['/shop'];
        if (preg_match_all('~href="(/shop/[^"#?]+)"~', $startHtml, $links)) {
            $listingPaths = array_values(array_unique(array_merge($listingPaths, $links[1])));
        }

        $slugs = [];
        foreach ($listingPaths as $path) {
            $html = $path === '/shop' ? $startHtml : $this->fetchText(self::BASE_URL . $path);
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
     * @return array{description: ?string, scope: list<string>, links: list<array{title: string, url: string}>, videos: list<array{title: string, url: string}>, options: list<array{title: string, type: string, required: bool, values: list<array{title: string, price: string}>}>}
     */
    private function parseDetails(string $html): array
    {
        $description = $this->tabPaneInner($html, 'description');
        if (null !== $description) {
            $description = trim($description) ?: null;
        }

        $scope = [];
        $scopePane = $this->tabPaneInner($html, 'lieferumfang') ?? '';
        if (preg_match_all('~<li[^>]*>(.*?)</li>~s', $scopePane, $items)) {
            foreach ($items[1] as $item) {
                $text = $this->cleanText($item);
                if ('' !== $text) {
                    $scope[] = $text;
                }
            }
        }

        $links = [];
        $linksPane = $this->tabPaneInner($html, 'links') ?? '';
        if (preg_match_all('~<a[^>]*href="([^"]+)"[^>]*>(.*?)</a>~s', $linksPane, $anchors, PREG_SET_ORDER)) {
            foreach ($anchors as $a) {
                $links[] = ['title' => $this->cleanText($a[2]), 'url' => html_entity_decode($a[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')];
            }
        }

        $videos = [];
        $videosPane = $this->tabPaneInner($html, 'videos') ?? '';
        if (preg_match_all('~<h5[^>]*>(.*?)</h5>\s*<iframe[^>]*src="([^"]+)"~s', $videosPane, $vids, PREG_SET_ORDER)) {
            foreach ($vids as $v) {
                $videos[] = ['title' => $this->cleanText($v[1]), 'url' => html_entity_decode($v[2], ENT_QUOTES | ENT_HTML5, 'UTF-8')];
            }
        }

        return [
            'description' => $description,
            'scope' => $scope,
            'links' => $links,
            'videos' => $videos,
            'options' => $this->parseOptions($html),
        ];
    }

    /**
     * @return list<array{title: string, type: string, required: bool, values: list<array{title: string, price: string}>}>
     */
    private function parseOptions(string $html): array
    {
        $start = strpos($html, 'Produktoptionen');
        if (false === $start) {
            return [];
        }

        $end = strpos($html, '<!-- Related Products -->', $start);
        $section = substr($html, $start, false === $end ? null : $end - $start);

        $groups = preg_split('~<div class="option-group~', $section);
        array_shift($groups);

        $options = [];
        foreach ($groups as $group) {
            if (!preg_match('~<label class="form-label[^"]*"[^>]*>(.*?)</label>~s', $group, $label)) {
                continue;
            }

            $title = trim(str_replace('*', '', $this->cleanText($label[1])));
            if ('' === $title) {
                continue;
            }

            $values = [];
            if (preg_match_all('~<option([^>]*)>(.*?)</option>~s', $group, $opts, PREG_SET_ORDER)) {
                foreach ($opts as $o) {
                    if (!preg_match('~value="(\d+)"~', $o[1])) {
                        continue;
                    }
                    $price = preg_match('~data-price="([-\d.]+)"~', $o[1], $p) ? $p[1] : '0';
                    $text = $this->cleanText($o[2]);
                    if ('' !== $text) {
                        $values[] = ['title' => $this->stripPrice($text), 'price' => $this->decimal($price)];
                    }
                }
            }

            if (preg_match_all('~<input[^>]*id="([^"]+)"[^>]*>~', $group, $inputs)) {
                foreach ($inputs[0] as $tag) {
                    if (!preg_match('~id="([^"]+)"~', $tag, $id)) {
                        continue;
                    }
                    $price = preg_match('~data-price="([-\d.]+)"~', $tag, $p) ? $p[1] : '0';
                    if (!preg_match('~<label[^>]*for="' . preg_quote($id[1], '~') . '"[^>]*>(.*?)</label>~s', $group, $lbl)) {
                        continue;
                    }
                    $text = $this->cleanText(preg_replace('~<(small|button)[^>]*>.*?</\1>~s', '', $lbl[1]) ?? $lbl[1]);
                    if ('' !== $text) {
                        $values[] = ['title' => $text, 'price' => $this->decimal($price)];
                    }
                }
            }

            $type = str_contains($group, '<select') ? 'select' : (str_contains($group, 'type="radio"') ? 'radio' : 'checkbox');

            $options[] = [
                'title' => $title,
                'type' => $type,
                'required' => str_contains($label[1], 'text-danger'),
                'values' => $values,
            ];
        }

        return $options;
    }

    /**
     * @param array{description: ?string, scope: list<string>, links: list<array{title: string, url: string}>, videos: list<array{title: string, url: string}>, options: list<array{title: string, type: string, required: bool, values: list<array{title: string, price: string}>}>} $details
     */
    private function applyDetails(ShopProduct $product, array $details): void
    {
        $productUid = $product->getUid();

        if (null !== $details['description']) {
            $product->setDescription($details['description']);
        }

        $this->connection->executeStatement(
            'DELETE v FROM tx_tuningtoolshop_domain_model_productoptionvalue v JOIN tx_tuningtoolshop_domain_model_productoption o ON o.uid = v.product_option WHERE o.product = :uid',
            ['uid' => $productUid],
        );
        $this->connection->executeStatement('DELETE FROM tx_tuningtoolshop_domain_model_productoption WHERE product = :uid', ['uid' => $productUid]);

        $this->entityManager->createQuery('DELETE FROM App\Entity\ShopProductDeliveryScope s WHERE s.product = :p')->setParameter('p', $product)->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\ShopProductLink l WHERE l.product = :p')->setParameter('p', $product)->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\ShopVideo v WHERE v.product = :p')->setParameter('p', $product)->execute();

        $sorting = 0;
        foreach ($details['scope'] as $text) {
            $scope = (new ShopProductDeliveryScope())
                ->setUid($this->nextUid('tx_tuningtoolshop_domain_model_productdeliveryscope'))
                ->setPid(0)->setTstamp(time())->setCrdate(time())->setDeleted(false)->setHidden(false)
                ->setSorting($sorting += 10)
                ->setSysLanguageUid(0)->setL10nParent(0)->setL10nState(null)->setL10nDiffsource(null)
                ->setTitle($text)->setDescription(null)->setProduct($product);
            $this->entityManager->persist($scope);
        }
        $product->setDeliveryScopeCount(count($details['scope']));

        $sorting = 0;
        foreach ($details['links'] as $link) {
            $entity = (new ShopProductLink())
                ->setUid($this->nextUid('tx_tuningtoolshop_domain_model_productlink'))
                ->setPid(0)->setTstamp(time())->setCrdate(time())->setDeleted(false)->setHidden(false)
                ->setSorting($sorting += 10)
                ->setTitle($link['title'] ?: $link['url'])->setUrl($link['url'])->setProduct($product);
            $this->entityManager->persist($entity);
        }
        $product->setLinksCount(count($details['links']));

        $sorting = 0;
        foreach ($details['videos'] as $video) {
            $entity = (new ShopVideo())
                ->setUid($this->nextUid('tx_tuningtoolshop_domain_model_video'))
                ->setPid(0)->setTstamp(time())->setCrdate(time())->setDeleted(false)->setHidden(false)
                ->setSorting($sorting += 10)
                ->setSysLanguageUid(0)->setL10nParent(0)->setL10nState(null)->setL10nDiffsource(null)
                ->setTitle($video['title'] ?: 'Video')->setDescription(null)->setFile(null)->setVideoUrl($video['url'])->setProduct($product);
            $this->entityManager->persist($entity);
        }
        $product->setVideosCount(count($details['videos']));

        $sorting = 0;
        foreach ($details['options'] as $opt) {
            $option = (new ShopProductOption())
                ->setUid($this->nextUid('tx_tuningtoolshop_domain_model_productoption'))
                ->setPid(0)->setTstamp(time())->setCrdate(time())->setDeleted(false)->setHidden(false)
                ->setSorting($sorting += 10)
                ->setTitle($opt['title'])->setType($opt['type'])->setIsRequired($opt['required'])
                ->setValuesCount(count($opt['values']))->setProduct($product);
            $this->entityManager->persist($option);

            $valueSorting = 0;
            foreach ($opt['values'] as $value) {
                $entity = (new ShopProductOptionValue())
                    ->setUid($this->nextUid('tx_tuningtoolshop_domain_model_productoptionvalue'))
                    ->setPid(0)->setTstamp(time())->setCrdate(time())->setDeleted(false)->setHidden(false)
                    ->setSorting($valueSorting += 10)
                    ->setTitle($value['title'])->setPriceModifier($value['price'])->setSpecialPrice('0.00')
                    ->setDescription(null)->setImage(null)->setProductOption($option);
                $this->entityManager->persist($entity);
            }
        }
        $product->setOptionsCount(count($details['options']));
    }

    private function nextUid(string $table): int
    {
        if (!isset($this->uidCounters[$table])) {
            $this->uidCounters[$table] = (int) $this->connection->fetchOne(sprintf('SELECT COALESCE(MAX(uid), 0) + 1 FROM %s', $table));
        }

        return $this->uidCounters[$table]++;
    }

    private function tabPaneInner(string $html, string $id): ?string
    {
        $marker = 'id="' . $id . '" role="tabpanel"';
        $start = strpos($html, $marker);
        if (false === $start) {
            return null;
        }

        $from = $start + strlen($marker);
        $end = strlen($html);
        foreach (['class="tab-pane', '<!-- Related Products -->'] as $stop) {
            $pos = strpos($html, $stop, $from);
            if (false !== $pos && $pos < $end) {
                $end = $pos;
            }
        }

        $pane = substr($html, $from, $end - $from);
        $pane = preg_replace('~^\s*>\s*~', '', $pane) ?? $pane;
        $pane = preg_replace('~^\s*<div class="row[^"]*">\s*<div class="col[^"]*">~', '', $pane) ?? $pane;
        $pane = preg_replace('~\s*<div\s*$~', '', $pane) ?? $pane;
        $pane = preg_replace('~(\s*</div>){1,3}\s*$~', '', $pane) ?? $pane;

        return trim($pane);
    }

    private function cleanText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    private function stripPrice(string $text): string
    {
        return trim((string) preg_replace('~\s*\([+-]?[\d.,]+\s*€\)\s*$~u', '', $text));
    }

    private function decimal(string $raw): string
    {
        return number_format((float) $raw, 2, '.', '');
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
