<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305022000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create customer_chat_message table for dashboard company chat.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE customer_chat_message (id INT AUTO_INCREMENT NOT NULL, firma_id INT NOT NULL, kunde_id INT DEFAULT NULL, sender_type VARCHAR(20) NOT NULL, sender_name VARCHAR(120) NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_D0D73FE505AEC11 (firma_id), INDEX IDX_D0D73FE9395C3F3 (kunde_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE customer_chat_message ADD CONSTRAINT FK_D0D73FE505AEC11 FOREIGN KEY (firma_id) REFERENCES firma (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer_chat_message ADD CONSTRAINT FK_D0D73FE9395C3F3 FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_chat_message DROP FOREIGN KEY FK_D0D73FE505AEC11');
        $this->addSql('ALTER TABLE customer_chat_message DROP FOREIGN KEY FK_D0D73FE9395C3F3');
        $this->addSql('DROP TABLE customer_chat_message');
    }
}
