<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304195201 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abo (id INT AUTO_INCREMENT NOT NULL, leistungen LONGTEXT NOT NULL, start_datum DATETIME NOT NULL, end_datum DATETIME DEFAULT NULL, status VARCHAR(50) NOT NULL, preis NUMERIC(10, 2) DEFAULT NULL, notizen LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, kunde_id INT NOT NULL, software_id INT NOT NULL, INDEX IDX_3C920DE99D4738BC (kunde_id), INDEX IDX_3C920DE9D7452741 (software_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE kunde (id INT AUTO_INCREMENT NOT NULL, kundennummer VARCHAR(50) DEFAULT NULL, firmenname VARCHAR(255) DEFAULT NULL, vorname VARCHAR(120) NOT NULL, nachname VARCHAR(120) NOT NULL, email VARCHAR(180) NOT NULL, telefon VARCHAR(80) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, plz VARCHAR(20) DEFAULT NULL, stadt VARCHAR(120) DEFAULT NULL, land VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_A213CB1C4D741E (kundennummer), UNIQUE INDEX UNIQ_A213CB1E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE software (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, version VARCHAR(120) DEFAULT NULL, beschreibung LONGTEXT DEFAULT NULL, preis NUMERIC(10, 2) DEFAULT NULL, aktiv TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE abo ADD CONSTRAINT FK_3C920DE99D4738BC FOREIGN KEY (kunde_id) REFERENCES kunde (id)');
        $this->addSql('ALTER TABLE abo ADD CONSTRAINT FK_3C920DE9D7452741 FOREIGN KEY (software_id) REFERENCES software (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abo DROP FOREIGN KEY FK_3C920DE99D4738BC');
        $this->addSql('ALTER TABLE abo DROP FOREIGN KEY FK_3C920DE9D7452741');
        $this->addSql('DROP TABLE abo');
        $this->addSql('DROP TABLE kunde');
        $this->addSql('DROP TABLE software');
    }
}
