<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403134500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vehicle type relation to BrandMaster and backfill from existing VehicleBrand rows.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master ADD vehicle_type INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master ADD CONSTRAINT FK_7C45707BA7A0EB7E FOREIGN KEY (vehicle_type) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7C45707BA7A0EB7E ON tx_chiptuningdatabase_brand_master (vehicle_type)');

        $this->addSql("
            UPDATE tx_chiptuningdatabase_brand_master bm
            INNER JOIN (
                SELECT vb.master_brand AS master_brand, MIN(vb.relation_to_vehicletype) AS vehicle_type
                FROM tx_chiptuningdatabase_vehiclebrand vb
                WHERE vb.master_brand IS NOT NULL
                  AND vb.relation_to_vehicletype IS NOT NULL
                GROUP BY vb.master_brand
            ) resolved ON resolved.master_brand = bm.uid
            SET bm.vehicle_type = resolved.vehicle_type
            WHERE bm.vehicle_type IS NULL
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master DROP FOREIGN KEY FK_7C45707BA7A0EB7E');
        $this->addSql('DROP INDEX IDX_7C45707BA7A0EB7E ON tx_chiptuningdatabase_brand_master');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master DROP vehicle_type');
    }
}
