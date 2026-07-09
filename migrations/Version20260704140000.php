<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove ECU records that are actually scraped engine metadata (power/torque/standard/method values) misfiled as ECU manufacturers "Power HP", "Power kW", "Standard", "Torque / traction", "Method".';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "DELETE mm FROM tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm mm
             INNER JOIN tx_chiptuningdatabase_domain_model_ecu e ON e.uid = mm.uid_local
             INNER JOIN tx_chiptuningdatabase_domain_model_ecumanufacturer em ON em.uid = e.manufacturer
             WHERE em.name IN ('Power HP', 'Power kW', 'Standard', 'Torque / traction', 'Method')"
        );
        $this->addSql(
            "DELETE mm FROM tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm mm
             INNER JOIN tx_chiptuningdatabase_domain_model_ecu e ON e.uid = mm.uid_foreign
             INNER JOIN tx_chiptuningdatabase_domain_model_ecumanufacturer em ON em.uid = e.manufacturer
             WHERE em.name IN ('Power HP', 'Power kW', 'Standard', 'Torque / traction', 'Method')"
        );
        $this->addSql(
            "DELETE e FROM tx_chiptuningdatabase_domain_model_ecu e
             INNER JOIN tx_chiptuningdatabase_domain_model_ecumanufacturer em ON em.uid = e.manufacturer
             WHERE em.name IN ('Power HP', 'Power kW', 'Standard', 'Torque / traction', 'Method')"
        );
        $this->addSql(
            "DELETE FROM tx_chiptuningdatabase_domain_model_ecumanufacturer
             WHERE name IN ('Power HP', 'Power kW', 'Standard', 'Torque / traction', 'Method')"
        );
    }

    public function down(Schema $schema): void
    {
        // Data cleanup is not reversible.
    }
}
