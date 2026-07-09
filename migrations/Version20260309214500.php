<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309214500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds unique token_identifier to api_token for dual-factor API token validation.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE api_token ADD token_identifier VARCHAR(64) DEFAULT NULL');
        $this->addSql('UPDATE api_token SET token_identifier = LOWER(REPLACE(UUID(), "-", "")) WHERE token_identifier IS NULL OR token_identifier = ""');
        $this->addSql('ALTER TABLE api_token MODIFY token_identifier VARCHAR(64) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7BA2F5EBB3BC57DA ON api_token (token_identifier)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_7BA2F5EBB3BC57DA ON api_token');
        $this->addSql('ALTER TABLE api_token DROP token_identifier');
    }
}

