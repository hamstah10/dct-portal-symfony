<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260405100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vehicle data pool relation to portal';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal ADD vehicle_data_pool_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE portal ADD CONSTRAINT FK_3C1C9C3A1C6A2A0A FOREIGN KEY (vehicle_data_pool_id) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3C1C9C3A1C6A2A0A ON portal (vehicle_data_pool_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal DROP FOREIGN KEY FK_3C1C9C3A1C6A2A0A');
        $this->addSql('DROP INDEX IDX_3C1C9C3A1C6A2A0A ON portal');
        $this->addSql('ALTER TABLE portal DROP vehicle_data_pool_id');
    }
}
