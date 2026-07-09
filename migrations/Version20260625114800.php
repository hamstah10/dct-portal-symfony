<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260625114800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add no1tuningfiles portal tables (upload jobs and password reset tokens)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE no1_portal_upload_job (id INT AUTO_INCREMENT NOT NULL, brand_key VARCHAR(80) NOT NULL, original_filename VARCHAR(255) NOT NULL, stored_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(180) DEFAULT NULL, size_bytes INT NOT NULL, status VARCHAR(40) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, processed_at DATETIME DEFAULT NULL, returned_at DATETIME DEFAULT NULL, kunde_id INT NOT NULL, INDEX IDX_9BE533F89D4738BC (kunde_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE no1_portal_password_reset_token (id INT AUTO_INCREMENT NOT NULL, brand_key VARCHAR(80) NOT NULL, token_hash VARCHAR(128) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, used_at DATETIME DEFAULT NULL, kunde_id INT NOT NULL, INDEX IDX_2F09B2619D4738BC (kunde_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE no1_portal_upload_job ADD CONSTRAINT FK_9BE533F89D4738BC FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE no1_portal_password_reset_token ADD CONSTRAINT FK_2F09B2619D4738BC FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE no1_portal_upload_job DROP FOREIGN KEY FK_9BE533F89D4738BC');
        $this->addSql('ALTER TABLE no1_portal_password_reset_token DROP FOREIGN KEY FK_2F09B2619D4738BC');
        $this->addSql('DROP TABLE no1_portal_upload_job');
        $this->addSql('DROP TABLE no1_portal_password_reset_token');
    }
}
