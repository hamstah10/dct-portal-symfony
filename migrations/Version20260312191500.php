<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312191500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add include_children flag to admin page access rules.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE admin_page_access ADD include_children BOOLEAN NOT NULL DEFAULT TRUE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE admin_page_access DROP include_children');
    }
}
