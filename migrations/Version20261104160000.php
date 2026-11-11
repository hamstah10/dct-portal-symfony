<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261104160000 extends AbstractMigration
{
    private const MAP = [1 => 100008, 2 => 100009, 3 => 100010, 4 => 100011, 5 => 100012, 6 => 100013, 7 => 100014];

    public function getDescription(): string
    {
        return 'Doppelte Fahrzeugtypen (uid 1-7) auf die neuen (100008-100014) zusammenführen und Slug eindeutig machen';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE tx_chiptuningdatabase_vehicletype n JOIN tx_chiptuningdatabase_vehicletype o ON o.uid = 1 SET n.description = o.description WHERE n.uid = 100008 AND (n.description IS NULL OR n.description = '')");
        $this->addSql("UPDATE tx_chiptuningdatabase_vehicletype n JOIN tx_chiptuningdatabase_vehicletype o ON o.uid = 2 SET n.description = o.description WHERE n.uid = 100009 AND (n.description IS NULL OR n.description = '')");

        foreach (self::MAP as $old => $new) {
            // Pool-Zuordnung: Kollisionen vorher entfernen
            $this->addSql(sprintf('DELETE m FROM tx_chiptuningdatabase_vehicle_data_pool_type_mm m JOIN tx_chiptuningdatabase_vehicle_data_pool_type_mm x ON x.uid_local = m.uid_local AND x.uid_foreign = %d WHERE m.uid_foreign = %d', $new, $old));
            $this->addSql(sprintf('UPDATE tx_chiptuningdatabase_vehicle_data_pool_type_mm SET uid_foreign = %d WHERE uid_foreign = %d', $new, $old));

            foreach (['client' => 'client_id', 'tool' => 'tool_id', 'package' => 'package_id', 'combination' => 'combination_id', 'lead' => 'lead_id'] as $t => $col) {
                $tbl = 'tfd_partner_wizard_' . $t . '_vehicletype';
                $this->addSql(sprintf('DELETE m FROM %1$s m JOIN %1$s x ON x.%2$s = m.%2$s AND x.vehicle_type_id = %3$d WHERE m.vehicle_type_id = %4$d', $tbl, $col, $new, $old));
                $this->addSql(sprintf('UPDATE %s SET vehicle_type_id = %d WHERE vehicle_type_id = %d', $tbl, $new, $old));
            }
            $this->addSql(sprintf('UPDATE tfd_partner_wizard_lead SET main_vehicle_type_id = %d WHERE main_vehicle_type_id = %d', $new, $old));
            $this->addSql(sprintf('UPDATE tx_chiptuningdatabase_vehiclebrand SET relation_to_vehicletype = %d WHERE relation_to_vehicletype = %d', $new, $old));
            $this->addSql(sprintf('UPDATE tx_chiptuningdatabase_brand_master SET vehicle_type = %d WHERE vehicle_type = %d', $new, $old));
            $this->addSql(sprintf('UPDATE chiptuning_widget_config SET vehicle_type = %d WHERE vehicle_type = %d', $new, $old));
            $this->addSql(sprintf('DELETE FROM tx_chiptuningdatabase_vehicletype WHERE uid = %d', $old));
        }

        $this->addSql('CREATE UNIQUE INDEX UNIQ_VEHICLETYPE_SLUG ON tx_chiptuningdatabase_vehicletype (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_VEHICLETYPE_SLUG ON tx_chiptuningdatabase_vehicletype');
        $this->throwIrreversibleMigrationException('Daten-Zusammenführung nicht umkehrbar, Backup nutzen.');
    }
}
