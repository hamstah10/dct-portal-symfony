<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260307213000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add media manager icon relation to gearbox.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox ADD icon_media_asset_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_94FFA4A943DC12A ON tx_chiptuningdatabase_gearbox (icon_media_asset_id)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox ADD CONSTRAINT FK_94FFA4A943DC12A FOREIGN KEY (icon_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox DROP FOREIGN KEY FK_94FFA4A943DC12A');
        $this->addSql('DROP INDEX IDX_94FFA4A943DC12A ON tx_chiptuningdatabase_gearbox');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox DROP icon_media_asset_id');
    }
}

