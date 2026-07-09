<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326232000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add custom design toggle to portal';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal ADD custom_design_enabled TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal DROP custom_design_enabled');
    }
}
