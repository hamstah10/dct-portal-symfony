<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional vehicle data pool selection to website widgets';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config ADD vehicle_data_pool INT DEFAULT NULL');
        $this->addSql('ALTER TABLE website_widget_config ADD CONSTRAINT FK_7D23B311C067CF9D FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7D23B311C067CF9D ON website_widget_config (vehicle_data_pool)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config DROP FOREIGN KEY FK_7D23B311C067CF9D');
        $this->addSql('DROP INDEX IDX_7D23B311C067CF9D ON website_widget_config');
        $this->addSql('ALTER TABLE website_widget_config DROP vehicle_data_pool');
    }
}
