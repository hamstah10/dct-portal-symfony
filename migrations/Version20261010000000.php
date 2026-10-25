<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261010000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create superadmin_project table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE superadmin_project (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(2000) NOT NULL, description LONGTEXT, active TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE superadmin_project');
    }
}
