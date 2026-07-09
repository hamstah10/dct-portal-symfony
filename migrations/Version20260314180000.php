<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260314180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add AI projects, project knowledge base, project chat/image scoping, and project agent runs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE ai_project (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, system_prompt LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE ai_project_resource (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, media_asset_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, file_path VARCHAR(512) DEFAULT NULL, media_type VARCHAR(30) NOT NULL, mime_type VARCHAR(255) DEFAULT NULL, file_size INT DEFAULT NULL, indexing_status VARCHAR(20) NOT NULL, extracted_text LONGTEXT DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, last_indexed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_AI_PROJECT_RESOURCE_PROJECT (project_id), INDEX IDX_AI_PROJECT_RESOURCE_MEDIA (media_asset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE ai_project_vector_document (id VARCHAR(191) NOT NULL, project_id INT NOT NULL, resource_id INT DEFAULT NULL, content LONGTEXT DEFAULT NULL, vector_data LONGTEXT NOT NULL, metadata LONGTEXT DEFAULT NULL, created_at INT NOT NULL, INDEX IDX_AI_PROJECT_VECTOR_PROJECT (project_id), INDEX IDX_AI_PROJECT_VECTOR_RESOURCE (resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE ai_project_agent_run (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, system_prompt LONGTEXT DEFAULT NULL, prompt LONGTEXT NOT NULL, response LONGTEXT DEFAULT NULL, metadata LONGTEXT DEFAULT NULL, error LONGTEXT DEFAULT NULL, created_at INT NOT NULL, updated_at INT NOT NULL, INDEX IDX_AI_PROJECT_AGENT_PROJECT (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE ai_chat_session ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_chat_session ADD INDEX IDX_AI_CHAT_SESSION_PROJECT (project_id)');
        $this->addSql('ALTER TABLE ai_dalle_image ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_dalle_image ADD INDEX IDX_AI_DALLE_IMAGE_PROJECT (project_id)');
        $this->addSql('ALTER TABLE ai_project_resource ADD CONSTRAINT FK_AI_PROJECT_RESOURCE_PROJECT FOREIGN KEY (project_id) REFERENCES ai_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_project_resource ADD CONSTRAINT FK_AI_PROJECT_RESOURCE_MEDIA FOREIGN KEY (media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ai_project_vector_document ADD CONSTRAINT FK_AI_PROJECT_VECTOR_PROJECT FOREIGN KEY (project_id) REFERENCES ai_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_project_vector_document ADD CONSTRAINT FK_AI_PROJECT_VECTOR_RESOURCE FOREIGN KEY (resource_id) REFERENCES ai_project_resource (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_project_agent_run ADD CONSTRAINT FK_AI_PROJECT_AGENT_PROJECT FOREIGN KEY (project_id) REFERENCES ai_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_chat_session ADD CONSTRAINT FK_AI_CHAT_SESSION_PROJECT FOREIGN KEY (project_id) REFERENCES ai_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_dalle_image ADD CONSTRAINT FK_AI_DALLE_IMAGE_PROJECT FOREIGN KEY (project_id) REFERENCES ai_project (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_dalle_image DROP FOREIGN KEY FK_AI_DALLE_IMAGE_PROJECT');
        $this->addSql('ALTER TABLE ai_chat_session DROP FOREIGN KEY FK_AI_CHAT_SESSION_PROJECT');
        $this->addSql('ALTER TABLE ai_project_agent_run DROP FOREIGN KEY FK_AI_PROJECT_AGENT_PROJECT');
        $this->addSql('ALTER TABLE ai_project_vector_document DROP FOREIGN KEY FK_AI_PROJECT_VECTOR_PROJECT');
        $this->addSql('ALTER TABLE ai_project_vector_document DROP FOREIGN KEY FK_AI_PROJECT_VECTOR_RESOURCE');
        $this->addSql('ALTER TABLE ai_project_resource DROP FOREIGN KEY FK_AI_PROJECT_RESOURCE_PROJECT');
        $this->addSql('ALTER TABLE ai_project_resource DROP FOREIGN KEY FK_AI_PROJECT_RESOURCE_MEDIA');
        $this->addSql('ALTER TABLE ai_dalle_image DROP INDEX IDX_AI_DALLE_IMAGE_PROJECT');
        $this->addSql('ALTER TABLE ai_dalle_image DROP project_id');
        $this->addSql('ALTER TABLE ai_chat_session DROP INDEX IDX_AI_CHAT_SESSION_PROJECT');
        $this->addSql('ALTER TABLE ai_chat_session DROP project_id');
        $this->addSql('DROP TABLE ai_project_agent_run');
        $this->addSql('DROP TABLE ai_project_vector_document');
        $this->addSql('DROP TABLE ai_project_resource');
        $this->addSql('DROP TABLE ai_project');
    }
}
