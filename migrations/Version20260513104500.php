<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260513104500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add scraper source namespace to vehicle brands to prevent scraper imports from overwriting each other.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD scraper_source VARCHAR(80) NOT NULL DEFAULT 'legacy' AFTER slug");
        $this->addSql('CREATE INDEX IDX_VEHICLEBRAND_SCRAPER_SOURCE ON tx_chiptuningdatabase_vehiclebrand (scraper_source)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_VEHICLEBRAND_SCRAPER_SOURCE ON tx_chiptuningdatabase_vehiclebrand');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP scraper_source');
    }
}
