<?php

namespace App\Service;

use App\Entity\VehicleDataPool;
use Doctrine\DBAL\Connection;

class VehicleDataPoolPurger
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function purge(VehicleDataPool $pool): void
    {
        $poolId = $pool->getId();
        if (null === $poolId) {
            throw new \InvalidArgumentException('Only persisted pools can be purged.');
        }

        $this->connection->beginTransaction();
        try {
            $this->cleanupContexts($poolId);
            $this->cleanupAssociations($poolId);
            $this->cleanupEntities($poolId);

            $this->connection->executeStatement('DELETE FROM tx_chiptuningdatabase_vehicle_data_pool WHERE uid = ?', [$poolId]);

            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw new \RuntimeException(sprintf('Unable to purge pool %d: %s', $poolId, $exception->getMessage()), 0, $exception);
        }
    }

    private function cleanupContexts(int $poolId): void
    {
        $this->connection->executeStatement('DELETE FROM tx_chiptuningdatabase_vehicle_data_context WHERE default_vehicle_data_pool = ?', [$poolId]);
        $this->connection->executeStatement('DELETE FROM tx_chiptuningdatabase_vehicle_data_context_pool_mm WHERE uid_foreign = ?', [$poolId]);
        $this->connection->executeStatement('DELETE FROM tx_chiptuningdatabase_vehicle_data_pool_type_mm WHERE uid_local = ?', [$poolId]);
    }

    private function cleanupAssociations(int $poolId): void
    {
        $statements = [
            'DELETE FROM scraper_import_log WHERE vehicle_data_pool = ?',
            'DELETE FROM tx_chiptuningdatabase_vehicle_frontend_settings WHERE vehicle_data_pool = ?',
            'DELETE FROM tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm WHERE uid_local IN (SELECT uid FROM tx_chiptuningdatabase_vehicleengine WHERE vehicle_data_pool = ?)',
            'DELETE FROM tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm WHERE uid_foreign IN (SELECT uid FROM tx_chiptuningdatabase_domain_model_ecu WHERE vehicle_data_pool = ?)',
            'DELETE FROM tx_chiptuningdatabase_domain_model_vehicleengine_gearbox_mm WHERE uid_local IN (SELECT uid FROM tx_chiptuningdatabase_vehicleengine WHERE vehicle_data_pool = ?)',
            'DELETE FROM tx_chiptuningdatabase_domain_model_vehicleengine_gearbox_mm WHERE uid_foreign IN (SELECT uid FROM tx_chiptuningdatabase_gearbox WHERE vehicle_data_pool = ?)',
            'DELETE FROM tx_chiptuningdatabase_gearbox_ecu_mm WHERE uid_local IN (SELECT uid FROM tx_chiptuningdatabase_gearbox WHERE vehicle_data_pool = ?)',
            'DELETE FROM tx_chiptuningdatabase_gearbox_ecu_mm WHERE uid_foreign IN (SELECT uid FROM tx_chiptuningdatabase_domain_model_ecu WHERE vehicle_data_pool = ?)',
            'DELETE FROM tx_chiptuningdatabase_ecuoption_ecu_mm WHERE uid_local IN (SELECT uid FROM tx_chiptuningdatabase_domain_model_ecuoption WHERE vehicle_data_pool = ?)',
            'DELETE FROM tx_chiptuningdatabase_ecuoption_ecu_mm WHERE uid_foreign IN (SELECT uid FROM tx_chiptuningdatabase_domain_model_ecu WHERE vehicle_data_pool = ?)',
            'DELETE FROM tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm WHERE uid_local IN (SELECT uid FROM tx_chiptuningdatabase_domain_model_ecu WHERE vehicle_data_pool = ?)',
        ];

        foreach ($statements as $sql) {
            $this->connection->executeStatement($sql, [$poolId]);
        }
    }

    private function cleanupEntities(int $poolId): void
    {
        $statements = [
            'DELETE FROM tx_chiptuningdatabase_vehiclestage WHERE vehicle_data_pool = ?',
            'DELETE FROM tx_chiptuningdatabase_vehicleengine WHERE vehicle_data_pool = ?',
            'DELETE FROM tx_chiptuningdatabase_vehiclemodel WHERE vehicle_data_pool = ?',
            'DELETE FROM tx_chiptuningdatabase_vehicleseries WHERE vehicle_data_pool = ?',
            'DELETE FROM tx_chiptuningdatabase_vehiclebrand WHERE vehicle_data_pool = ?',
            'DELETE FROM tx_chiptuningdatabase_domain_model_ecuoption WHERE vehicle_data_pool = ?',
            'DELETE FROM tx_chiptuningdatabase_domain_model_ecu WHERE vehicle_data_pool = ?',
            'DELETE FROM tx_chiptuningdatabase_gearbox WHERE vehicle_data_pool = ?',
        ];

        foreach ($statements as $sql) {
            $this->connection->executeStatement($sql, [$poolId]);
        }
    }
}
