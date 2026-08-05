<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305195136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        try {
            if ($this->columnExists('zugangsdaten', 'firma_id')) {
                $this->addSql('ALTER TABLE zugangsdaten CHANGE firma_id firma_id INT DEFAULT NULL');
                $this->addSql('ALTER TABLE zugangsdaten ADD CONSTRAINT FK_4A6836F2505AEC11 FOREIGN KEY (firma_id) REFERENCES firma (id)');
                $this->addSql('CREATE INDEX IDX_4A6836F2505AEC11 ON zugangsdaten (firma_id)');
            }
        } catch (\Exception) {
            // Table or column doesn't exist, skip
        }
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        try {
            if ($this->columnExists('zugangsdaten', 'firma_id')) {
                $this->addSql('ALTER TABLE zugangsdaten DROP FOREIGN KEY FK_4A6836F2505AEC11');
                $this->addSql('DROP INDEX IDX_4A6836F2505AEC11 ON zugangsdaten');
                $this->addSql('ALTER TABLE zugangsdaten CHANGE firma_id firma_id INT NOT NULL');
            }
        } catch (\Exception) {
            // Table or column doesn't exist, skip
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            $result = $this->connection->executeQuery(
                "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
                [$table, $column]
            )->fetchAssociative();
            return (bool) $result;
        } catch (\Exception) {
            return false;
        }
    }
}
