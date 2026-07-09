<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create AI chat session table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ai_chat_session (
            id INT AUTO_INCREMENT NOT NULL,
            project_id INT DEFAULT NULL,
            title VARCHAR(255) DEFAULT NULL,
            messages LONGTEXT NOT NULL,
            created_at INT NOT NULL,
            updated_at INT NOT NULL,
            PRIMARY KEY (id),
            INDEX IDX_PROJECT_ID (project_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_chat_session');
    }
}
