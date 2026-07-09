<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add performance chart fields to VehicleStage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD original_ps INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD original_nm INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD rpm_start INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD rpm_end INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD engine_type VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP COLUMN original_ps');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP COLUMN original_nm');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP COLUMN rpm_start');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP COLUMN rpm_end');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP COLUMN engine_type');
    }
}
