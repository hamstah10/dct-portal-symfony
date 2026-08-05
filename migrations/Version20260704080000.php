<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704080000 extends AbstractMigration
{
    /**
     * Maps legacy free-text vehicle category keys (as used by the wizard frontend and
     * stored in JSON columns) to the real App\Entity\VehicleType.slug values.
     * "alles" has no 1:1 VehicleType and is expanded to all active types instead.
     * "transporter" and "baumaschinen" have no VehicleType equivalent and are dropped.
     *
     * @var array<string, string>
     */
    private const LEGACY_KEY_TO_SLUG = [
        'pkw' => 'pkw',
        'lkw' => 'lkw',
        'motorrad' => 'motorrad',
        'agrar' => 'agrar',
        'atv' => 'atv',
        'marine' => 'boat',
        'jetski' => 'jetski',
    ];

    public function getDescription(): string
    {
        return 'Replace free-text vehicle category JSON columns with VehicleType relations across the partner wizard bundle';
    }

    public function up(Schema $schema): void
    {
        // Schema changes run synchronously (via the connection directly, not addSql,
        // which Doctrine only executes after up() returns) so the backfill below can
        // already see the new tables/columns.
        $connection = $this->connection;

        $connection->executeStatement('CREATE TABLE tfd_partner_wizard_client_vehicletype (client_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_TFD_PW_CLIENT_VT_CLIENT (client_id), INDEX IDX_TFD_PW_CLIENT_VT_TYPE (vehicle_type_id), PRIMARY KEY(client_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $connection->executeStatement('CREATE TABLE tfd_partner_wizard_tool_vehicletype (tool_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_TFD_PW_TOOL_VT_TOOL (tool_id), INDEX IDX_TFD_PW_TOOL_VT_TYPE (vehicle_type_id), PRIMARY KEY(tool_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $connection->executeStatement('CREATE TABLE tfd_partner_wizard_package_vehicletype (package_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_TFD_PW_PACKAGE_VT_PACKAGE (package_id), INDEX IDX_TFD_PW_PACKAGE_VT_TYPE (vehicle_type_id), PRIMARY KEY(package_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $connection->executeStatement('CREATE TABLE tfd_partner_wizard_combination_vehicletype (combination_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_TFD_PW_COMB_VT_COMB (combination_id), INDEX IDX_TFD_PW_COMB_VT_TYPE (vehicle_type_id), PRIMARY KEY(combination_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $connection->executeStatement('CREATE TABLE tfd_partner_wizard_lead_vehicletype (lead_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_TFD_PW_LEAD_VT_LEAD (lead_id), INDEX IDX_TFD_PW_LEAD_VT_TYPE (vehicle_type_id), PRIMARY KEY(lead_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_client_vehicletype ADD CONSTRAINT FK_TFD_PW_CLIENT_VT_CLIENT FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        if ($this->tableExists('tx_chiptuningdatabase_vehicletype', $connection)) {
            $connection->executeStatement('ALTER TABLE tfd_partner_wizard_client_vehicletype ADD CONSTRAINT FK_TFD_PW_CLIENT_VT_TYPE FOREIGN KEY (vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
        }
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_tool_vehicletype ADD CONSTRAINT FK_TFD_PW_TOOL_VT_TOOL FOREIGN KEY (tool_id) REFERENCES tfd_partner_wizard_tool (id) ON DELETE CASCADE');
        if ($this->tableExists('tx_chiptuningdatabase_vehicletype', $connection)) {
            $connection->executeStatement('ALTER TABLE tfd_partner_wizard_tool_vehicletype ADD CONSTRAINT FK_TFD_PW_TOOL_VT_TYPE FOREIGN KEY (vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
        }
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_package_vehicletype ADD CONSTRAINT FK_TFD_PW_PACKAGE_VT_PACKAGE FOREIGN KEY (package_id) REFERENCES tfd_partner_wizard_package (id) ON DELETE CASCADE');
        if ($this->tableExists('tx_chiptuningdatabase_vehicletype', $connection)) {
            $connection->executeStatement('ALTER TABLE tfd_partner_wizard_package_vehicletype ADD CONSTRAINT FK_TFD_PW_PACKAGE_VT_TYPE FOREIGN KEY (vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
        }
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_combination_vehicletype ADD CONSTRAINT FK_TFD_PW_COMB_VT_COMB FOREIGN KEY (combination_id) REFERENCES tfd_partner_wizard_combination (id) ON DELETE CASCADE');
        if ($this->tableExists('tx_chiptuningdatabase_vehicletype', $connection)) {
            $connection->executeStatement('ALTER TABLE tfd_partner_wizard_combination_vehicletype ADD CONSTRAINT FK_TFD_PW_COMB_VT_TYPE FOREIGN KEY (vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
        }
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead_vehicletype ADD CONSTRAINT FK_TFD_PW_LEAD_VT_LEAD FOREIGN KEY (lead_id) REFERENCES tfd_partner_wizard_lead (id) ON DELETE CASCADE');
        if ($this->tableExists('tx_chiptuningdatabase_vehicletype', $connection)) {
            $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead_vehicletype ADD CONSTRAINT FK_TFD_PW_LEAD_VT_TYPE FOREIGN KEY (vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
        }

        if ($this->tableExists('tx_chiptuningdatabase_vehicletype', $connection)) {
            $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead ADD main_vehicle_type_id INT DEFAULT NULL');
            $connection->executeStatement('CREATE INDEX IDX_TFD_PW_LEAD_MAIN_VT ON tfd_partner_wizard_lead (main_vehicle_type_id)');
            $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead ADD CONSTRAINT FK_TFD_PW_LEAD_MAIN_VT FOREIGN KEY (main_vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE SET NULL');

            $this->backfillFromJson('tfd_partner_wizard_client', 'client_id', 'tfd_partner_wizard_client_vehicletype', 'enabled_vehicle_categories');
            $this->backfillFromJson('tfd_partner_wizard_tool', 'tool_id', 'tfd_partner_wizard_tool_vehicletype', 'recommended_for_vehicle_categories');
            $this->backfillFromJson('tfd_partner_wizard_package', 'package_id', 'tfd_partner_wizard_package_vehicletype', 'recommended_vehicle_categories');
            $this->backfillFromJson('tfd_partner_wizard_combination', 'combination_id', 'tfd_partner_wizard_combination_vehicletype', 'vehicle_categories');
            $this->backfillFromJson('tfd_partner_wizard_lead', 'lead_id', 'tfd_partner_wizard_lead_vehicletype', 'vehicle_categories');
            $this->backfillMainVehicleType();
        }

        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_client DROP enabled_vehicle_categories');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_tool DROP recommended_for_vehicle_categories');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_package DROP recommended_vehicle_categories');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_combination DROP vehicle_categories');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead DROP vehicle_categories');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead DROP main_vehicle_category');
    }

    /**
     * Reads the legacy JSON string-array column, maps each value to a VehicleType uid
     * (case-insensitive against both name and slug), and inserts the resulting pairs
     * into the new join table. The literal value "alles" expands to all active
     * VehicleTypes. Unmappable values (e.g. "transporter", "baumaschinen") are skipped.
     */
    private function backfillFromJson(string $sourceTable, string $joinTableSourceColumn, string $joinTable, string $jsonColumn): void
    {
        $connection = $this->connection;

        $slugToUid = [];
        foreach ($connection->fetchAllAssociative('SELECT uid, name, slug FROM tx_chiptuningdatabase_vehicletype WHERE active = 1') as $row) {
            $slugToUid[mb_strtolower((string) $row['name'])] = (int) $row['uid'];
            if (null !== $row['slug'] && '' !== $row['slug']) {
                $slugToUid[mb_strtolower((string) $row['slug'])] = (int) $row['uid'];
            }
        }
        foreach (self::LEGACY_KEY_TO_SLUG as $legacyKey => $slug) {
            if (isset($slugToUid[$slug])) {
                $slugToUid[$legacyKey] = $slugToUid[$slug];
            }
        }
        $allActiveUids = array_values(array_unique($slugToUid));

        $rows = $connection->fetchAllAssociative(sprintf('SELECT id, %s FROM %s', $jsonColumn, $sourceTable));

        $inserted = 0;
        $skipped = [];

        foreach ($rows as $row) {
            $values = json_decode((string) $row[$jsonColumn], true);
            if (!is_array($values)) {
                continue;
            }

            $uids = [];
            foreach ($values as $value) {
                $key = mb_strtolower(trim((string) $value));
                if ('' === $key) {
                    continue;
                }
                if ('alles' === $key) {
                    $uids = array_merge($uids, $allActiveUids);
                    continue;
                }
                if (isset($slugToUid[$key])) {
                    $uids[] = $slugToUid[$key];
                } else {
                    $skipped[$key] = true;
                }
            }

            foreach (array_unique($uids) as $uid) {
                $connection->executeStatement(
                    sprintf('INSERT IGNORE INTO %s (%s, vehicle_type_id) VALUES (:sourceId, :vehicleTypeId)', $joinTable, $joinTableSourceColumn),
                    ['sourceId' => (int) $row['id'], 'vehicleTypeId' => $uid]
                );
                ++$inserted;
            }
        }

        $this->write(sprintf(
            '  -> %s: inserted %d rows into %s%s',
            $sourceTable,
            $inserted,
            $joinTable,
            [] !== $skipped ? ('; unmapped legacy values skipped: ' . implode(', ', array_keys($skipped))) : ''
        ));
    }

    /**
     * Maps PartnerWizardLead.mainVehicleCategory (legacy string) to main_vehicle_type_id.
     * Values without a 1:1 VehicleType (e.g. "car", "alles") are left NULL; the original
     * raw string remains readable in the lead's untouched `payload` JSON column.
     */
    private function backfillMainVehicleType(): void
    {
        $connection = $this->connection;

        $slugToUid = [];
        foreach ($connection->fetchAllAssociative('SELECT uid, name, slug FROM tx_chiptuningdatabase_vehicletype WHERE active = 1') as $row) {
            $slugToUid[mb_strtolower((string) $row['name'])] = (int) $row['uid'];
            if (null !== $row['slug'] && '' !== $row['slug']) {
                $slugToUid[mb_strtolower((string) $row['slug'])] = (int) $row['uid'];
            }
        }
        foreach (self::LEGACY_KEY_TO_SLUG as $legacyKey => $slug) {
            if (isset($slugToUid[$slug])) {
                $slugToUid[$legacyKey] = $slugToUid[$slug];
            }
        }

        $rows = $connection->fetchAllAssociative('SELECT id, main_vehicle_category FROM tfd_partner_wizard_lead WHERE main_vehicle_category IS NOT NULL');

        $updated = 0;
        $unmapped = [];

        foreach ($rows as $row) {
            $key = mb_strtolower(trim((string) $row['main_vehicle_category']));
            if ('' === $key || !isset($slugToUid[$key])) {
                if ('' !== $key) {
                    $unmapped[$key] = true;
                }
                continue;
            }

            $connection->executeStatement(
                'UPDATE tfd_partner_wizard_lead SET main_vehicle_type_id = :vehicleTypeId WHERE id = :id',
                ['vehicleTypeId' => $slugToUid[$key], 'id' => (int) $row['id']]
            );
            ++$updated;
        }

        $this->write(sprintf(
            '  -> tfd_partner_wizard_lead.main_vehicle_category: mapped %d rows%s',
            $updated,
            [] !== $unmapped ? ('; left NULL for unmapped values: ' . implode(', ', array_keys($unmapped))) : ''
        ));
    }

    public function down(Schema $schema): void
    {
        $connection = $this->connection;

        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_client ADD enabled_vehicle_categories JSON NOT NULL');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_tool ADD recommended_for_vehicle_categories JSON NOT NULL');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_package ADD recommended_vehicle_categories JSON NOT NULL');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_combination ADD vehicle_categories JSON NOT NULL');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead ADD vehicle_categories JSON NOT NULL, ADD main_vehicle_category VARCHAR(80) DEFAULT NULL');

        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead DROP FOREIGN KEY FK_TFD_PW_LEAD_MAIN_VT');
        $connection->executeStatement('DROP INDEX IDX_TFD_PW_LEAD_MAIN_VT ON tfd_partner_wizard_lead');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead DROP main_vehicle_type_id');

        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_client_vehicletype DROP FOREIGN KEY FK_TFD_PW_CLIENT_VT_CLIENT');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_client_vehicletype DROP FOREIGN KEY FK_TFD_PW_CLIENT_VT_TYPE');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_tool_vehicletype DROP FOREIGN KEY FK_TFD_PW_TOOL_VT_TOOL');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_tool_vehicletype DROP FOREIGN KEY FK_TFD_PW_TOOL_VT_TYPE');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_package_vehicletype DROP FOREIGN KEY FK_TFD_PW_PACKAGE_VT_PACKAGE');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_package_vehicletype DROP FOREIGN KEY FK_TFD_PW_PACKAGE_VT_TYPE');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_combination_vehicletype DROP FOREIGN KEY FK_TFD_PW_COMB_VT_COMB');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_combination_vehicletype DROP FOREIGN KEY FK_TFD_PW_COMB_VT_TYPE');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead_vehicletype DROP FOREIGN KEY FK_TFD_PW_LEAD_VT_LEAD');
        $connection->executeStatement('ALTER TABLE tfd_partner_wizard_lead_vehicletype DROP FOREIGN KEY FK_TFD_PW_LEAD_VT_TYPE');

        $connection->executeStatement('DROP TABLE tfd_partner_wizard_client_vehicletype');
        $connection->executeStatement('DROP TABLE tfd_partner_wizard_tool_vehicletype');
        $connection->executeStatement('DROP TABLE tfd_partner_wizard_package_vehicletype');
        $connection->executeStatement('DROP TABLE tfd_partner_wizard_combination_vehicletype');
        $connection->executeStatement('DROP TABLE tfd_partner_wizard_lead_vehicletype');
    }

    public function isTransactional(): bool
    {
        return false;
    }

    private function tableExists(string $tableName, $connection): bool
    {
        try {
            $result = $connection->executeQuery(
                "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
                [$tableName]
            )->fetchAssociative();
            return (bool) $result;
        } catch (\Exception) {
            return false;
        }
    }
}
