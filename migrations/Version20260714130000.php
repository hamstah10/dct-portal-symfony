<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260714130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add processor (MCU) field to EcuMaster (Sync mit verwaltung.tuningfux.de)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_master ADD processor VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_master DROP processor');
    }
}
