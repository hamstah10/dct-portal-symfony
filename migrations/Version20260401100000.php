<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260401100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create crm_lead table for superadmin lead management.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE crm_lead (id INT AUTO_INCREMENT NOT NULL, firma VARCHAR(255) DEFAULT NULL, vorname VARCHAR(120) DEFAULT NULL, nachname VARCHAR(120) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, telefon VARCHAR(80) DEFAULT NULL, quelle VARCHAR(120) DEFAULT NULL, status VARCHAR(50) NOT NULL, notiz LONGTEXT DEFAULT NULL, showcase_password_hash VARCHAR(255) DEFAULT NULL, showcase_password_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', showcase_password_generated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_crm_lead_status (status), INDEX idx_crm_lead_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE crm_lead');
    }
}
