<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260328071112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add updated_at timestamp to the auftrag table for portal ordering';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auftrag ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE auftrag SET updated_at = COALESCE(created_at, NOW())');
        $this->addSql('ALTER TABLE auftrag CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auftrag DROP updated_at');
    }
}
