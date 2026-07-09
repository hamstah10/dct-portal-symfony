<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260512100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add type_icon_color to website_widget_config.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE website_widget_config ADD type_icon_color VARCHAR(10) NOT NULL DEFAULT 'white'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config DROP type_icon_color');
    }
}
