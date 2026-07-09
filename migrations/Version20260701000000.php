<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add EcuToolEntry: multiple tool manufacturers per ECU, each with own methods';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE tfd_ecu_tool_entry (
                id INT AUTO_INCREMENT NOT NULL,
                ecu_id INT NOT NULL,
                tool_manufacturer_id INT DEFAULT NULL,
                position INT DEFAULT NULL,
                PRIMARY KEY (id),
                INDEX IDX_ECU_TOOL_ENTRY_ECU (ecu_id),
                INDEX IDX_ECU_TOOL_ENTRY_MFR (tool_manufacturer_id),
                CONSTRAINT FK_ECU_TOOL_ENTRY_ECU
                    FOREIGN KEY (ecu_id) REFERENCES tx_chiptuningdatabase_domain_model_ecu (uid) ON DELETE CASCADE,
                CONSTRAINT FK_ECU_TOOL_ENTRY_MFR
                    FOREIGN KEY (tool_manufacturer_id) REFERENCES tx_chiptuningdatabase_domain_model_toolmanufacturer (uid) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE tfd_ecu_tool_entry_methode_mm (
                entry_id INT NOT NULL,
                methode_id INT NOT NULL,
                PRIMARY KEY (entry_id, methode_id),
                INDEX IDX_ENTRY_METHODE_ENTRY (entry_id),
                INDEX IDX_ENTRY_METHODE_METHODE (methode_id),
                CONSTRAINT FK_ENTRY_METHODE_ENTRY
                    FOREIGN KEY (entry_id) REFERENCES tfd_ecu_tool_entry (id) ON DELETE CASCADE,
                CONSTRAINT FK_ENTRY_METHODE_METHODE
                    FOREIGN KEY (methode_id) REFERENCES tx_chiptuningdatabase_domain_model_toolmethode (uid) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        // Migrate existing single tool_manufacturer + toolMethods to new structure
        $this->addSql(<<<'SQL'
            INSERT INTO tfd_ecu_tool_entry (ecu_id, tool_manufacturer_id, position)
            SELECT uid, tool_manufacturer, 0
            FROM tx_chiptuningdatabase_domain_model_ecu
            WHERE tool_manufacturer IS NOT NULL
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO tfd_ecu_tool_entry_methode_mm (entry_id, methode_id)
            SELECT e.id, mm.uid_foreign
            FROM tfd_ecu_tool_entry e
            JOIN tx_chiptuningdatabase_domain_model_ecu ecu ON ecu.uid = e.ecu_id
            JOIN tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm mm ON mm.uid_local = ecu.uid
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS tfd_ecu_tool_entry_methode_mm');
        $this->addSql('DROP TABLE IF EXISTS tfd_ecu_tool_entry');
    }
}
