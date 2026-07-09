<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260314124500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add preview_paths to AI vehicle OCR runs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_vehicle_ocr_run ADD preview_paths LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_vehicle_ocr_run DROP preview_paths');
    }
}
