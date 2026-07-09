<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308093000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add top menu switch to vehicle brands.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD show_in_top_menu TINYINT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP show_in_top_menu');
    }
}

