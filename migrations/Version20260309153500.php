<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309153500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds email account management with IMAP/SMTP settings.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE email_konto (
    id INT AUTO_INCREMENT NOT NULL,
    firma_id INT DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    email_address VARCHAR(190) NOT NULL,
    from_name VARCHAR(150) DEFAULT NULL,
    imap_host VARCHAR(255) NOT NULL,
    imap_port INT NOT NULL,
    imap_encryption VARCHAR(20) NOT NULL,
    imap_username VARCHAR(255) DEFAULT NULL,
    imap_password LONGTEXT DEFAULT NULL,
    imap_folder VARCHAR(120) NOT NULL,
    smtp_host VARCHAR(255) NOT NULL,
    smtp_port INT NOT NULL,
    smtp_encryption VARCHAR(20) NOT NULL,
    smtp_username VARCHAR(255) DEFAULT NULL,
    smtp_password LONGTEXT DEFAULT NULL,
    active TINYINT(1) NOT NULL,
    fetch_enabled TINYINT(1) NOT NULL,
    send_enabled TINYINT(1) NOT NULL,
    sorting INT NOT NULL,
    notes LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX IDX_EMAIL_KONTO_FIRMA (firma_id),
    INDEX IDX_EMAIL_KONTO_ACTIVE (active),
    INDEX IDX_EMAIL_KONTO_FETCH_ENABLED (fetch_enabled),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE email_konto
    ADD CONSTRAINT FK_EMAIL_KONTO_FIRMA FOREIGN KEY (firma_id) REFERENCES firma (id) ON DELETE SET NULL
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_konto DROP FOREIGN KEY FK_EMAIL_KONTO_FIRMA');
        $this->addSql('DROP TABLE email_konto');
    }
}
