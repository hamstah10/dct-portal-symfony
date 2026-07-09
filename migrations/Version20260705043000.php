<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705043000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add mobile_api_token and user_push_token tables for the mobile app API.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mobile_api_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type VARCHAR(20) NOT NULL, token_hash VARCHAR(64) NOT NULL, session_id VARCHAR(64) NOT NULL, device_name VARCHAR(120) DEFAULT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME DEFAULT NULL, revoked_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F7BCBEB4B3BC57DA (token_hash), INDEX idx_mobile_api_token_expires_at (expires_at), INDEX idx_mobile_api_token_session (user_id, session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE mobile_api_token ADD CONSTRAINT FK_F7BCBEB4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');

        $this->addSql('CREATE TABLE user_push_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME DEFAULT NULL, UNIQUE INDEX uniq_user_push_token_token (token), INDEX IDX_F6DBD56DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_push_token ADD CONSTRAINT FK_F6DBD56DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mobile_api_token DROP FOREIGN KEY FK_F7BCBEB4A76ED395');
        $this->addSql('DROP TABLE mobile_api_token');

        $this->addSql('ALTER TABLE user_push_token DROP FOREIGN KEY FK_F6DBD56DA76ED395');
        $this->addSql('DROP TABLE user_push_token');
    }
}
