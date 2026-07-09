<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318072000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create missing ai_project_agent_run table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ai_project_agent_run (
            id INT AUTO_INCREMENT NOT NULL,
            project_id INT NOT NULL,
            system_prompt LONGTEXT DEFAULT NULL,
            prompt LONGTEXT NOT NULL,
            response LONGTEXT DEFAULT NULL,
            metadata LONGTEXT DEFAULT NULL,
            error LONGTEXT DEFAULT NULL,
            created_at INT NOT NULL,
            updated_at INT NOT NULL,
            INDEX IDX_AI_PROJECT_AGENT_PROJECT (project_id),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_project_agent_run');
    }
}
