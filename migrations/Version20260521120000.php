<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional vehicle type selection to website widgets';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config ADD vehicle_type INT DEFAULT NULL');
        $this->addSql('ALTER TABLE website_widget_config ADD CONSTRAINT FK_WEBSITE_WIDGET_VEHICLE_TYPE FOREIGN KEY (vehicle_type) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_WEBSITE_WIDGET_VEHICLE_TYPE ON website_widget_config (vehicle_type)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config DROP FOREIGN KEY FK_WEBSITE_WIDGET_VEHICLE_TYPE');
        $this->addSql('DROP INDEX IDX_WEBSITE_WIDGET_VEHICLE_TYPE ON website_widget_config');
        $this->addSql('ALTER TABLE website_widget_config DROP vehicle_type');
    }
}
