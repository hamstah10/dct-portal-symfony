<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309193500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Expands abo_auswahl.beschreibung to LONGTEXT for long CKEditor content.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo_auswahl MODIFY beschreibung LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo_auswahl MODIFY beschreibung VARCHAR(255) DEFAULT NULL');
    }
}
