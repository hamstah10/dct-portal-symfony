<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110064833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Partner Wizard: API-Token pro Client (Hash, Hinweis, Erstellt, Pflicht-Flag)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_client ADD api_token_hash VARCHAR(64) DEFAULT NULL, ADD api_token_hint VARCHAR(16) DEFAULT NULL, ADD api_token_created_at DATETIME DEFAULT NULL, ADD api_token_required TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_client DROP api_token_hash, DROP api_token_hint, DROP api_token_created_at, DROP api_token_required');
    }
}
