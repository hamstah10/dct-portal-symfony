<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add open dropdown background color to website widget configs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE website_widget_config ADD select_open_background_color VARCHAR(20) NOT NULL DEFAULT '#f3f4f6'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config DROP select_open_background_color');
    }
}
