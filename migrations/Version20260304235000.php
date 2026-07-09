<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304235000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create template and demo tables for website administration.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE template (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, beschreibung LONGTEXT DEFAULT NULL, aktiv TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE demo (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, beschreibung LONGTEXT DEFAULT NULL, aktiv TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE demo');
        $this->addSql('DROP TABLE template');
    }
}
