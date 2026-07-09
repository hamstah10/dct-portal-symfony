<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304235026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fuegt Preisstaffeln fuer Abo-Auswahl hinzu (Laufzeit, monatlicher und jaehrlicher Preis).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE abo_auswahl_preisstaffel (id INT AUTO_INCREMENT NOT NULL, laufzeit_monate INT NOT NULL, preis_monatlich NUMERIC(10, 2) DEFAULT NULL, preis_jaehrlich NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME NOT NULL, abo_auswahl_id INT NOT NULL, INDEX IDX_4821337125D87FE (abo_auswahl_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE abo_auswahl_preisstaffel ADD CONSTRAINT FK_4821337125D87FE FOREIGN KEY (abo_auswahl_id) REFERENCES abo_auswahl (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo_auswahl_preisstaffel DROP FOREIGN KEY FK_4821337125D87FE');
        $this->addSql('DROP TABLE abo_auswahl_preisstaffel');
    }
}
