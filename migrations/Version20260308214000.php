<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308214000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds full HTML, parsed JSON data and screenshot path to website sniffer pages.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_sniffer_page ADD html_content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE website_sniffer_page ADD parsed_data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE website_sniffer_page ADD screenshot_path VARCHAR(1024) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_sniffer_page DROP COLUMN screenshot_path');
        $this->addSql('ALTER TABLE website_sniffer_page DROP COLUMN parsed_data');
        $this->addSql('ALTER TABLE website_sniffer_page DROP COLUMN html_content');
    }
}
