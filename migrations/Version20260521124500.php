<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521124500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add admin-only chart visibility setting to website widgets';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config ADD show_performance_chart TINYINT(1) NOT NULL DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config DROP show_performance_chart');
    }
}
