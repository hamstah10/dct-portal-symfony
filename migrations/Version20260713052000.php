<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260713052000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add showOptionCategories/allowVehicleEditing columns on VehicleDataContext (context-only toggles)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context ADD show_option_categories TINYINT(1) NOT NULL DEFAULT 1, ADD allow_vehicle_editing TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context DROP show_option_categories, DROP allow_vehicle_editing');
    }
}
