<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260713050000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vehicle-data feature toggles (context/pool), stage dynoChart column, and gearbox tool entries';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context ADD show_prices TINYINT(1) NOT NULL DEFAULT 1, ADD show_hints TINYINT(1) NOT NULL DEFAULT 1, ADD show_gearbox TINYINT(1) NOT NULL DEFAULT 1, ADD show_dyno TINYINT(1) NOT NULL DEFAULT 1, ADD show_eco_stage TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool ADD show_prices TINYINT(1) NOT NULL DEFAULT 1, ADD show_hints TINYINT(1) NOT NULL DEFAULT 1, ADD show_gearbox TINYINT(1) NOT NULL DEFAULT 1, ADD show_dyno TINYINT(1) NOT NULL DEFAULT 1, ADD show_eco_stage TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD dyno_chart JSON DEFAULT NULL');

        $this->addSql('CREATE TABLE tfd_gearbox_tool_entry (id INT AUTO_INCREMENT NOT NULL, gearbox_id INT NOT NULL, tool_manufacturer_id INT DEFAULT NULL, position INT DEFAULT NULL, INDEX IDX_GEARBOX_TOOL_ENTRY_GEARBOX (gearbox_id), INDEX IDX_GEARBOX_TOOL_ENTRY_MFR (tool_manufacturer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tfd_gearbox_tool_entry_methode_mm (entry_id INT NOT NULL, methode_id INT NOT NULL, INDEX IDX_GEARBOX_ENTRY_METHODE_ENTRY (entry_id), INDEX IDX_GEARBOX_ENTRY_METHODE_METHODE (methode_id), PRIMARY KEY(entry_id, methode_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tfd_gearbox_tool_entry ADD CONSTRAINT FK_GEARBOX_TOOL_ENTRY_GEARBOX FOREIGN KEY (gearbox_id) REFERENCES tx_chiptuningdatabase_gearbox (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_gearbox_tool_entry ADD CONSTRAINT FK_GEARBOX_TOOL_ENTRY_MFR FOREIGN KEY (tool_manufacturer_id) REFERENCES tx_chiptuningdatabase_domain_model_toolmanufacturer (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tfd_gearbox_tool_entry_methode_mm ADD CONSTRAINT FK_GEARBOX_ENTRY_METHODE_ENTRY FOREIGN KEY (entry_id) REFERENCES tfd_gearbox_tool_entry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_gearbox_tool_entry_methode_mm ADD CONSTRAINT FK_GEARBOX_ENTRY_METHODE_METHODE FOREIGN KEY (methode_id) REFERENCES tx_chiptuningdatabase_domain_model_toolmethode (uid) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_gearbox_tool_entry_methode_mm DROP FOREIGN KEY FK_GEARBOX_ENTRY_METHODE_ENTRY');
        $this->addSql('ALTER TABLE tfd_gearbox_tool_entry_methode_mm DROP FOREIGN KEY FK_GEARBOX_ENTRY_METHODE_METHODE');
        $this->addSql('ALTER TABLE tfd_gearbox_tool_entry DROP FOREIGN KEY FK_GEARBOX_TOOL_ENTRY_GEARBOX');
        $this->addSql('ALTER TABLE tfd_gearbox_tool_entry DROP FOREIGN KEY FK_GEARBOX_TOOL_ENTRY_MFR');
        $this->addSql('DROP TABLE tfd_gearbox_tool_entry_methode_mm');
        $this->addSql('DROP TABLE tfd_gearbox_tool_entry');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP dyno_chart');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context DROP show_prices, DROP show_hints, DROP show_gearbox, DROP show_dyno, DROP show_eco_stage');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool DROP show_prices, DROP show_hints, DROP show_gearbox, DROP show_dyno, DROP show_eco_stage');
    }
}
