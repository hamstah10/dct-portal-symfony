<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309211000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extends api_token with optional customer relation and validity end date.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE api_token ADD kunde_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE api_token ADD gueltig_bis DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EB9395C3F3 FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7BA2F5EB9395C3F3 ON api_token (kunde_id)');
        $this->addSql('CREATE INDEX IDX_API_TOKEN_GUELTIG_BIS ON api_token (gueltig_bis)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EB9395C3F3');
        $this->addSql('DROP INDEX IDX_API_TOKEN_GUELTIG_BIS ON api_token');
        $this->addSql('DROP INDEX IDX_7BA2F5EB9395C3F3 ON api_token');
        $this->addSql('ALTER TABLE api_token DROP kunde_id, DROP gueltig_bis');
    }
}

