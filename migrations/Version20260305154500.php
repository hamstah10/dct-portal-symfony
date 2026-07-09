<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305154500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add control_unit_kind (TCU/MCU) to ECU entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu ADD control_unit_kind VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP control_unit_kind');
    }
}
