<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds finished file upload metadata to auftrag entries.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
ALTER TABLE auftrag
    ADD finished_file_path VARCHAR(255) DEFAULT NULL,
    ADD finished_file_name VARCHAR(255) DEFAULT NULL,
    ADD finished_file_size INT DEFAULT NULL,
    ADD finished_file_uploaded_at DATETIME DEFAULT NULL
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
ALTER TABLE auftrag
    DROP finished_file_path,
    DROP finished_file_name,
    DROP finished_file_size,
    DROP finished_file_uploaded_at
SQL);
    }
}

