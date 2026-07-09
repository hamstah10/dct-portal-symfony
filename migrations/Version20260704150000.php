<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tool_provider, tool_news_source and tool_news_item tables for the ECU Intelligence tool news scraper (MVP 1).';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE tool_provider (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(150) NOT NULL,
                slug VARCHAR(150) NOT NULL,
                website_url VARCHAR(255) DEFAULT NULL,
                is_active TINYINT(1) NOT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                UNIQUE INDEX UNIQ_TOOL_PROVIDER_SLUG (slug),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE tool_news_source (
                id INT AUTO_INCREMENT NOT NULL,
                provider_id INT NOT NULL,
                name VARCHAR(150) NOT NULL,
                url VARCHAR(500) NOT NULL,
                type VARCHAR(20) NOT NULL,
                spider_name VARCHAR(150) NOT NULL,
                is_active TINYINT(1) NOT NULL,
                last_run_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                last_success_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                last_error LONGTEXT DEFAULT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX IDX_TOOL_NEWS_SOURCE_PROVIDER (provider_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE tool_news_item (
                id INT AUTO_INCREMENT NOT NULL,
                provider_id INT NOT NULL,
                source_id INT DEFAULT NULL,
                title VARCHAR(500) NOT NULL,
                url VARCHAR(1000) NOT NULL,
                excerpt LONGTEXT DEFAULT NULL,
                content LONGTEXT DEFAULT NULL,
                image_url VARCHAR(1000) DEFAULT NULL,
                published_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                content_hash VARCHAR(64) DEFAULT NULL,
                status VARCHAR(20) NOT NULL,
                raw_data JSON DEFAULT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                UNIQUE INDEX UNIQ_TOOL_NEWS_ITEM_URL (url(255)),
                UNIQUE INDEX UNIQ_TOOL_NEWS_ITEM_CONTENT_HASH (content_hash),
                INDEX IDX_TOOL_NEWS_ITEM_PROVIDER (provider_id),
                INDEX IDX_TOOL_NEWS_ITEM_SOURCE (source_id),
                INDEX IDX_TOOL_NEWS_ITEM_STATUS (status),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $this->addSql('ALTER TABLE tool_news_source ADD CONSTRAINT FK_TOOL_NEWS_SOURCE_PROVIDER FOREIGN KEY (provider_id) REFERENCES tool_provider (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tool_news_item ADD CONSTRAINT FK_TOOL_NEWS_ITEM_PROVIDER FOREIGN KEY (provider_id) REFERENCES tool_provider (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tool_news_item ADD CONSTRAINT FK_TOOL_NEWS_ITEM_SOURCE FOREIGN KEY (source_id) REFERENCES tool_news_source (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tool_news_item DROP FOREIGN KEY FK_TOOL_NEWS_ITEM_SOURCE');
        $this->addSql('ALTER TABLE tool_news_item DROP FOREIGN KEY FK_TOOL_NEWS_ITEM_PROVIDER');
        $this->addSql('ALTER TABLE tool_news_source DROP FOREIGN KEY FK_TOOL_NEWS_SOURCE_PROVIDER');
        $this->addSql('DROP TABLE tool_news_item');
        $this->addSql('DROP TABLE tool_news_source');
        $this->addSql('DROP TABLE tool_provider');
    }
}
