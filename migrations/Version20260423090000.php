<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional Media Manager image relation to brand master records.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master ADD image_media_asset_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_BRAND_MASTER_IMAGE_MEDIA_ASSET ON tx_chiptuningdatabase_brand_master (image_media_asset_id)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master ADD CONSTRAINT FK_BRAND_MASTER_IMAGE_MEDIA_ASSET FOREIGN KEY (image_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master DROP FOREIGN KEY FK_BRAND_MASTER_IMAGE_MEDIA_ASSET');
        $this->addSql('DROP INDEX IDX_BRAND_MASTER_IMAGE_MEDIA_ASSET ON tx_chiptuningdatabase_brand_master');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master DROP image_media_asset_id');
    }
}
