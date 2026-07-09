<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308102000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add layout switcher toggle to vehicle frontend settings.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings ADD layout_switcher_enabled TINYINT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings DROP layout_switcher_enabled');
    }
}

