<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260805204000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add imageMediaAsset to VehicleBrand';
    }

    public function up(Schema $schema): void
    {
        // Column already exists from database restore, just ensure constraints are set
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD CONSTRAINT FK_VEHICLEBRAND_IMAGE_MEDIA FOREIGN KEY (image_media_asset) REFERENCES media_asset (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('tx_chiptuningdatabase_vehiclebrand');
        if ($table->hasColumn('image_media_asset')) {
            $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP FOREIGN KEY FK_FEEACAD9D2DE099');
            $this->addSql('DROP INDEX IDX_FEEACAD9D2DE099 ON tx_chiptuningdatabase_vehiclebrand');
            $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP image_media_asset');
        }
    }
}
