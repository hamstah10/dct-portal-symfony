<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260404193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vehicle_engine relation to ECUs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu ADD vehicle_engine INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu ADD CONSTRAINT FK_ECU_VEHICLE_ENGINE FOREIGN KEY (vehicle_engine) REFERENCES tx_chiptuningdatabase_vehicleengine (uid) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_ECU_VEHICLE_ENGINE ON tx_chiptuningdatabase_domain_model_ecu (vehicle_engine)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP FOREIGN KEY FK_ECU_VEHICLE_ENGINE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP INDEX IDX_ECU_VEHICLE_ENGINE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP vehicle_engine');
    }
}
