<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shop_product_media join table linking ShopProduct to MediaAsset (scraped product images)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE shop_product_media (product_uid INT NOT NULL, media_asset_id INT NOT NULL, INDEX IDX_SHOP_PRODUCT_MEDIA_PRODUCT (product_uid), INDEX IDX_SHOP_PRODUCT_MEDIA_ASSET (media_asset_id), PRIMARY KEY(product_uid, media_asset_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shop_product_media ADD CONSTRAINT FK_SHOP_PRODUCT_MEDIA_PRODUCT FOREIGN KEY (product_uid) REFERENCES tx_tuningtoolshop_domain_model_product (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shop_product_media ADD CONSTRAINT FK_SHOP_PRODUCT_MEDIA_ASSET FOREIGN KEY (media_asset_id) REFERENCES media_asset (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shop_product_media DROP FOREIGN KEY FK_SHOP_PRODUCT_MEDIA_PRODUCT');
        $this->addSql('ALTER TABLE shop_product_media DROP FOREIGN KEY FK_SHOP_PRODUCT_MEDIA_ASSET');
        $this->addSql('DROP TABLE shop_product_media');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
