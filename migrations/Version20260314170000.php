<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260314170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add async status and progress tracking to AI Firecrawl runs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE ai_firecrawl_run ADD status VARCHAR(16) NOT NULL DEFAULT 'success', ADD progress_label VARCHAR(255) DEFAULT NULL, ADD progress_current INT NOT NULL DEFAULT 4, ADD progress_max INT NOT NULL DEFAULT 4, ADD started_at INT DEFAULT NULL, ADD finished_at INT DEFAULT NULL");
        $this->addSql("UPDATE ai_firecrawl_run SET status = 'failed', progress_label = 'Fehlgeschlagen', progress_current = progress_max, started_at = created_at, finished_at = updated_at WHERE error IS NOT NULL AND TRIM(error) <> ''");
        $this->addSql("UPDATE ai_firecrawl_run SET status = 'success', progress_label = 'Abgeschlossen', progress_current = progress_max, started_at = created_at, finished_at = updated_at WHERE error IS NULL OR TRIM(error) = ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_firecrawl_run DROP status, DROP progress_label, DROP progress_current, DROP progress_max, DROP started_at, DROP finished_at');
    }
}
