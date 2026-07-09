<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260307221500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add media manager icon relation to ecu options.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption ADD icon_media_asset_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_3DFD385B2D711E27 ON tx_chiptuningdatabase_domain_model_ecuoption (icon_media_asset_id)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption ADD CONSTRAINT FK_3DFD385B2D711E27 FOREIGN KEY (icon_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption DROP FOREIGN KEY FK_3DFD385B2D711E27');
        $this->addSql('DROP INDEX IDX_3DFD385B2D711E27 ON tx_chiptuningdatabase_domain_model_ecuoption');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption DROP icon_media_asset_id');
    }
}

