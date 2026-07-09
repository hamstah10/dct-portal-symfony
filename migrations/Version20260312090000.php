<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add UX icon column to profit calculator vehicle types.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype ADD icon VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype DROP icon');
    }
}
