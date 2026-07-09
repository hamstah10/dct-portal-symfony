<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520005952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add module_slug to ecu_option_master for linking to module hints.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master ADD COLUMN module_slug VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master DROP COLUMN module_slug');
    }
}
