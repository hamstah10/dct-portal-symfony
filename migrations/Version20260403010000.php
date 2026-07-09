<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce vehicle data pools and contexts, attach existing vehicle database records to a live pool, and keep API tokens backward compatible.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehicle_data_pool (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, pool_key VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_A77A5ED5D7A605B7 (pool_key), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehicle_data_context (uid INT AUTO_INCREMENT NOT NULL, default_vehicle_data_pool INT DEFAULT NULL, name VARCHAR(255) NOT NULL, context_key VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, usage_type VARCHAR(50) NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_43D4AE467585E27 (default_vehicle_data_pool), UNIQUE INDEX UNIQ_43D4AE4931A6D0BC (context_key), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm (uid_local INT NOT NULL, uid_foreign INT NOT NULL, INDEX IDX_6760EE8B442812D3 (uid_local), INDEX IDX_6760EE8BC067CF9D (uid_foreign), PRIMARY KEY(uid_local, uid_foreign)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm (uid_local INT NOT NULL, uid_foreign INT NOT NULL, INDEX IDX_58A30681442812D3 (uid_local), INDEX IDX_58A30681C067CF9D (uid_foreign), PRIMARY KEY(uid_local, uid_foreign)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context ADD CONSTRAINT FK_43D4AE467585E27 FOREIGN KEY (default_vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm ADD CONSTRAINT FK_6760EE8B442812D3 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm ADD CONSTRAINT FK_6760EE8BC067CF9D FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm ADD CONSTRAINT FK_58A30681442812D3 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_vehicle_data_context (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm ADD CONSTRAINT FK_58A30681C067CF9D FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE api_token ADD vehicle_data_context INT DEFAULT NULL');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EBE2A20A48 FOREIGN KEY (vehicle_data_context) REFERENCES tx_chiptuningdatabase_vehicle_data_context (uid) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7BA2F5EBE2A20A48 ON api_token (vehicle_data_context)');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD vehicle_data_pool INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries ADD vehicle_data_pool INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclemodel ADD vehicle_data_pool INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD vehicle_data_pool INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD vehicle_data_pool INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu ADD vehicle_data_pool INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption ADD vehicle_data_pool INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox ADD vehicle_data_pool INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings ADD vehicle_data_pool INT DEFAULT NULL, ADD name VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD active TINYINT(1) NOT NULL DEFAULT 1');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD CONSTRAINT FK_7FBEF0A7D9DD56D0 FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries ADD CONSTRAINT FK_1F174B4FD9DD56D0 FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclemodel ADD CONSTRAINT FK_38068742D9DD56D0 FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD CONSTRAINT FK_F4ED206AD9DD56D0 FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD CONSTRAINT FK_1C1B6D8FD9DD56D0 FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu ADD CONSTRAINT FK_EEAF4B4BD9DD56D0 FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption ADD CONSTRAINT FK_688A1E6FD9DD56D0 FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox ADD CONSTRAINT FK_F4E3EE0FD9DD56D0 FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings ADD CONSTRAINT FK_404A3B7CD9DD56D0 FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');

        $this->addSql('CREATE INDEX IDX_7FBEF0A7D9DD56D0 ON tx_chiptuningdatabase_vehiclebrand (vehicle_data_pool)');
        $this->addSql('CREATE INDEX IDX_1F174B4FD9DD56D0 ON tx_chiptuningdatabase_vehicleseries (vehicle_data_pool)');
        $this->addSql('CREATE INDEX IDX_38068742D9DD56D0 ON tx_chiptuningdatabase_vehiclemodel (vehicle_data_pool)');
        $this->addSql('CREATE INDEX IDX_F4ED206AD9DD56D0 ON tx_chiptuningdatabase_vehicleengine (vehicle_data_pool)');
        $this->addSql('CREATE INDEX IDX_1C1B6D8FD9DD56D0 ON tx_chiptuningdatabase_vehiclestage (vehicle_data_pool)');
        $this->addSql('CREATE INDEX IDX_EEAF4B4BD9DD56D0 ON tx_chiptuningdatabase_domain_model_ecu (vehicle_data_pool)');
        $this->addSql('CREATE INDEX IDX_688A1E6FD9DD56D0 ON tx_chiptuningdatabase_domain_model_ecuoption (vehicle_data_pool)');
        $this->addSql('CREATE INDEX IDX_F4E3EE0FD9DD56D0 ON tx_chiptuningdatabase_gearbox (vehicle_data_pool)');
        $this->addSql('CREATE INDEX IDX_404A3B7CD9DD56D0 ON tx_chiptuningdatabase_vehicle_frontend_settings (vehicle_data_pool)');

        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehicle_data_pool (name, pool_key, description, active) VALUES ('Live', 'live', 'Automatisch migrierter Standardbestand.', 1)");
        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehicle_data_context (default_vehicle_data_pool, name, context_key, description, usage_type, active) VALUES ((SELECT uid FROM tx_chiptuningdatabase_vehicle_data_pool WHERE pool_key = 'live' LIMIT 1), 'Live API', 'live-api', 'Standardkontext fuer bestehende Fahrzeugdatenbank-Clients.', 'api', 1)");
        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehicle_data_pool_type_mm (uid_local, uid_foreign) SELECT p.uid, t.uid FROM tx_chiptuningdatabase_vehicle_data_pool p CROSS JOIN tx_chiptuningdatabase_vehicletype t WHERE p.pool_key = 'live'");
        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehicle_data_context_pool_mm (uid_local, uid_foreign) SELECT c.uid, p.uid FROM tx_chiptuningdatabase_vehicle_data_context c INNER JOIN tx_chiptuningdatabase_vehicle_data_pool p ON p.pool_key = 'live' WHERE c.context_key = 'live-api'");

        $this->addSql("UPDATE api_token SET vehicle_data_context = (SELECT uid FROM tx_chiptuningdatabase_vehicle_data_context WHERE context_key = 'live-api' LIMIT 1) WHERE vehicle_data_context IS NULL");
        $this->addSql("UPDATE tx_chiptuningdatabase_vehiclebrand SET vehicle_data_pool = (SELECT uid FROM tx_chiptuningdatabase_vehicle_data_pool WHERE pool_key = 'live' LIMIT 1) WHERE vehicle_data_pool IS NULL");
        $this->addSql("UPDATE tx_chiptuningdatabase_vehicleseries SET vehicle_data_pool = (SELECT uid FROM tx_chiptuningdatabase_vehicle_data_pool WHERE pool_key = 'live' LIMIT 1) WHERE vehicle_data_pool IS NULL");
        $this->addSql("UPDATE tx_chiptuningdatabase_vehiclemodel SET vehicle_data_pool = (SELECT uid FROM tx_chiptuningdatabase_vehicle_data_pool WHERE pool_key = 'live' LIMIT 1) WHERE vehicle_data_pool IS NULL");
        $this->addSql("UPDATE tx_chiptuningdatabase_vehicleengine SET vehicle_data_pool = (SELECT uid FROM tx_chiptuningdatabase_vehicle_data_pool WHERE pool_key = 'live' LIMIT 1) WHERE vehicle_data_pool IS NULL");
        $this->addSql("UPDATE tx_chiptuningdatabase_vehiclestage SET vehicle_data_pool = (SELECT uid FROM tx_chiptuningdatabase_vehicle_data_pool WHERE pool_key = 'live' LIMIT 1) WHERE vehicle_data_pool IS NULL");
        $this->addSql("UPDATE tx_chiptuningdatabase_domain_model_ecu SET vehicle_data_pool = (SELECT uid FROM tx_chiptuningdatabase_vehicle_data_pool WHERE pool_key = 'live' LIMIT 1) WHERE vehicle_data_pool IS NULL");
        $this->addSql("UPDATE tx_chiptuningdatabase_domain_model_ecuoption SET vehicle_data_pool = (SELECT uid FROM tx_chiptuningdatabase_vehicle_data_pool WHERE pool_key = 'live' LIMIT 1) WHERE vehicle_data_pool IS NULL");
        $this->addSql("UPDATE tx_chiptuningdatabase_gearbox SET vehicle_data_pool = (SELECT uid FROM tx_chiptuningdatabase_vehicle_data_pool WHERE pool_key = 'live' LIMIT 1) WHERE vehicle_data_pool IS NULL");
        $this->addSql("UPDATE tx_chiptuningdatabase_vehicle_frontend_settings SET vehicle_data_pool = (SELECT uid FROM tx_chiptuningdatabase_vehicle_data_pool WHERE pool_key = 'live' LIMIT 1), name = COALESCE(NULLIF(name, ''), 'Live Frontend'), active = 1 WHERE vehicle_data_pool IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EBE2A20A48');
        $this->addSql('DROP INDEX IDX_7BA2F5EBE2A20A48 ON api_token');
        $this->addSql('ALTER TABLE api_token DROP vehicle_data_context');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings DROP FOREIGN KEY FK_404A3B7CD9DD56D0');
        $this->addSql('DROP INDEX IDX_404A3B7CD9DD56D0 ON tx_chiptuningdatabase_vehicle_frontend_settings');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings DROP vehicle_data_pool, DROP name, DROP description, DROP active');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox DROP FOREIGN KEY FK_F4E3EE0FD9DD56D0');
        $this->addSql('DROP INDEX IDX_F4E3EE0FD9DD56D0 ON tx_chiptuningdatabase_gearbox');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox DROP vehicle_data_pool');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption DROP FOREIGN KEY FK_688A1E6FD9DD56D0');
        $this->addSql('DROP INDEX IDX_688A1E6FD9DD56D0 ON tx_chiptuningdatabase_domain_model_ecuoption');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption DROP vehicle_data_pool');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP FOREIGN KEY FK_EEAF4B4BD9DD56D0');
        $this->addSql('DROP INDEX IDX_EEAF4B4BD9DD56D0 ON tx_chiptuningdatabase_domain_model_ecu');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP vehicle_data_pool');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP FOREIGN KEY FK_1C1B6D8FD9DD56D0');
        $this->addSql('DROP INDEX IDX_1C1B6D8FD9DD56D0 ON tx_chiptuningdatabase_vehiclestage');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP vehicle_data_pool');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP FOREIGN KEY FK_F4ED206AD9DD56D0');
        $this->addSql('DROP INDEX IDX_F4ED206AD9DD56D0 ON tx_chiptuningdatabase_vehicleengine');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP vehicle_data_pool');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclemodel DROP FOREIGN KEY FK_38068742D9DD56D0');
        $this->addSql('DROP INDEX IDX_38068742D9DD56D0 ON tx_chiptuningdatabase_vehiclemodel');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclemodel DROP vehicle_data_pool');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries DROP FOREIGN KEY FK_1F174B4FD9DD56D0');
        $this->addSql('DROP INDEX IDX_1F174B4FD9DD56D0 ON tx_chiptuningdatabase_vehicleseries');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries DROP vehicle_data_pool');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP FOREIGN KEY FK_7FBEF0A7D9DD56D0');
        $this->addSql('DROP INDEX IDX_7FBEF0A7D9DD56D0 ON tx_chiptuningdatabase_vehiclebrand');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP vehicle_data_pool');

        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context DROP FOREIGN KEY FK_43D4AE467585E27');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehicle_data_context');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehicle_data_pool');
    }
}
