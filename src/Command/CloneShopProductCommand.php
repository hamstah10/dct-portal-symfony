<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:shop:clone-product',
    description: 'Copy a shop product with its category/tag/media/related data and take over the starter fields from another product.',
)]
class CloneShopProductCommand extends Command
{
    private const TABLE = 'tx_tuningtoolshop_domain_model_product';
    private const MEDIA_TABLE = 'shop_product_media';

    private const STARTER_FIELDS = [
        'product_type',
        'badge',
        'headline',
        'operating_costs',
        'extensions',
        'startup_help_headline',
        'startup_help_text',
        'features_headline',
        'features_text',
        'recommendation_headline',
        'recommendation_text',
    ];

    private const MM_TABLES = [
        'categories' => 'tx_tuningtoolshop_product_category_mm',
        'tags' => 'tx_tuningtoolshop_product_tag_mm',
        'related_products' => 'tx_tuningtoolshop_product_relatedproducts_mm',
        'shipping_methods' => 'tx_tuningtoolshop_product_shippingmethod_mm',
    ];

    public function __construct(
        private readonly Connection $connection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'uid of the product to copy', '9')
            ->addOption('starter-from', null, InputOption::VALUE_REQUIRED, 'uid of the starter product whose starter fields and related products are taken over', '25')
            ->addOption('title', null, InputOption::VALUE_REQUIRED, 'Title of the new product')
            ->addOption('slug', null, InputOption::VALUE_REQUIRED, 'Slug of the new product (must be unique)')
            ->addOption('sku', null, InputOption::VALUE_REQUIRED, 'SKU of the new product')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only report what would be inserted');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourceUid = (int) $input->getOption('from');
        $starterUid = (int) $input->getOption('starter-from');
        $title = (string) $input->getOption('title');
        $slug = (string) $input->getOption('slug');
        $sku = (string) $input->getOption('sku');
        $dryRun = (bool) $input->getOption('dry-run');

        if ('' === $title || '' === $slug || '' === $sku) {
            $output->writeln('<error>--title, --slug and --sku are required.</error>');

            return Command::FAILURE;
        }

        $source = $this->fetchProduct($sourceUid);
        $starter = $this->fetchProduct($starterUid);
        if (null === $source || null === $starter) {
            $output->writeln(sprintf('<error>Product %d or %d not found.</error>', $sourceUid, $starterUid));

            return Command::FAILURE;
        }

        if ((int) $this->connection->fetchOne('SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE slug = ?', [$slug]) > 0) {
            $output->writeln(sprintf('<error>Slug "%s" already exists.</error>', $slug));

            return Command::FAILURE;
        }

        $newUid = (int) $this->connection->fetchOne('SELECT COALESCE(MAX(uid), 0) + 1 FROM ' . self::TABLE);

        $categories = $this->unionColumn(self::MM_TABLES['categories'], 'uid_local', 'uid_foreign', [$sourceUid, $starterUid]);
        $tags = $this->unionColumn(self::MM_TABLES['tags'], 'uid_local', 'uid_foreign', [$sourceUid, $starterUid]);
        $related = array_values(array_filter(
            array_unique(array_merge([$starterUid], $this->column(self::MM_TABLES['related_products'], 'uid_foreign', 'uid_local', $starterUid))),
            static fn (int $uid): bool => $uid !== $newUid,
        ));
        $shipping = $this->column(self::MM_TABLES['shipping_methods'], 'uid_foreign', 'uid_local', $sourceUid);
        $images = array_map('intval', $this->connection->fetchFirstColumn(
            'SELECT media_asset_id FROM ' . self::MEDIA_TABLE . ' WHERE product_uid = ?',
            [$sourceUid],
        ));

        $data = $source;
        unset($data['uid']);
        foreach (self::STARTER_FIELDS as $field) {
            $data[$field] = $starter[$field];
        }
        $now = time();
        $data = array_merge($data, [
            'pid' => $source['pid'],
            'crdate' => $now,
            'tstamp' => $now,
            'deleted' => 0,
            'hidden' => 0,
            'is_active' => 1,
            'api_active' => 1,
            'sys_language_uid' => 0,
            'l10n_parent' => 0,
            'l10n_diffsource' => null,
            'lexoffice_product_id' => null,
            'title' => $title,
            'slug' => $slug,
            'sku' => $sku,
            'special_price' => $source['price'],
            'categories' => count($categories),
            'tags' => count($tags),
            'related_products' => count($related),
            'shipping_methods' => count($shipping),
            'images' => count($images),
            'lieferumfang' => 0,
            'links' => 0,
            'documents' => 0,
            'videos' => 0,
            'options' => 0,
        ]);

        $output->writeln(sprintf('Neue uid: %d', $newUid));
        $output->writeln(sprintf('Titel: %s | Slug: %s | SKU: %s', $title, $slug, $sku));
        $output->writeln(sprintf('Starter-Felder von %d: product_type=%s, badge=%s', $starterUid, $starter['product_type'], $starter['badge']));
        $output->writeln(sprintf('Kategorien: %s', implode(',', $categories)));
        $output->writeln(sprintf('Tags: %s', implode(',', $tags)));
        $output->writeln(sprintf('Related: %s', implode(',', $related)));
        $output->writeln(sprintf('Bilder (media_asset_id): %s', implode(',', $images)));
        $output->writeln(sprintf('Versand: %s', implode(',', $shipping)));

        if ($dryRun) {
            $output->writeln('<comment>Dry-run: nichts geschrieben.</comment>');

            return Command::SUCCESS;
        }

        $this->connection->transactional(function (Connection $connection) use ($data, $newUid, $categories, $tags, $related, $shipping, $images): void {
            $connection->insert(self::TABLE, array_merge(['uid' => $newUid], $data));

            foreach ($categories as $uid) {
                $connection->insert(self::MM_TABLES['categories'], ['uid_local' => $newUid, 'uid_foreign' => $uid]);
            }
            foreach ($tags as $uid) {
                $connection->insert(self::MM_TABLES['tags'], ['uid_local' => $newUid, 'uid_foreign' => $uid]);
            }
            foreach ($related as $uid) {
                $connection->insert(self::MM_TABLES['related_products'], ['uid_local' => $newUid, 'uid_foreign' => $uid]);
            }
            foreach ($shipping as $uid) {
                $connection->insert(self::MM_TABLES['shipping_methods'], ['uid_local' => $newUid, 'uid_foreign' => $uid]);
            }
            foreach ($images as $assetId) {
                $connection->insert(self::MEDIA_TABLE, ['product_uid' => $newUid, 'media_asset_id' => $assetId]);
            }
        });

        $output->writeln(sprintf('<info>Produkt %d angelegt.</info>', $newUid));

        return Command::SUCCESS;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchProduct(int $uid): ?array
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM ' . self::TABLE . ' WHERE uid = ?', [$uid]);

        return false === $row ? null : $row;
    }

    /**
     * @return list<int>
     */
    private function column(string $table, string $selectColumn, string $whereColumn, int $uid): array
    {
        return array_map('intval', $this->connection->fetchFirstColumn(
            sprintf('SELECT %s FROM %s WHERE %s = ?', $selectColumn, $table, $whereColumn),
            [$uid],
        ));
    }

    /**
     * @param list<int> $uids
     *
     * @return list<int>
     */
    private function unionColumn(string $table, string $whereColumn, string $selectColumn, array $uids): array
    {
        $result = [];
        foreach ($uids as $uid) {
            foreach ($this->column($table, $selectColumn, $whereColumn, $uid) as $value) {
                $result[$value] = $value;
            }
        }

        return array_values($result);
    }
}
