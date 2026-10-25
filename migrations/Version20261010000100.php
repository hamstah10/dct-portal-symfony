<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261010000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image_path to superadmin_project table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE superadmin_project ADD image_path VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE superadmin_project DROP image_path');
    }
}
