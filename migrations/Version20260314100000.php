<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260314100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create AI Firecrawl run storage table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ai_firecrawl_run (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(16) NOT NULL, url TEXT NOT NULL, instruction LONGTEXT NOT NULL, model VARCHAR(100) NOT NULL, crawl_depth INT NOT NULL, crawl_limit INT NOT NULL, response LONGTEXT DEFAULT NULL, metadata LONGTEXT DEFAULT NULL, map_links LONGTEXT DEFAULT NULL, error LONGTEXT DEFAULT NULL, created_at INT NOT NULL, updated_at INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_firecrawl_run');
    }
}
