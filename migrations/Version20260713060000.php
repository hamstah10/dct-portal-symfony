<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Direkte Motor-Option-Zuordnung (M:M) wie im TFD-CRM.
 */
final class Version20260713060000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MM-Tabelle tx_chiptuningdatabase_vehicleengine_ecuoption_mm (VehicleEngine <-> EcuOption)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE tx_chiptuningdatabase_vehicleengine_ecuoption_mm (
                uid_local INT NOT NULL,
                uid_foreign INT NOT NULL,
                INDEX IDX_C55C23215C4C8485 (uid_local),
                INDEX IDX_C55C23211D17607B (uid_foreign),
                PRIMARY KEY (uid_local, uid_foreign)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine_ecuoption_mm ADD CONSTRAINT FK_C55C23215C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_vehicleengine (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine_ecuoption_mm ADD CONSTRAINT FK_C55C23211D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_domain_model_ecuoption (uid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehicleengine_ecuoption_mm');
    }
}
