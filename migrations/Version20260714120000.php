<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260714120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add read_methods field to VehicleEngine (Sync mit verwaltung.tuningfux.de)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD read_methods JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP read_methods');
    }
}
