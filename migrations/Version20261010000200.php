<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261010000200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add login_username and login_password_encrypted to superadmin_project';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE superadmin_project ADD login_username VARCHAR(255) DEFAULT NULL, ADD login_password_encrypted LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE superadmin_project DROP login_username, DROP login_password_encrypted');
    }
}
