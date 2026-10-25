<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261024150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Recreate ai_project_vector_document (dropped by Version20260603023219) for the AI project knowledge store';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE IF NOT EXISTS ai_project_vector_document (
                id VARCHAR(191) NOT NULL,
                project_id INT NOT NULL,
                resource_id INT DEFAULT NULL,
                content LONGTEXT DEFAULT NULL,
                vector_data LONGTEXT NOT NULL,
                metadata LONGTEXT DEFAULT NULL,
                created_at INT NOT NULL,
                INDEX IDX_AI_PROJECT_VECTOR_PROJECT (project_id),
                INDEX IDX_AI_PROJECT_VECTOR_RESOURCE (resource_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql('ALTER TABLE ai_project_vector_document ADD CONSTRAINT FK_AI_PROJECT_VECTOR_PROJECT FOREIGN KEY (project_id) REFERENCES ai_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_project_vector_document ADD CONSTRAINT FK_AI_PROJECT_VECTOR_RESOURCE FOREIGN KEY (resource_id) REFERENCES ai_project_resource (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_project_vector_document');
    }
}
