<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260327170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add heading and body font selection to portals.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal ADD heading_font VARCHAR(255) DEFAULT NULL, ADD body_font VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal DROP heading_font, DROP body_font');
    }
}
