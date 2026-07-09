<?php

namespace App\Command;

use App\Entity\ShopCategory;
use App\Entity\ShopDownload;
use App\Entity\ShopManufacturer;
use App\Entity\ShopOrder;
use App\Entity\ShopPaymentMethod;
use App\Entity\ShopProduct;
use App\Entity\ShopProductDeliveryScope;
use App\Entity\ShopProductLink;
use App\Entity\ShopProductOption;
use App\Entity\ShopProductOptionValue;
use App\Entity\ShopShippingMethod;
use App\Entity\ShopTag;
use App\Entity\ShopTax;
use App\Entity\ShopVideo;
use App\Repository\ShopCategoryRepository;
use App\Repository\ShopDownloadRepository;
use App\Repository\ShopManufacturerRepository;
use App\Repository\ShopOrderRepository;
use App\Repository\ShopPaymentMethodRepository;
use App\Repository\ShopProductDeliveryScopeRepository;
use App\Repository\ShopProductLinkRepository;
use App\Repository\ShopProductOptionRepository;
use App\Repository\ShopProductOptionValueRepository;
use App\Repository\ShopProductRepository;
use App\Repository\ShopShippingMethodRepository;
use App\Repository\ShopTagRepository;
use App\Repository\ShopTaxRepository;
use App\Repository\ShopVideoRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:shop:import-json',
    description: 'Import the tuning tool shop catalog from a JSON export.',
)]
class ImportShopCatalogCommand extends Command
{
    /**
     * @var array<int, ShopCategory>
     */
    private array $categoryCache = [];

    /**
     * @var array<int, ShopManufacturer>
     */
    private array $manufacturerCache = [];

    /**
     * @var array<int, ShopTax>
     */
    private array $taxCache = [];

    /**
     * @var array<int, ShopTag>
     */
    private array $tagCache = [];

    /**
     * @var array<int, ShopShippingMethod>
     */
    private array $shippingCache = [];

    /**
     * @var array<int, ShopPaymentMethod>
     */
    private array $paymentCache = [];

    /**
     * @var array<int, ShopProduct>
     */
    private array $productCache = [];

