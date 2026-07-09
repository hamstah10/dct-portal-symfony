<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260707070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add performance brand master entity (M, AMG, RS, ...) with optional references from vehicle series and engines.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_performance_brand (uid INT AUTO_INCREMENT NOT NULL, image_media_asset_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, INDEX IDX_2F7DCCD39D2DE099 (image_media_asset_id), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_performance_brand ADD CONSTRAINT FK_2F7DCCD39D2DE099 FOREIGN KEY (image_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries ADD performance_brand INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries ADD CONSTRAINT FK_VEHICLESERIES_PERFBRAND FOREIGN KEY (performance_brand) REFERENCES tx_chiptuningdatabase_performance_brand (uid) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_VEHICLESERIES_PERFBRAND ON tx_chiptuningdatabase_vehicleseries (performance_brand)');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD performance_brand INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD CONSTRAINT FK_VEHICLEENGINE_PERFBRAND FOREIGN KEY (performance_brand) REFERENCES tx_chiptuningdatabase_performance_brand (uid) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_VEHICLEENGINE_PERFBRAND ON tx_chiptuningdatabase_vehicleengine (performance_brand)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP FOREIGN KEY FK_VEHICLEENGINE_PERFBRAND');
        $this->addSql('DROP INDEX IDX_VEHICLEENGINE_PERFBRAND ON tx_chiptuningdatabase_vehicleengine');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP performance_brand');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries DROP FOREIGN KEY FK_VEHICLESERIES_PERFBRAND');
        $this->addSql('DROP INDEX IDX_VEHICLESERIES_PERFBRAND ON tx_chiptuningdatabase_vehicleseries');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries DROP performance_brand');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_performance_brand DROP FOREIGN KEY FK_2F7DCCD39D2DE099');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_performance_brand');
    }
}
