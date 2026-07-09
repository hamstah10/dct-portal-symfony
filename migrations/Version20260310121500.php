<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310121500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds profile fields (vorname, nachname, adresse, bild, avatar) to user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD vorname VARCHAR(120) DEFAULT NULL, ADD nachname VARCHAR(120) DEFAULT NULL, ADD adresse LONGTEXT DEFAULT NULL, ADD bild VARCHAR(255) DEFAULT NULL, ADD avatar VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP vorname, DROP nachname, DROP adresse, DROP bild, DROP avatar');
    }
}
