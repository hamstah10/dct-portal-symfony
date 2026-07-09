<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317120300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create AI vehicle OCR run table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ai_vehicle_ocr_run (
            id INT AUTO_INCREMENT NOT NULL,
            source_type VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) DEFAULT NULL,
            mime_type VARCHAR(50) DEFAULT NULL,
            document_path VARCHAR(255) DEFAULT NULL,
            preview_path VARCHAR(255) DEFAULT NULL,
            preview_paths JSON DEFAULT NULL,
            model VARCHAR(255) NOT NULL,
            extracted JSON DEFAULT NULL,
            raw_text LONGTEXT DEFAULT NULL,
            raw_response LONGTEXT DEFAULT NULL,
            criteria JSON DEFAULT NULL,
            results JSON DEFAULT NULL,
            error LONGTEXT DEFAULT NULL,
            created_at INT NOT NULL,
            updated_at INT NOT NULL,
            PRIMARY KEY (id),
            INDEX IDX_CREATED_AT (created_at)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_vehicle_ocr_run');
    }
}
