<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260713051000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add EcuOptionCategoryMaster (with icon/sorting) and category_master_id on EcuOption + EcuOptionMaster, seed default categories';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_ecu_option_category_master (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, icon_media_asset_id INT DEFAULT NULL, active TINYINT(1) NOT NULL DEFAULT 1, sorting INT DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_ECU_OPTION_CATEGORY_MASTER_SLUG (slug), INDEX IDX_ECU_OPTION_CATEGORY_MASTER_ICON_MEDIA_ASSET (icon_media_asset_id), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_category_master ADD CONSTRAINT FK_ECU_OPTION_CATEGORY_MASTER_ICON_MEDIA_ASSET FOREIGN KEY (icon_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption ADD category_master_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_ECU_OPTION_CATEGORY_MASTER ON tx_chiptuningdatabase_domain_model_ecuoption (category_master_id)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption ADD CONSTRAINT FK_ECU_OPTION_CATEGORY_MASTER FOREIGN KEY (category_master_id) REFERENCES tx_chiptuningdatabase_ecu_option_category_master (uid) ON DELETE SET NULL');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master ADD category_master_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_ECU_OPTION_MASTER_CATEGORY_MASTER ON tx_chiptuningdatabase_ecu_option_master (category_master_id)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master ADD CONSTRAINT FK_ECU_OPTION_MASTER_CATEGORY_MASTER FOREIGN KEY (category_master_id) REFERENCES tx_chiptuningdatabase_ecu_option_category_master (uid) ON DELETE SET NULL');

        $rows = [
            ['Performance', 'performance', 10],
            ['Abgassystem', 'abgassystem', 20],
            ['Motor', 'motor', 30],
            ['Kraftstoff', 'kraftstoff', 40],
            ['Diagnose', 'diagnose', 50],
            ['Service', 'service', 60],
            ['Spezial', 'spezial', 70],
        ];
        foreach ($rows as [$name, $slug, $sorting]) {
            $this->addSql('INSERT INTO tx_chiptuningdatabase_ecu_option_category_master (name, slug, active, sorting) VALUES (?, ?, 1, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), active = VALUES(active), sorting = VALUES(sorting)', [$name, $slug, $sorting]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master DROP FOREIGN KEY FK_ECU_OPTION_MASTER_CATEGORY_MASTER');
        $this->addSql('DROP INDEX IDX_ECU_OPTION_MASTER_CATEGORY_MASTER ON tx_chiptuningdatabase_ecu_option_master');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master DROP category_master_id');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption DROP FOREIGN KEY FK_ECU_OPTION_CATEGORY_MASTER');
        $this->addSql('DROP INDEX IDX_ECU_OPTION_CATEGORY_MASTER ON tx_chiptuningdatabase_domain_model_ecuoption');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption DROP category_master_id');

        $this->addSql('DROP TABLE tx_chiptuningdatabase_ecu_option_category_master');
    }
}
