<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add recipient email settings to website widgets';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE website_widget_config ADD recipient_email_mode VARCHAR(20) NOT NULL DEFAULT 'customer', ADD custom_recipient_email VARCHAR(180) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config DROP recipient_email_mode, DROP custom_recipient_email');
    }
}
