<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304232200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create abo_auswahl table and add optional relation from abo to abo_auswahl.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE abo_auswahl (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, beschreibung VARCHAR(255) DEFAULT NULL, aktiv TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE abo ADD abo_auswahl_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE abo ADD CONSTRAINT FK_3C920DE95D69D08F FOREIGN KEY (abo_auswahl_id) REFERENCES abo_auswahl (id)');
        $this->addSql('CREATE INDEX IDX_3C920DE95D69D08F ON abo (abo_auswahl_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo DROP FOREIGN KEY FK_3C920DE95D69D08F');
        $this->addSql('DROP INDEX IDX_3C920DE95D69D08F ON abo');
        $this->addSql('ALTER TABLE abo DROP abo_auswahl_id');
        $this->addSql('DROP TABLE abo_auswahl');
    }
}
