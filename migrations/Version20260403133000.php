<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403133000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add icon field to ECU option master and backfill from existing ECU option icon sources.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master ADD icon VARCHAR(255) DEFAULT NULL');

        $this->addSql("
            UPDATE tx_chiptuningdatabase_ecu_option_master eom
            LEFT JOIN (
                SELECT
                    eo.master_option AS master_option,
                    MAX(COALESCE(NULLIF(ma.file_path, ''), NULLIF(eo.icon, ''))) AS icon
                FROM tx_chiptuningdatabase_domain_model_ecuoption eo
                LEFT JOIN media_asset ma ON ma.id = eo.icon_media_asset_id
                WHERE eo.master_option IS NOT NULL
                GROUP BY eo.master_option
            ) resolved ON resolved.master_option = eom.uid
            SET eom.icon = resolved.icon
            WHERE (eom.icon IS NULL OR eom.icon = '')
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master DROP icon');
    }
}
