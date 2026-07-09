<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create auftrag table for incoming CRM order transfers.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE auftrag (id INT AUTO_INCREMENT NOT NULL, kunde_id INT NOT NULL, customer_email VARCHAR(180) NOT NULL, file_name VARCHAR(255) NOT NULL, file_data LONGTEXT NOT NULL, file_size INT NOT NULL, vehicle_data JSON DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_AUFTRAG_KUNDE (kunde_id), INDEX IDX_AUFTRAG_CREATED_AT (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE auftrag ADD CONSTRAINT FK_AUFTRAG_KUNDE FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auftrag DROP FOREIGN KEY FK_AUFTRAG_KUNDE');
        $this->addSql('DROP TABLE auftrag');
    }
}
