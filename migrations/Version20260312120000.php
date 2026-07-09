<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add media manager relations for vehicle engine image and glb model.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD image_media_asset_id INT DEFAULT NULL, ADD glb_media_asset_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_VEHICLEENGINE_IMAGE_MEDIA_ASSET ON tx_chiptuningdatabase_vehicleengine (image_media_asset_id)');
        $this->addSql('CREATE INDEX IDX_VEHICLEENGINE_GLB_MEDIA_ASSET ON tx_chiptuningdatabase_vehicleengine (glb_media_asset_id)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD CONSTRAINT FK_VEHICLEENGINE_IMAGE_MEDIA_ASSET FOREIGN KEY (image_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD CONSTRAINT FK_VEHICLEENGINE_GLB_MEDIA_ASSET FOREIGN KEY (glb_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP FOREIGN KEY FK_VEHICLEENGINE_IMAGE_MEDIA_ASSET');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP FOREIGN KEY FK_VEHICLEENGINE_GLB_MEDIA_ASSET');
        $this->addSql('DROP INDEX IDX_VEHICLEENGINE_IMAGE_MEDIA_ASSET ON tx_chiptuningdatabase_vehicleengine');
        $this->addSql('DROP INDEX IDX_VEHICLEENGINE_GLB_MEDIA_ASSET ON tx_chiptuningdatabase_vehicleengine');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP image_media_asset_id, DROP glb_media_asset_id');
    }
}
