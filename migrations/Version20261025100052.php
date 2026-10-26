<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261025100052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE knowledge_entry (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, category VARCHAR(100) NOT NULL, tags JSON NOT NULL, active TINYINT(1) NOT NULL, usefulness INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE knowledge_entry_vehicle_engine (knowledge_entry_id INT NOT NULL, vehicle_engine_uid INT NOT NULL, PRIMARY KEY(knowledge_entry_id, vehicle_engine_uid), KEY IDX_F5D8FA74DD8D3B64 (knowledge_entry_id), KEY IDX_F5D8FA74AED06C00 (vehicle_engine_uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE knowledge_entry_vehicle_engine ADD CONSTRAINT FK_F5D8FA74DD8D3B64 FOREIGN KEY (knowledge_entry_id) REFERENCES knowledge_entry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE knowledge_entry_vehicle_engine ADD CONSTRAINT FK_F5D8FA74AED06C00 FOREIGN KEY (vehicle_engine_uid) REFERENCES tx_chiptuningdatabase_vehicleengine (uid) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE knowledge_entry_vehicle_engine DROP FOREIGN KEY FK_F5D8FA74DD8D3B64');
        $this->addSql('ALTER TABLE knowledge_entry_vehicle_engine DROP FOREIGN KEY FK_F5D8FA74AED06C00');
        $this->addSql('DROP TABLE knowledge_entry');
        $this->addSql('DROP TABLE knowledge_entry_vehicle_engine');
    }
}
