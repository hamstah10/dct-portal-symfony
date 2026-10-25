<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261011010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add logo_media_asset_id to shop manufacturers and clear numeric TYPO3 logo references';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_manufacturer ADD logo_media_asset_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_SHOP_MANUFACTURER_LOGO_MEDIA_ASSET ON tx_tuningtoolshop_domain_model_manufacturer (logo_media_asset_id)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_manufacturer ADD CONSTRAINT FK_SHOP_MANUFACTURER_LOGO_MEDIA_ASSET FOREIGN KEY (logo_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
        $this->addSql("UPDATE tx_tuningtoolshop_domain_model_manufacturer SET logo = NULL WHERE logo REGEXP '^[0-9]+$'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_manufacturer DROP FOREIGN KEY FK_SHOP_MANUFACTURER_LOGO_MEDIA_ASSET');
        $this->addSql('DROP INDEX IDX_SHOP_MANUFACTURER_LOGO_MEDIA_ASSET ON tx_tuningtoolshop_domain_model_manufacturer');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_manufacturer DROP logo_media_asset_id');
    }
}
