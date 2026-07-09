<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317120100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create AI Firecrawl run table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ai_firecrawl_run (
            id INT AUTO_INCREMENT NOT NULL,
            action VARCHAR(255) NOT NULL,
            url LONGTEXT NOT NULL,
            instruction LONGTEXT DEFAULT NULL,
            model VARCHAR(255) NOT NULL,
            crawl_depth INT DEFAULT 0,
            crawl_limit INT DEFAULT 0,
            response LONGTEXT DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            map_links JSON DEFAULT NULL,
            error LONGTEXT DEFAULT NULL,
            status VARCHAR(50) NOT NULL,
            progress_label VARCHAR(255) DEFAULT NULL,
            progress_current INT DEFAULT 0,
            progress_max INT DEFAULT 4,
            started_at INT DEFAULT NULL,
            finished_at INT DEFAULT NULL,
            created_at INT NOT NULL,
            updated_at INT NOT NULL,
            PRIMARY KEY (id),
            INDEX IDX_STATUS (status),
            INDEX IDX_CREATED_AT (created_at)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_firecrawl_run');
    }
}
