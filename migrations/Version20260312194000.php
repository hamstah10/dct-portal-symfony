<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312194000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add active flag to users for account locking.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD is_active BOOLEAN NOT NULL DEFAULT TRUE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP is_active');
    }
}
