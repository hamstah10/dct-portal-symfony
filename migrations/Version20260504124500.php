<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504124500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add select background color to website widget configuration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE website_widget_config ADD select_background_color VARCHAR(20) NOT NULL DEFAULT '#ffffff'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config DROP select_background_color');
    }
}
