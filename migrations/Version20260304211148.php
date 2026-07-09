<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304211148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Ensure all existing customers are assigned to a company before enforcing NOT NULL.
        $this->addSql("INSERT INTO firma (name, aktiv, created_at) SELECT 'Standard Firma', 1, NOW() WHERE NOT EXISTS (SELECT 1 FROM firma)");
        $this->addSql('UPDATE kunde SET firma_id = (SELECT id FROM firma ORDER BY id ASC LIMIT 1) WHERE firma_id IS NULL');
        $this->addSql('ALTER TABLE kunde CHANGE firma_id firma_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kunde CHANGE firma_id firma_id INT DEFAULT NULL');
    }
}
