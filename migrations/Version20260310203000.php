<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310203000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add icon media references and image paths to profit calculator vehicle types and options.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype ADD icon_media_asset_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype MODIFY image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype ADD INDEX IDX_PROFITCALC_VEHICLETYPE_ICON_MEDIA (icon_media_asset_id)');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype ADD CONSTRAINT FK_PROFITCALC_VEHICLETYPE_ICON_MEDIA FOREIGN KEY (icon_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');

        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option ADD icon_media_asset_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option MODIFY image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option ADD INDEX IDX_PROFITCALC_OPTION_ICON_MEDIA (icon_media_asset_id)');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option ADD CONSTRAINT FK_PROFITCALC_OPTION_ICON_MEDIA FOREIGN KEY (icon_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option DROP FOREIGN KEY FK_PROFITCALC_OPTION_ICON_MEDIA');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option DROP INDEX IDX_PROFITCALC_OPTION_ICON_MEDIA');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option DROP icon_media_asset_id');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option MODIFY image INT DEFAULT NULL');

        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype DROP FOREIGN KEY FK_PROFITCALC_VEHICLETYPE_ICON_MEDIA');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype DROP INDEX IDX_PROFITCALC_VEHICLETYPE_ICON_MEDIA');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype DROP icon_media_asset_id');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype MODIFY image INT DEFAULT NULL');
    }
}
