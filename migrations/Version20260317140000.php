<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create AI PDF analysis table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ai_pdf_analysis (
            id INT AUTO_INCREMENT NOT NULL,
            file_path VARCHAR(255) DEFAULT NULL,
            model VARCHAR(255) NOT NULL,
            prompt LONGTEXT NOT NULL,
            response LONGTEXT DEFAULT NULL,
            error LONGTEXT DEFAULT NULL,
            created_at INT NOT NULL,
            updated_at INT NOT NULL,
            PRIMARY KEY (id),
            INDEX IDX_CREATED_AT (created_at)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_pdf_analysis');
    }
}
