<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Synonyme für Marken-Suche (z.B. "vw" -> Volkswagen) auf Brand und Brand-Master.
 */
final class Version20260713100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'synonyms-Spalte auf tx_chiptuningdatabase_vehiclebrand und tx_chiptuningdatabase_brand_master';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD synonyms LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master ADD synonyms LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP COLUMN synonyms');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master DROP COLUMN synonyms');
    }
}