    /**
     * @var array<int, ShopProductOption>
     */
    private array $optionCache = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'Path to JSON file')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Validate and parse without persisting');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = (string) ($input->getArgument('file') ?? '');
        if ('' === $file) {
            $file = $this->projectDir . '/var/import/tts.json';
        }

        if (!is_file($file)) {
            $output->writeln(sprintf('<error>Datei nicht gefunden: %s</error>', $file));

            return Command::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($file), true);
        if (!is_array($payload)) {
            $output->writeln('<error>JSON konnte nicht gelesen werden.</error>');

            return Command::FAILURE;
        }

        $tables = $this->extractTables($payload);
        $dryRun = (bool) $input->getOption('dry-run');
        $connection = $this->entityManager->getConnection();

        if (!$dryRun) {
            $connection->beginTransaction();
        }

        try {
            $categoryRepo = $this->entityManager->getRepository(ShopCategory::class);
            $manufacturerRepo = $this->entityManager->getRepository(ShopManufacturer::class);
            $taxRepo = $this->entityManager->getRepository(ShopTax::class);
            $tagRepo = $this->entityManager->getRepository(ShopTag::class);
            $shippingRepo = $this->entityManager->getRepository(ShopShippingMethod::class);
            $paymentRepo = $this->entityManager->getRepository(ShopPaymentMethod::class);
            $productRepo = $this->entityManager->getRepository(ShopProduct::class);
            $optionRepo = $this->entityManager->getRepository(ShopProductOption::class);
            $optionValueRepo = $this->entityManager->getRepository(ShopProductOptionValue::class);
            $deliveryScopeRepo = $this->entityManager->getRepository(ShopProductDeliveryScope::class);
            $linkRepo = $this->entityManager->getRepository(ShopProductLink::class);
            $downloadRepo = $this->entityManager->getRepository(ShopDownload::class);
            $videoRepo = $this->entityManager->getRepository(ShopVideo::class);
            $orderRepo = $this->entityManager->getRepository(ShopOrder::class);

            $this->importCategories($categoryRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_category'));
            $this->importManufacturers($manufacturerRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_manufacturer'));
            $this->importTaxes($taxRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_tax'));
            $this->importTags($tagRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_tag'));
            $this->importShippingMethods($shippingRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_shippingmethod'));
            $this->importPaymentMethods($paymentRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_paymentmethod'));
            $this->importProducts($productRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_product'));
            $this->importProductOptions($optionRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_productoption'));
            $this->importProductOptionValues($optionValueRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_productoptionvalue'));
            $this->importDeliveryScopes($deliveryScopeRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_productdeliveryscope'));
            $this->importProductLinks($linkRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_productlink'));
            $this->importDownloads($downloadRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_download'));
            $this->importVideos($videoRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_video'));
            $this->importOrders($orderRepo, $this->getRows($tables, 'tx_tuningtoolshop_domain_model_order'));

            if (!$dryRun) {
                $this->entityManager->flush();

                $this->importJoinTable($connection, 'tx_tuningtoolshop_product_category_mm', $this->getRows($tables, 'tx_tuningtoolshop_product_category_mm'));
                $this->importJoinTable($connection, 'tx_tuningtoolshop_product_tag_mm', $this->getRows($tables, 'tx_tuningtoolshop_product_tag_mm'));
                $this->importJoinTable($connection, 'tx_tuningtoolshop_product_relatedproducts_mm', $this->getRows($tables, 'tx_tuningtoolshop_product_relatedproducts_mm'));
                $this->importJoinTable($connection, 'tx_tuningtoolshop_product_shippingmethod_mm', $this->getRows($tables, 'tx_tuningtoolshop_product_shippingmethod_mm'));

                $connection->commit();
            } else {
                $this->entityManager->clear();
            }
        } catch (\Throwable $e) {
            if (!$dryRun && $connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $e;
        }

        $output->writeln('<info>Import abgeschlossen.</info>');

        return Command::SUCCESS;
    }

    /**
     * @param array<int, mixed> $payload
     * @return array<string, array<int, mixed>>
     */
    private function extractTables(array $payload): array
    {
        $tables = [];
        foreach ($payload as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            if (($entry['type'] ?? null) !== 'table') {
                continue;
            }

            $name = (string) ($entry['name'] ?? '');
            if ('' === $name) {
                continue;
            }

            $data = $entry['data'] ?? [];
            if (!is_array($data)) {
                $data = [];
            }

            $tables[$name] = $data;
        }

        return $tables;
    }

    /**
     * @param array<string, array<int, mixed>> $tables
     * @return array<int, mixed>
     */
    private function getRows(array $tables, string $name): array
    {
        $rows = $tables[$name] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        return $rows;
    }

    /**
     * @param ShopCategoryRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importCategories(ShopCategoryRepository $repository, array $rows): void
    {
        $parentMap = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $category = $repository->find($uid);
            if (!$category instanceof ShopCategory) {
                $category = new ShopCategory();
                $category->setUid($uid);
                $this->entityManager->persist($category);
            }

            $category
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setStarttime($this->toInt($row, 'starttime'))
                ->setEndtime($this->toInt($row, 'endtime'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setSysLanguageUid($this->toInt($row, 'sys_language_uid'))
                ->setL10nParent($this->toInt($row, 'l10n_parent'))
                ->setL10nState($this->toNullableString($row, 'l10n_state'))
                ->setL10nDiffsource($this->toNullableString($row, 'l10n_diffsource'))
                ->setTitle($this->toString($row, 'title'))
                ->setSlug($this->toNullableString($row, 'slug'))
                ->setDescription($this->toNullableString($row, 'description'))
                ->setImage($this->toNullableString($row, 'image'))
                ->setParent(null);

            $parentMap[$uid] = $this->toInt($row, 'parent');
            $this->categoryCache[$uid] = $category;
        }

        foreach ($parentMap as $uid => $parentUid) {
            if ($parentUid <= 0) {
                continue;
            }

            $category = $this->categoryCache[$uid] ?? null;
            $parent = $this->categoryCache[$parentUid] ?? null;
            if ($category instanceof ShopCategory && $parent instanceof ShopCategory) {
                $category->setParent($parent);
            }
        }
    }

    /**
     * @param ShopManufacturerRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importManufacturers(ShopManufacturerRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $manufacturer = $repository->find($uid);
            if (!$manufacturer instanceof ShopManufacturer) {
                $manufacturer = new ShopManufacturer();
                $manufacturer->setUid($uid);
                $this->entityManager->persist($manufacturer);
            }

            $manufacturer
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setStarttime($this->toInt($row, 'starttime'))
                ->setEndtime($this->toInt($row, 'endtime'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setSysLanguageUid($this->toInt($row, 'sys_language_uid'))
                ->setL10nParent($this->toInt($row, 'l10n_parent'))
                ->setL10nState($this->toNullableString($row, 'l10n_state'))
                ->setL10nDiffsource($this->toNullableString($row, 'l10n_diffsource'))
                ->setTitle($this->toString($row, 'title'))
                ->setSlug($this->toNullableString($row, 'slug'))
                ->setLogo($this->toNullableString($row, 'logo'))
                ->setDescription($this->toNullableString($row, 'description'))
                ->setWebsite($this->toNullableString($row, 'website'))
                ->setManufacturerPage($this->toInt($row, 'manufacturer_page'));

            $this->manufacturerCache[$uid] = $manufacturer;
        }
    }

    /**
     * @param ShopTaxRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importTaxes(ShopTaxRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $tax = $repository->find($uid);
            if (!$tax instanceof ShopTax) {
                $tax = new ShopTax();
                $tax->setUid($uid);
                $this->entityManager->persist($tax);
            }

            $tax
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setTitle($this->toString($row, 'title'))
                ->setRate($this->toDecimal($row, 'rate', '0.00'))
                ->setCountry($this->toNullableString($row, 'country'))
                ->setIsDefault($this->toBool($row, 'is_default'));

            $this->taxCache[$uid] = $tax;
        }
    }

    /**
     * @param ShopTagRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importTags(ShopTagRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $tag = $repository->find($uid);
            if (!$tag instanceof ShopTag) {
                $tag = new ShopTag();
                $tag->setUid($uid);
                $this->entityManager->persist($tag);
            }

            $tag
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setSysLanguageUid($this->toInt($row, 'sys_language_uid'))
                ->setL10nParent($this->toInt($row, 'l10n_parent'))
                ->setL10nState($this->toNullableString($row, 'l10n_state'))
                ->setL10nDiffsource($this->toNullableString($row, 'l10n_diffsource'))
                ->setTitle($this->toString($row, 'title'))
                ->setSlug($this->toNullableString($row, 'slug'))
                ->setDescription($this->toNullableString($row, 'description'))
                ->setColor($this->toNullableString($row, 'color'));

            $this->tagCache[$uid] = $tag;
        }
    }

    /**
     * @param ShopShippingMethodRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importShippingMethods(ShopShippingMethodRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $shipping = $repository->find($uid);
            if (!$shipping instanceof ShopShippingMethod) {
                $shipping = new ShopShippingMethod();
                $shipping->setUid($uid);
                $this->entityManager->persist($shipping);
            }

            $shipping
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setTitle($this->toString($row, 'title'))
                ->setDescription($this->toNullableString($row, 'description'))
                ->setProvider($this->toNullableString($row, 'provider'))
                ->setLogo($this->toNullableString($row, 'logo'))
                ->setPrice($this->toDecimal($row, 'price', '0.00'))
                ->setIsActive($this->toBool($row, 'is_active'))
                ->setLegacyIsActive($this->toBool($row, 'isActive'))
                ->setSortOrder($this->toInt($row, 'sort_order'))
                ->setLegacySortOrder($this->toInt($row, 'sortOrder'))
                ->setMinWeight($this->toNullableString($row, 'min_weight'))
                ->setMaxWeight($this->toNullableString($row, 'max_weight'));

            $this->shippingCache[$uid] = $shipping;
        }
    }

    /**
     * @param ShopPaymentMethodRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importPaymentMethods(ShopPaymentMethodRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $payment = $repository->find($uid);
            if (!$payment instanceof ShopPaymentMethod) {
                $payment = new ShopPaymentMethod();
                $payment->setUid($uid);
                $this->entityManager->persist($payment);
            }

            $payment
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setSortOrder($this->toInt($row, 'sort_order'))
                ->setTitle($this->toString($row, 'title'))
                ->setDescription($this->toNullableString($row, 'description'))
                ->setIcon($this->toNullableString($row, 'icon'))
                ->setIsActive($this->toBool($row, 'is_active'))
                ->setHandlerClass($this->toNullableString($row, 'handler_class'))
                ->setClientId($this->toNullableString($row, 'client_id'))
                ->setSecret($this->toNullableString($row, 'secret'))
                ->setApiUrl($this->toNullableString($row, 'api_url'))
                ->setIsSandbox($this->toBool($row, 'is_sandbox'));

            $this->paymentCache[$uid] = $payment;
        }
    }

    /**
     * @param ShopProductRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importProducts(ShopProductRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $product = $repository->find($uid);
            if (!$product instanceof ShopProduct) {
                $product = new ShopProduct();
                $product->setUid($uid);
                $this->entityManager->persist($product);
            }

            $manufacturer = $this->resolveManufacturer($this->toInt($row, 'manufacturer'));
            $tax = $this->resolveTax($this->toInt($row, 'tax'));

            $product
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setStarttime($this->toInt($row, 'starttime'))
                ->setEndtime($this->toInt($row, 'endtime'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setSysLanguageUid($this->toInt($row, 'sys_language_uid'))
                ->setL10nParent($this->toInt($row, 'l10n_parent'))
                ->setL10nState($this->toNullableString($row, 'l10n_state'))
                ->setL10nDiffsource($this->toNullableString($row, 'l10n_diffsource'))
                ->setTitle($this->toString($row, 'title'))
                ->setSlug($this->toNullableString($row, 'slug'))
                ->setSku($this->toNullableString($row, 'sku'))
                ->setPrice($this->toDecimal($row, 'price', '0.00'))
                ->setSpecialPrice($this->toDecimal($row, 'special_price', '0.00'))
                ->setDescription($this->toNullableString($row, 'description'))
                ->setShortDescription($this->toNullableString($row, 'short_description'))
                ->setHeadline($this->toNullableString($row, 'headline'))
                ->setDeliveryScopeCount($this->toInt($row, 'lieferumfang'))
                ->setManufacturer($manufacturer)
                ->setCategoriesCount($this->toInt($row, 'categories'))
                ->setImagesCount($this->toInt($row, 'images'))
                ->setVideosCount($this->toInt($row, 'videos'))
                ->setDocumentsCount($this->toInt($row, 'documents'))
                ->setLinksCount($this->toInt($row, 'links'))
                ->setStock($this->toInt($row, 'stock'))
                ->setWeight($this->toNullableString($row, 'weight'))
                ->setIsActive($this->toBool($row, 'is_active'))
                ->setTax($tax)
                ->setShippingMethodsCount($this->toInt($row, 'shipping_methods'))
                ->setProductType($this->toNullableString($row, 'product_type'))
                ->setOperatingCosts($this->toNullableString($row, 'operating_costs'))
                ->setExtensions($this->toNullableString($row, 'extensions'))
                ->setStartupHelpHeadline($this->toNullableString($row, 'startup_help_headline'))
                ->setStartupHelpText($this->toNullableString($row, 'startup_help_text'))
                ->setFeaturesHeadline($this->toNullableString($row, 'features_headline'))
                ->setFeaturesText($this->toNullableString($row, 'features_text'))
                ->setRecommendationHeadline($this->toNullableString($row, 'recommendation_headline'))
                ->setRecommendationText($this->toNullableString($row, 'recommendation_text'))
                ->setMetaTitle($this->toNullableString($row, 'meta_title'))
                ->setMetaDescription($this->toNullableString($row, 'meta_description'))
                ->setMetaKeywords($this->toNullableString($row, 'meta_keywords'))
                ->setCanonicalUrl($this->toNullableString($row, 'canonical_url'))
                ->setRelatedProductsCount($this->toInt($row, 'related_products'))
                ->setTagsCount($this->toInt($row, 'tags'))
                ->setShippingFree($this->toBool($row, 'shipping_free'))
                ->setBadge($this->toNullableString($row, 'badge'))
                ->setOptionsCount($this->toInt($row, 'options'))
                ->setLexofficeProductId($this->toNullableString($row, 'lexoffice_product_id'));

            $this->productCache[$uid] = $product;
        }
    }

    /**
     * @param ShopProductOptionRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importProductOptions(ShopProductOptionRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $option = $repository->find($uid);
            if (!$option instanceof ShopProductOption) {
                $option = new ShopProductOption();
                $option->setUid($uid);
                $this->entityManager->persist($option);
            }

            $option
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setTitle($this->toString($row, 'title'))
                ->setType($this->toString($row, 'type'))
                ->setIsRequired($this->toBool($row, 'is_required'))
                ->setValuesCount($this->toInt($row, 'values'))
                ->setProduct($this->resolveProduct($this->toInt($row, 'product')));

            $this->optionCache[$uid] = $option;
        }
    }

    /**
     * @param ShopProductOptionValueRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importProductOptionValues(ShopProductOptionValueRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $value = $repository->find($uid);
            if (!$value instanceof ShopProductOptionValue) {
                $value = new ShopProductOptionValue();
                $value->setUid($uid);
                $this->entityManager->persist($value);
            }

            $value
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setTitle($this->toString($row, 'title'))
                ->setPriceModifier($this->toDecimal($row, 'price_modifier', '0.00'))
                ->setSpecialPrice($this->toDecimal($row, 'special_price', '0.00'))
                ->setDescription($this->toNullableString($row, 'description'))
                ->setImage($this->toNullableString($row, 'image'))
                ->setProductOption($this->resolveOption($this->toInt($row, 'product_option')));
        }
    }

    /**
     * @param ShopProductDeliveryScopeRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importDeliveryScopes(ShopProductDeliveryScopeRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $scope = $repository->find($uid);
            if (!$scope instanceof ShopProductDeliveryScope) {
                $scope = new ShopProductDeliveryScope();
                $scope->setUid($uid);
                $this->entityManager->persist($scope);
            }

            $scope
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setSysLanguageUid($this->toInt($row, 'sys_language_uid'))
                ->setL10nParent($this->toInt($row, 'l10n_parent'))
                ->setL10nState($this->toNullableString($row, 'l10n_state'))
                ->setL10nDiffsource($this->toNullableString($row, 'l10n_diffsource'))
                ->setTitle($this->toString($row, 'title'))
                ->setDescription($this->toNullableString($row, 'description'))
                ->setProduct($this->resolveProduct($this->toInt($row, 'lieferumfang')));
        }
    }

    /**
     * @param ShopProductLinkRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importProductLinks(ShopProductLinkRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $link = $repository->find($uid);
            if (!$link instanceof ShopProductLink) {
                $link = new ShopProductLink();
                $link->setUid($uid);
                $this->entityManager->persist($link);
            }

            $link
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setTitle($this->toString($row, 'title'))
                ->setUrl($this->toNullableString($row, 'url'))
                ->setProduct($this->resolveProduct($this->toInt($row, 'product')));
        }
    }

    /**
     * @param ShopDownloadRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importDownloads(ShopDownloadRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $download = $repository->find($uid);
            if (!$download instanceof ShopDownload) {
                $download = new ShopDownload();
                $download->setUid($uid);
                $this->entityManager->persist($download);
            }

            $download
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setSysLanguageUid($this->toInt($row, 'sys_language_uid'))
                ->setL10nParent($this->toInt($row, 'l10n_parent'))
                ->setL10nState($this->toNullableString($row, 'l10n_state'))
                ->setL10nDiffsource($this->toNullableString($row, 'l10n_diffsource'))
                ->setTitle($this->toString($row, 'title'))
                ->setDescription($this->toNullableString($row, 'description'))
                ->setFile($this->toNullableString($row, 'file'))
                ->setProduct($this->resolveProduct($this->toInt($row, 'product')));
        }
    }

    /**
     * @param ShopVideoRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importVideos(ShopVideoRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $video = $repository->find($uid);
            if (!$video instanceof ShopVideo) {
                $video = new ShopVideo();
                $video->setUid($uid);
                $this->entityManager->persist($video);
            }

            $video
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setSorting($this->toInt($row, 'sorting'))
                ->setSysLanguageUid($this->toInt($row, 'sys_language_uid'))
                ->setL10nParent($this->toInt($row, 'l10n_parent'))
                ->setL10nState($this->toNullableString($row, 'l10n_state'))
                ->setL10nDiffsource($this->toNullableString($row, 'l10n_diffsource'))
                ->setTitle($this->toString($row, 'title'))
                ->setDescription($this->toNullableString($row, 'description'))
                ->setFile($this->toNullableString($row, 'file'))
                ->setVideoUrl($this->toNullableString($row, 'video_url'))
                ->setProduct($this->resolveProduct($this->toInt($row, 'product')));
        }
    }

    /**
     * @param ShopOrderRepository $repository
     * @param array<int, mixed> $rows
     */
    private function importOrders(ShopOrderRepository $repository, array $rows): void
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $uid = $this->toInt($row, 'uid');
            if ($uid <= 0) {
                continue;
            }

            $order = $repository->find($uid);
            if (!$order instanceof ShopOrder) {
                $order = new ShopOrder();
                $order->setUid($uid);
                $this->entityManager->persist($order);
            }

            $order
                ->setPid($this->toInt($row, 'pid'))
                ->setTstamp($this->toInt($row, 'tstamp'))
                ->setCrdate($this->toInt($row, 'crdate'))
                ->setDeleted($this->toBool($row, 'deleted'))
                ->setHidden($this->toBool($row, 'hidden'))
                ->setOrderNumber($this->toNullableString($row, 'order_number'))
                ->setStatus($this->toInt($row, 'status'))
                ->setPaymentStatus($this->toInt($row, 'payment_status'))
                ->setExportStatus($this->toInt($row, 'export_status'))
                ->setPaymentMethod($this->resolvePayment($this->toInt($row, 'payment_method')))
                ->setShippingMethod($this->resolveShipping($this->toInt($row, 'shipping_method')))
                ->setTransactionId($this->toNullableString($row, 'transaction_id'))
                ->setFrontendUserId($this->toInt($row, 'frontend_user_id'))
                ->setItemsJson($this->toNullableString($row, 'items_json'))
                ->setDiscount($this->toDecimal($row, 'discount', '0.00'))
                ->setShippingCost($this->toDecimal($row, 'shipping_cost', '0.00'))
                ->setSubtotal($this->toDecimal($row, 'subtotal', '0.00'))
                ->setTaxAmount($this->toDecimal($row, 'tax_amount', '0.00'))
                ->setTotal($this->toDecimal($row, 'total', '0.00'))
                ->setCreatedAt($this->toInt($row, 'created_at'))
                ->setCustomerFirstName($this->toNullableString($row, 'customer_first_name'))
                ->setCustomerLastName($this->toNullableString($row, 'customer_last_name'))
                ->setCustomerName($this->toNullableString($row, 'customer_name'))
                ->setCustomerCompany($this->toNullableString($row, 'customer_company'))
                ->setCustomerEmail($this->toNullableString($row, 'customer_email'))
                ->setBillingAddress($this->toNullableString($row, 'billing_address'))
                ->setBillingStreet($this->toNullableString($row, 'billing_street'))
                ->setBillingCity($this->toNullableString($row, 'billing_city'))
                ->setBillingZip($this->toNullableString($row, 'billing_zip'))
                ->setBillingCountry($this->toNullableString($row, 'billing_country'))
                ->setShippingAddress($this->toNullableString($row, 'shipping_address'))
                ->setShippingStreet($this->toNullableString($row, 'shipping_street'))
                ->setShippingCity($this->toNullableString($row, 'shipping_city'))
                ->setShippingZip($this->toNullableString($row, 'shipping_zip'))
                ->setShippingCountry($this->toNullableString($row, 'shipping_country'))
                ->setShippingFirstName($this->toNullableString($row, 'shipping_first_name'))
                ->setShippingLastName($this->toNullableString($row, 'shipping_last_name'))
                ->setShippingCompany($this->toNullableString($row, 'shipping_company'))
                ->setShippingSameAsBilling($this->toBool($row, 'shipping_same_as_billing'))
                ->setComment($this->toNullableString($row, 'comment'))
                ->setNotes($this->toNullableString($row, 'notes'))
                ->setLexofficeInvoiceId($this->toNullableString($row, 'lexoffice_invoice_id'))
                ->setLexofficeInvoicePdf($this->toNullableString($row, 'lexoffice_invoice_pdf'));
        }
    }

    /**
     * @param array<int, mixed> $rows
     */
    private function importJoinTable(Connection $connection, string $table, array $rows): void
    {
        $connection->executeStatement('DELETE FROM ' . $table);

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $connection->insert($table, [
                'uid_local' => $this->toInt($row, 'uid_local'),
                'uid_foreign' => $this->toInt($row, 'uid_foreign'),
                'sorting' => $this->toInt($row, 'sorting'),
                'sorting_foreign' => $this->toInt($row, 'sorting_foreign'),
            ]);
        }
    }

    private function resolveManufacturer(int $uid): ?ShopManufacturer
    {
        if ($uid <= 0) {
            return null;
        }

        $cached = $this->manufacturerCache[$uid] ?? null;
        if ($cached instanceof ShopManufacturer) {
            return $cached;
        }

        $manufacturer = $this->entityManager->getRepository(ShopManufacturer::class)->find($uid);
        if ($manufacturer instanceof ShopManufacturer) {
            $this->manufacturerCache[$uid] = $manufacturer;
        }

        return $manufacturer;
    }

    private function resolveTax(int $uid): ?ShopTax
    {
        if ($uid <= 0) {
            return null;
        }

        $cached = $this->taxCache[$uid] ?? null;
        if ($cached instanceof ShopTax) {
            return $cached;
        }

        $tax = $this->entityManager->getRepository(ShopTax::class)->find($uid);
        if ($tax instanceof ShopTax) {
            $this->taxCache[$uid] = $tax;
        }

        return $tax;
    }

    private function resolveProduct(int $uid): ?ShopProduct
    {
        if ($uid <= 0) {
            return null;
        }

        $cached = $this->productCache[$uid] ?? null;
        if ($cached instanceof ShopProduct) {
            return $cached;
        }

        $product = $this->entityManager->getRepository(ShopProduct::class)->find($uid);
        if ($product instanceof ShopProduct) {
            $this->productCache[$uid] = $product;
        }

        return $product;
    }

    private function resolveOption(int $uid): ?ShopProductOption
    {
        if ($uid <= 0) {
            return null;
        }

        $cached = $this->optionCache[$uid] ?? null;
        if ($cached instanceof ShopProductOption) {
            return $cached;
        }

        $option = $this->entityManager->getRepository(ShopProductOption::class)->find($uid);
        if ($option instanceof ShopProductOption) {
            $this->optionCache[$uid] = $option;
        }

        return $option;
    }

    private function resolveShipping(int $uid): ?ShopShippingMethod
    {
        if ($uid <= 0) {
            return null;
        }

        $cached = $this->shippingCache[$uid] ?? null;
        if ($cached instanceof ShopShippingMethod) {
            return $cached;
        }

        $method = $this->entityManager->getRepository(ShopShippingMethod::class)->find($uid);
        if ($method instanceof ShopShippingMethod) {
            $this->shippingCache[$uid] = $method;
        }

        return $method;
    }

    private function resolvePayment(int $uid): ?ShopPaymentMethod
    {
        if ($uid <= 0) {
            return null;
        }

        $cached = $this->paymentCache[$uid] ?? null;
        if ($cached instanceof ShopPaymentMethod) {
            return $cached;
        }

        $method = $this->entityManager->getRepository(ShopPaymentMethod::class)->find($uid);
        if ($method instanceof ShopPaymentMethod) {
            $this->paymentCache[$uid] = $method;
        }

        return $method;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toInt(array $row, string $key): int
    {
        if (!array_key_exists($key, $row)) {
            return 0;
        }

        $value = $row[$key];
        if (null === $value || '' === $value) {
            return 0;
        }

        return (int) $value;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toBool(array $row, string $key): bool
    {
        if (!array_key_exists($key, $row)) {
            return false;
        }

        $value = $row[$key];
        if (null === $value) {
            return false;
        }

        return '1' === (string) $value || 'true' === strtolower((string) $value);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toString(array $row, string $key, string $default = ''): string
    {
        if (!array_key_exists($key, $row)) {
            return $default;
        }

        $value = $row[$key];
        if (null === $value) {
            return $default;
        }

        return (string) $value;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toNullableString(array $row, string $key): ?string
    {
        if (!array_key_exists($key, $row)) {
            return null;
        }

        $value = $row[$key];
        if (null === $value) {
            return null;
        }

        return (string) $value;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toDecimal(array $row, string $key, string $default): string
    {
        $value = $this->toNullableString($row, $key);
        if (null === $value || '' === $value) {
            return $default;
        }

        $normalized = trim(html_entity_decode(strip_tags($value)));
        if ('' === $normalized) {
            return $default;
        }

        if (!preg_match('/-?\\d+(?:[\\.,]\\d+)?/', $normalized, $matches)) {
            return $default;
        }

        $number = $matches[0];
        if (str_contains($number, ',') && str_contains($number, '.')) {
            $number = str_replace('.', '', $number);
            $number = str_replace(',', '.', $number);
        } elseif (str_contains($number, ',')) {
            $number = str_replace(',', '.', $number);
        }

        $number = preg_replace('/[^0-9\\.-]/', '', $number) ?? '';
        if ('' === $number || '-' === $number || '.' === $number || '-.' === $number) {
            return $default;
        }

        return $number;
    }
}
