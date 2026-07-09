<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306182000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create webhook_endpoint table for outgoing webhook delivery configuration.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE webhook_endpoint (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, target_url VARCHAR(500) NOT NULL, secret VARCHAR(255) DEFAULT NULL, subscribed_events JSON NOT NULL, active TINYINT(1) NOT NULL, timeout_seconds INT NOT NULL, verify_ssl TINYINT(1) NOT NULL, last_delivery_at DATETIME DEFAULT NULL, last_http_status INT DEFAULT NULL, last_error LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE webhook_endpoint');
    }
}
