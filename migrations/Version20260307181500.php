<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260307181500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend vehicle stages with media manager icon selection.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD icon_media_asset_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_D1C0C69C2D711E27 ON tx_chiptuningdatabase_vehiclestage (icon_media_asset_id)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD CONSTRAINT FK_D1C0C69C2D711E27 FOREIGN KEY (icon_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP FOREIGN KEY FK_D1C0C69C2D711E27');
        $this->addSql('DROP INDEX IDX_D1C0C69C2D711E27 ON tx_chiptuningdatabase_vehiclestage');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP icon_media_asset_id');
    }
}

