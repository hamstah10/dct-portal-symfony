<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305024000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Entfernt Legacy-Spalten laufzeit und preis aus abo_auswahl.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo_auswahl DROP laufzeit, DROP preis');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo_auswahl ADD laufzeit VARCHAR(80) DEFAULT NULL, ADD preis NUMERIC(10, 2) DEFAULT NULL');
    }
}
