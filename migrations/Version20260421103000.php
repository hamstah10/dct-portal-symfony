<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260421103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add source column to scraper_import_log and backfill root/output origin';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE scraper_import_log ADD source VARCHAR(32) NOT NULL DEFAULT 'root' AFTER file_path");
        $this->addSql("UPDATE scraper_import_log SET source = 'output' WHERE file_path LIKE '%/scrapy-remote-scraper/data/output/%'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE scraper_import_log DROP source');
    }
}
