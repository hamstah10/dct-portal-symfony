<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304230500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create api_token table for external application API credentials.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE api_token (id INT AUTO_INCREMENT NOT NULL, application_name VARCHAR(150) NOT NULL, application_link VARCHAR(255) DEFAULT NULL, api_token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_BA9994B77E4AF2A0 (api_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE api_token');
    }
}
