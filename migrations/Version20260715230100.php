<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260715230100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add imageMediaAsset (Media Manager icon picker) to VehicleBrand, matching BrandMaster.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD image_media_asset_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD CONSTRAINT FK_FEEACAD9D2DE099 FOREIGN KEY (image_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_FEEACAD9D2DE099 ON tx_chiptuningdatabase_vehiclebrand (image_media_asset_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP FOREIGN KEY FK_FEEACAD9D2DE099');
        $this->addSql('DROP INDEX IDX_FEEACAD9D2DE099 ON tx_chiptuningdatabase_vehiclebrand');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP image_media_asset_id');
    }
}
