<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626032000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add watermark preset and area config tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE tfd_watermark_preset (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            text VARCHAR(255) DEFAULT NULL,
            position VARCHAR(30) NOT NULL DEFAULT 'bottom-right',
            opacity NUMERIC(3,2) NOT NULL DEFAULT '0.50',
            font_size INT DEFAULT NULL,
            text_color VARCHAR(30) DEFAULT '#ffffff',
            image_width INT DEFAULT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE tfd_watermark_area_config (
            id INT AUTO_INCREMENT NOT NULL,
            preset_id INT DEFAULT NULL,
            area VARCHAR(60) NOT NULL,
            enabled TINYINT(1) NOT NULL DEFAULT 0,
            UNIQUE INDEX uniq_area (area),
            INDEX IDX_watermark_preset (preset_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_watermark_area_preset FOREIGN KEY (preset_id) REFERENCES tfd_watermark_preset (id) ON DELETE SET NULL
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("INSERT INTO tfd_watermark_area_config (area, enabled) VALUES
            ('vehicle_stage', 0),
            ('ecu_hint', 0),
            ('vehicle_engine', 0)
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tfd_watermark_area_config');
        $this->addSql('DROP TABLE tfd_watermark_preset');
    }
}
