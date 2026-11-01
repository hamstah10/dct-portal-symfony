<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261030191000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restore brand_assistant_qa table (missing although Version20261024130000 is marked as migrated)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS brand_assistant_qa (id INT AUTO_INCREMENT NOT NULL, question LONGTEXT NOT NULL, answer LONGTEXT NOT NULL, model VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_BRAND_ASSISTANT_QA_CREATED (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
    }
}
