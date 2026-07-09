<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260404153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow null values for scraper_run.success so pending jobs can be displayed';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE scraper_run MODIFY success TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE scraper_run MODIFY success TINYINT(1) NOT NULL');
    }
}
