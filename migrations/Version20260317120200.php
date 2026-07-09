<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317120200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create AI DALLE image table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ai_dalle_image (
            id INT AUTO_INCREMENT NOT NULL,
            project_id INT DEFAULT NULL,
            prompt LONGTEXT NOT NULL,
            revised_prompt LONGTEXT DEFAULT NULL,
            model VARCHAR(255) NOT NULL,
            size VARCHAR(50) NOT NULL,
            quality VARCHAR(50) DEFAULT NULL,
            style VARCHAR(255) DEFAULT NULL,
            response_format VARCHAR(50) NOT NULL,
            image_count INT DEFAULT 1,
            aspect_ratio VARCHAR(50) DEFAULT "square",
            image_path VARCHAR(255) DEFAULT NULL,
            image_url VARCHAR(2048) DEFAULT NULL,
            image_paths JSON DEFAULT NULL,
            image_urls JSON DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            error LONGTEXT DEFAULT NULL,
            created_at INT NOT NULL,
            updated_at INT NOT NULL,
            PRIMARY KEY (id),
            INDEX IDX_PROJECT_ID (project_id),
            INDEX IDX_CREATED_AT (created_at)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_dalle_image');
    }
}
