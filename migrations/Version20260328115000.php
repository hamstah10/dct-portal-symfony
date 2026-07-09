<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260328115000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add data_font column to the portal table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal ADD data_font VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal DROP data_font');
    }
}
