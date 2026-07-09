<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309161000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds support email messages for inbox/sent/draft handling.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE support_email_message (
    id INT AUTO_INCREMENT NOT NULL,
    email_konto_id INT DEFAULT NULL,
    folder VARCHAR(30) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    from_name VARCHAR(150) DEFAULT NULL,
    from_email VARCHAR(190) DEFAULT NULL,
    to_email LONGTEXT DEFAULT NULL,
    cc_email LONGTEXT DEFAULT NULL,
    bcc_email LONGTEXT DEFAULT NULL,
    body_text LONGTEXT NOT NULL,
    body_html LONGTEXT DEFAULT NULL,
    is_read TINYINT(1) NOT NULL,
    is_starred TINYINT(1) NOT NULL,
    is_draft TINYINT(1) NOT NULL,
    external_message_id VARCHAR(255) DEFAULT NULL,
    in_reply_to_message_id VARCHAR(255) DEFAULT NULL,
    labels JSON DEFAULT NULL,
    received_at DATETIME DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX IDX_SUPPORT_EMAIL_ACCOUNT (email_konto_id),
    INDEX IDX_SUPPORT_EMAIL_FOLDER (folder),
    INDEX IDX_SUPPORT_EMAIL_IS_READ (is_read),
    INDEX IDX_SUPPORT_EMAIL_IS_STARRED (is_starred),
    INDEX IDX_SUPPORT_EMAIL_RECEIVED_AT (received_at),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE support_email_message
    ADD CONSTRAINT FK_SUPPORT_EMAIL_ACCOUNT FOREIGN KEY (email_konto_id) REFERENCES email_konto (id) ON DELETE SET NULL
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE support_email_message DROP FOREIGN KEY FK_SUPPORT_EMAIL_ACCOUNT');
        $this->addSql('DROP TABLE support_email_message');
    }
}
