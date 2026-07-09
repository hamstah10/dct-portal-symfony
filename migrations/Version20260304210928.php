<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304210928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE firma (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, rechtsform VARCHAR(120) DEFAULT NULL, ust_id VARCHAR(50) DEFAULT NULL, steuernummer VARCHAR(50) DEFAULT NULL, strasse VARCHAR(255) DEFAULT NULL, hausnummer VARCHAR(30) DEFAULT NULL, plz VARCHAR(20) DEFAULT NULL, stadt VARCHAR(120) DEFAULT NULL, land VARCHAR(120) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, telefon VARCHAR(80) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, iban VARCHAR(40) DEFAULT NULL, bic VARCHAR(20) DEFAULT NULL, bankname VARCHAR(120) DEFAULT NULL, zahlungsbedingungen LONGTEXT DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, aktiv TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE firma');
    }
}
