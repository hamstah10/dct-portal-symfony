<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308203000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds support ticket tables (tickets + messages) for the CRM.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE support_ticket (
    id INT AUTO_INCREMENT NOT NULL,
    firma_id INT NOT NULL,
    kunde_id INT DEFAULT NULL,
    subject VARCHAR(255) NOT NULL,
    status VARCHAR(30) NOT NULL,
    priority VARCHAR(20) NOT NULL,
    message_count INT NOT NULL DEFAULT 0,
    last_message_preview VARCHAR(255) DEFAULT NULL,
    last_message_at DATETIME DEFAULT NULL,
    closed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX IDX_SUPPORT_TICKET_FIRMA (firma_id),
    INDEX IDX_SUPPORT_TICKET_KUNDE (kunde_id),
    INDEX IDX_SUPPORT_TICKET_STATUS (status),
    INDEX IDX_SUPPORT_TICKET_UPDATED_AT (updated_at),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE support_ticket
    ADD CONSTRAINT FK_SUPPORT_TICKET_FIRMA FOREIGN KEY (firma_id) REFERENCES firma (id) ON DELETE CASCADE
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE support_ticket
    ADD CONSTRAINT FK_SUPPORT_TICKET_KUNDE FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE SET NULL
SQL);

        $this->addSql(<<<'SQL'
CREATE TABLE support_ticket_message (
    id INT AUTO_INCREMENT NOT NULL,
    ticket_id INT NOT NULL,
    kunde_id INT DEFAULT NULL,
    sender_type VARCHAR(20) NOT NULL,
    sender_name VARCHAR(120) NOT NULL,
    message LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX IDX_SUPPORT_TICKET_MESSAGE_TICKET (ticket_id),
    INDEX IDX_SUPPORT_TICKET_MESSAGE_KUNDE (kunde_id),
    INDEX IDX_SUPPORT_TICKET_MESSAGE_CREATED_AT (created_at),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE support_ticket_message
    ADD CONSTRAINT FK_SUPPORT_TICKET_MESSAGE_TICKET FOREIGN KEY (ticket_id) REFERENCES support_ticket (id) ON DELETE CASCADE
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE support_ticket_message
    ADD CONSTRAINT FK_SUPPORT_TICKET_MESSAGE_KUNDE FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE SET NULL
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE support_ticket_message DROP FOREIGN KEY FK_SUPPORT_TICKET_MESSAGE_TICKET');
        $this->addSql('ALTER TABLE support_ticket_message DROP FOREIGN KEY FK_SUPPORT_TICKET_MESSAGE_KUNDE');
        $this->addSql('ALTER TABLE support_ticket DROP FOREIGN KEY FK_SUPPORT_TICKET_FIRMA');
        $this->addSql('ALTER TABLE support_ticket DROP FOREIGN KEY FK_SUPPORT_TICKET_KUNDE');
        $this->addSql('DROP TABLE support_ticket_message');
        $this->addSql('DROP TABLE support_ticket');
    }
}

