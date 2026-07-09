<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260401113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add showcase password fields to crm_lead.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE crm_lead ADD showcase_password_hash VARCHAR(255) DEFAULT NULL, ADD showcase_password_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD showcase_password_generated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE crm_lead DROP showcase_password_hash, DROP showcase_password_expires_at, DROP showcase_password_generated_at');
    }
}
