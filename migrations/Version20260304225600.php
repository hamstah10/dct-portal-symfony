<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304225600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add customer_api_token table and password_changed_at field for Kunde.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde ADD password_changed_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE TABLE customer_api_token (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, type VARCHAR(20) NOT NULL, token_hash VARCHAR(64) NOT NULL, session_id VARCHAR(64) NOT NULL, device_name VARCHAR(120) DEFAULT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME DEFAULT NULL, revoked_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_C4C0B9C6667D7EC4 (token_hash), INDEX IDX_C4C0B9C69395C3F3 (customer_id), INDEX idx_customer_api_token_expires_at (expires_at), INDEX idx_customer_api_token_session (customer_id, session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE customer_api_token ADD CONSTRAINT FK_C4C0B9C69395C3F3 FOREIGN KEY (customer_id) REFERENCES kunde (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_api_token DROP FOREIGN KEY FK_C4C0B9C69395C3F3');
        $this->addSql('DROP TABLE customer_api_token');
        $this->addSql('ALTER TABLE kunde DROP password_changed_at');
    }
}
