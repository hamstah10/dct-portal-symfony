<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304203806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kunde ADD ust_id VARCHAR(50) DEFAULT NULL, ADD steuernummer VARCHAR(50) DEFAULT NULL, ADD strasse VARCHAR(255) DEFAULT NULL, ADD hausnummer VARCHAR(30) DEFAULT NULL, ADD bild VARCHAR(255) DEFAULT NULL, ADD passwort VARCHAR(255) DEFAULT NULL, ADD registrierungsdatum DATE DEFAULT NULL, ADD newsletter TINYINT NOT NULL, CHANGE adresse zahlungsbedingungen LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kunde DROP ust_id, DROP steuernummer, DROP strasse, DROP hausnummer, DROP bild, DROP passwort, DROP registrierungsdatum, DROP newsletter, CHANGE zahlungsbedingungen adresse LONGTEXT DEFAULT NULL');
    }
}
