<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds per-layout frontend settings toggles for the vehicle database (autorotate, prices, logo, stage filtering).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings
    ADD performance_glb_autorotate TINYINT(1) NOT NULL DEFAULT 0,
    ADD performance_show_prices TINYINT(1) NOT NULL DEFAULT 1,
    ADD performance_show_logo TINYINT(1) NOT NULL DEFAULT 1,
    ADD performance_show_only_stage1 TINYINT(1) NOT NULL DEFAULT 0,
    ADD minimal_glb_autorotate TINYINT(1) NOT NULL DEFAULT 0,
    ADD minimal_show_prices TINYINT(1) NOT NULL DEFAULT 1,
    ADD minimal_show_logo TINYINT(1) NOT NULL DEFAULT 1,
    ADD minimal_show_only_stage1 TINYINT(1) NOT NULL DEFAULT 0,
    ADD contrast_glb_autorotate TINYINT(1) NOT NULL DEFAULT 0,
    ADD contrast_show_prices TINYINT(1) NOT NULL DEFAULT 1,
    ADD contrast_show_logo TINYINT(1) NOT NULL DEFAULT 0,
    ADD contrast_show_only_stage1 TINYINT(1) NOT NULL DEFAULT 0,
    ADD layout3_glb_autorotate TINYINT(1) NOT NULL DEFAULT 0,
    ADD layout3_show_prices TINYINT(1) NOT NULL DEFAULT 1,
    ADD layout3_show_logo TINYINT(1) NOT NULL DEFAULT 0,
    ADD layout3_show_only_stage1 TINYINT(1) NOT NULL DEFAULT 0,
    ADD layout4_glb_autorotate TINYINT(1) NOT NULL DEFAULT 0,
    ADD layout4_show_prices TINYINT(1) NOT NULL DEFAULT 1,
    ADD layout4_show_logo TINYINT(1) NOT NULL DEFAULT 0,
    ADD layout4_show_only_stage1 TINYINT(1) NOT NULL DEFAULT 0
SQL);

        $this->addSql(<<<'SQL'
UPDATE tx_chiptuningdatabase_vehicle_frontend_settings
SET
    performance_show_prices = show_prices,
    minimal_show_prices = show_prices,
    contrast_show_prices = show_prices,
    layout3_show_prices = show_prices,
    layout4_show_prices = show_prices,
    performance_show_logo = 1,
    minimal_show_logo = 1,
    contrast_show_logo = 0,
    layout3_show_logo = 0,
    layout4_show_logo = 0,
    performance_show_only_stage1 = show_only_stage1,
    minimal_show_only_stage1 = show_only_stage1,
    contrast_show_only_stage1 = show_only_stage1,
    layout3_show_only_stage1 = show_only_stage1,
    layout4_show_only_stage1 = show_only_stage1
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings
    DROP performance_glb_autorotate,
    DROP performance_show_prices,
    DROP performance_show_logo,
    DROP performance_show_only_stage1,
    DROP minimal_glb_autorotate,
    DROP minimal_show_prices,
    DROP minimal_show_logo,
    DROP minimal_show_only_stage1,
    DROP contrast_glb_autorotate,
    DROP contrast_show_prices,
    DROP contrast_show_logo,
    DROP contrast_show_only_stage1,
    DROP layout3_glb_autorotate,
    DROP layout3_show_prices,
    DROP layout3_show_logo,
    DROP layout3_show_only_stage1,
    DROP layout4_glb_autorotate,
    DROP layout4_show_prices,
    DROP layout4_show_logo,
    DROP layout4_show_only_stage1
SQL);
    }
}
