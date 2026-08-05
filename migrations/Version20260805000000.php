<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260805000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create admin_issue and admin_issue_comment tables for issue tracking system.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE admin_issue (
            id INT AUTO_INCREMENT NOT NULL,
            assigned_to_id INT DEFAULT NULL,
            created_by_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            category VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL,
            priority VARCHAR(50) NOT NULL,
            labels JSON NOT NULL,
            page_url LONGTEXT,
            page_title LONGTEXT,
            selector LONGTEXT,
            entity_type VARCHAR(100),
            entity_id INT,
            entity_label VARCHAR(255),
            stage_id INT,
            field VARCHAR(100),
            current_value LONGTEXT,
            screenshot_data_url LONGTEXT,
            audio_url VARCHAR(255),
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME COMMENT \'(DC2Type:datetime_immutable)\',
            resolved_at DATETIME COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_admin_issue_status (status),
            INDEX idx_admin_issue_created_at (created_at),
            INDEX IDX_ADMIN_ISSUE_ASSIGNED (assigned_to_id),
            INDEX IDX_ADMIN_ISSUE_CREATED_BY (created_by_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $this->addSql('CREATE TABLE admin_issue_comment (
            id INT AUTO_INCREMENT NOT NULL,
            issue_id INT NOT NULL,
            author_id INT NOT NULL,
            text LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_admin_issue_comment_issue_id (issue_id),
            INDEX IDX_ADMIN_ISSUE_COMMENT_AUTHOR (author_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $this->addSql('ALTER TABLE admin_issue ADD CONSTRAINT FK_ADMIN_ISSUE_ASSIGNED_TO FOREIGN KEY (assigned_to_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE admin_issue ADD CONSTRAINT FK_ADMIN_ISSUE_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE admin_issue_comment ADD CONSTRAINT FK_ADMIN_ISSUE_COMMENT_ISSUE FOREIGN KEY (issue_id) REFERENCES admin_issue (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_issue_comment ADD CONSTRAINT FK_ADMIN_ISSUE_COMMENT_AUTHOR FOREIGN KEY (author_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE admin_issue_comment DROP FOREIGN KEY FK_ADMIN_ISSUE_COMMENT_ISSUE');
        $this->addSql('ALTER TABLE admin_issue_comment DROP FOREIGN KEY FK_ADMIN_ISSUE_COMMENT_AUTHOR');
        $this->addSql('ALTER TABLE admin_issue DROP FOREIGN KEY FK_ADMIN_ISSUE_ASSIGNED_TO');
        $this->addSql('ALTER TABLE admin_issue DROP FOREIGN KEY FK_ADMIN_ISSUE_CREATED_BY');
        $this->addSql('ALTER TABLE admin_issue DROP FOREIGN KEY FK_ADMIN_ISSUE_VEHICLE');
        $this->addSql('ALTER TABLE admin_issue DROP FOREIGN KEY FK_ADMIN_ISSUE_ENGINE');
        $this->addSql('DROP TABLE admin_issue_comment');
        $this->addSql('DROP TABLE admin_issue');
    }
}
