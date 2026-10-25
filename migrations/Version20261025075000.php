<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025075000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add weight, hsn, and price fields to VehicleEngine';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD weight INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD hsn VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD price NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP COLUMN weight');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP COLUMN hsn');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP COLUMN price');
    }
}
