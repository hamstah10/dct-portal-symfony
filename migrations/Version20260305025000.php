<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305025000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fuegt preisstaffel_id zu abo hinzu fuer Paket+Laufzeit Auswahl.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo ADD preisstaffel_id INT DEFAULT NULL, ADD INDEX IDX_3C920DE9544BFFB1 (preisstaffel_id)');
        $this->addSql('ALTER TABLE abo ADD CONSTRAINT FK_3C920DE9544BFFB1 FOREIGN KEY (preisstaffel_id) REFERENCES abo_auswahl_preisstaffel (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo DROP FOREIGN KEY FK_3C920DE9544BFFB1');
        $this->addSql('DROP INDEX IDX_3C920DE9544BFFB1 ON abo');
        $this->addSql('ALTER TABLE abo DROP preisstaffel_id');
    }
}
