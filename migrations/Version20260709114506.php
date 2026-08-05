<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260709114506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add showPrices/showHints/showGearbox/showDyno/showEcoStage toggles to VehicleDataContext and VehicleDataPool';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context ADD show_prices TINYINT(1) NOT NULL DEFAULT 1, ADD show_hints TINYINT(1) NOT NULL DEFAULT 1, ADD show_gearbox TINYINT(1) NOT NULL DEFAULT 1, ADD show_dyno TINYINT(1) NOT NULL DEFAULT 1, ADD show_eco_stage TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool ADD show_prices TINYINT(1) NOT NULL DEFAULT 1, ADD show_hints TINYINT(1) NOT NULL DEFAULT 1, ADD show_gearbox TINYINT(1) NOT NULL DEFAULT 1, ADD show_dyno TINYINT(1) NOT NULL DEFAULT 1, ADD show_eco_stage TINYINT(1) NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context DROP show_prices, DROP show_hints, DROP show_gearbox, DROP show_dyno, DROP show_eco_stage');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool DROP show_prices, DROP show_hints, DROP show_gearbox, DROP show_dyno, DROP show_eco_stage');
    }
}
