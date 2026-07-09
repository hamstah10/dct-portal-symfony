<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310131500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds finished_files JSON column for multiple uploads on auftrag.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auftrag ADD finished_files JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auftrag DROP finished_files');
    }
}
