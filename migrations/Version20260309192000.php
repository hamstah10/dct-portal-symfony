<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309192000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds processed and total page counters for website sniffer run progress tracking.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_sniffer_run ADD processed_pages INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE website_sniffer_run ADD total_pages INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_sniffer_run DROP COLUMN total_pages');
        $this->addSql('ALTER TABLE website_sniffer_run DROP COLUMN processed_pages');
    }
}
