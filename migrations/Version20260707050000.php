<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260707050000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add maintenance_mode and credit_price columns to portal for the mobile portal detail screen.';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal ADD maintenance_mode TINYINT(1) NOT NULL DEFAULT 0, ADD credit_price NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal DROP maintenance_mode, DROP credit_price');
    }
}
