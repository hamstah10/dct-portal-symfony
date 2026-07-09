<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520011329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add icon_media_asset_id FK to ecu_option_master.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master ADD COLUMN icon_media_asset_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master ADD CONSTRAINT FK_ecu_option_master_icon_media FOREIGN KEY (icon_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_ECU_OPTION_MASTER_ICON_MEDIA ON tx_chiptuningdatabase_ecu_option_master (icon_media_asset_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master DROP FOREIGN KEY FK_ecu_option_master_icon_media');
        $this->addSql('DROP INDEX IDX_ECU_OPTION_MASTER_ICON_MEDIA ON tx_chiptuningdatabase_ecu_option_master');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master DROP COLUMN icon_media_asset_id');
    }
}
