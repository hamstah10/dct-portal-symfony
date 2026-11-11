<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261111080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Partner Wizard: Schalter „Texte per API bearbeiten" pro Client';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_client ADD text_api_enabled TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_client DROP text_api_enabled');
    }
}
