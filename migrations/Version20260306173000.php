<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add required vehicle, tuning and device metadata fields to auftrag.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auftrag ADD vehicle_type VARCHAR(80) DEFAULT NULL, ADD manufacturer VARCHAR(120) DEFAULT NULL, ADD model VARCHAR(180) DEFAULT NULL, ADD built VARCHAR(50) DEFAULT NULL, ADD engine VARCHAR(180) DEFAULT NULL, ADD vehicle_display VARCHAR(255) DEFAULT NULL, ADD tuning_tool VARCHAR(80) DEFAULT NULL, ADD tuning_method VARCHAR(80) DEFAULT NULL, ADD slave_or_master VARCHAR(20) DEFAULT NULL, ADD stage_label VARCHAR(80) DEFAULT NULL, ADD device_name VARCHAR(80) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auftrag DROP vehicle_type, DROP manufacturer, DROP model, DROP built, DROP engine, DROP vehicle_display, DROP tuning_tool, DROP tuning_method, DROP slave_or_master, DROP stage_label, DROP device_name');
    }
}
