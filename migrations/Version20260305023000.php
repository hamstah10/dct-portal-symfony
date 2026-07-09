<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305023000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ergaenzt Preis Einrichtung in der Abo-Auswahl Preisstaffel.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo_auswahl_preisstaffel ADD preis_einrichtung NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo_auswahl_preisstaffel DROP preis_einrichtung');
    }
}
