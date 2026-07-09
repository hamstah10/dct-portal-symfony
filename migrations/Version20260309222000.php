<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309222000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds active flag to api_token for enabling/disabling tokens.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE api_token ADD active TINYINT(1) NOT NULL DEFAULT 1');
        $this->addSql('CREATE INDEX IDX_API_TOKEN_ACTIVE ON api_token (active)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_API_TOKEN_ACTIVE ON api_token');
        $this->addSql('ALTER TABLE api_token DROP active');
    }
}
