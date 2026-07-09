<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326224500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add menu and content background colors to portal';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE portal ADD menu_background_color VARCHAR(7) NOT NULL DEFAULT '#111111', ADD content_background_color VARCHAR(7) NOT NULL DEFAULT '#050505'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal DROP menu_background_color, DROP content_background_color');
    }
}
