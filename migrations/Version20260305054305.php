<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305054305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_domain_model_ecu (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, image INT DEFAULT NULL, manufacturer INT DEFAULT NULL, tool_manufacturer INT DEFAULT NULL, INDEX IDX_888235EF3D0AE6DC (manufacturer), INDEX IDX_888235EFB143A831 (tool_manufacturer), PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm (uid_local INT NOT NULL, uid_foreign INT NOT NULL, INDEX IDX_B9C5E9D75C4C8485 (uid_local), INDEX IDX_B9C5E9D71D17607B (uid_foreign), PRIMARY KEY (uid_local, uid_foreign)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_ecuoption_ecu_mm (uid_local INT NOT NULL, uid_foreign INT NOT NULL, INDEX IDX_D5E34E885C4C8485 (uid_local), INDEX IDX_D5E34E881D17607B (uid_foreign), PRIMARY KEY (uid_local, uid_foreign)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_domain_model_ecumanufacturer (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, logo INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_domain_model_ecuoption (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, tooltip_text LONGTEXT DEFAULT NULL, price NUMERIC(11, 2) DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, image INT DEFAULT NULL, images INT DEFAULT NULL, slug VARCHAR(2048) DEFAULT NULL, is_main_option TINYINT NOT NULL, PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_domain_model_toolmanufacturer (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, beschreibung LONGTEXT DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, plz VARCHAR(20) DEFAULT NULL, stadt VARCHAR(255) DEFAULT NULL, land VARCHAR(255) DEFAULT NULL, telefon VARCHAR(50) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, logo INT DEFAULT NULL, images INT DEFAULT NULL, PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_domain_model_toolmethode (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, beschreibung LONGTEXT DEFAULT NULL, image INT DEFAULT NULL, tool_manufacturer INT DEFAULT NULL, INDEX IDX_E3D33709B143A831 (tool_manufacturer), PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_gearbox (uid INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, sorting INT NOT NULL, active TINYINT NOT NULL, gearbox_type INT DEFAULT NULL, INDEX IDX_94FFA4A9F97439C6 (gearbox_type), PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_gearbox_ecu_mm (uid_local INT NOT NULL, uid_foreign INT NOT NULL, INDEX IDX_BEF390425C4C8485 (uid_local), INDEX IDX_BEF390421D17607B (uid_foreign), PRIMARY KEY (uid_local, uid_foreign)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_gearboxtype (uid INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, active TINYINT NOT NULL, PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehiclebrand (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, slug VARCHAR(2048) DEFAULT NULL, active TINYINT NOT NULL, sorting INT NOT NULL, relation_to_vehicletype INT DEFAULT NULL, INDEX IDX_FEEACADD26018B2 (relation_to_vehicletype), PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehicleengine (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, active TINYINT NOT NULL, horsepower INT DEFAULT NULL, torque INT DEFAULT NULL, ecus INT DEFAULT NULL, fuel VARCHAR(50) DEFAULT NULL, displacement VARCHAR(255) DEFAULT NULL, compression VARCHAR(255) DEFAULT NULL, bore_stroke VARCHAR(255) DEFAULT NULL, engine_code VARCHAR(255) DEFAULT NULL, vmax INT DEFAULT NULL, acceleration_0_100 NUMERIC(5, 2) DEFAULT NULL, acceleration_0_200 NUMERIC(5, 2) DEFAULT NULL, acceleration_100_200 NUMERIC(5, 2) DEFAULT NULL, slug VARCHAR(2048) DEFAULT NULL, sorting INT NOT NULL, relation_to_vehiclemodel INT DEFAULT NULL, INDEX IDX_256CA04BB016865A (relation_to_vehiclemodel), PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm (uid_local INT NOT NULL, uid_foreign INT NOT NULL, INDEX IDX_18D55ADA5C4C8485 (uid_local), INDEX IDX_18D55ADA1D17607B (uid_foreign), PRIMARY KEY (uid_local, uid_foreign)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehiclemodel (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, slug VARCHAR(2048) DEFAULT NULL, active TINYINT NOT NULL, sorting INT NOT NULL, relation_to_vehicleseries INT DEFAULT NULL, INDEX IDX_C429272C4EC65043 (relation_to_vehicleseries), PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehicleseries (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, slug VARCHAR(2048) DEFAULT NULL, active TINYINT NOT NULL, sorting INT NOT NULL, relation_to_vehiclebrand INT DEFAULT NULL, INDEX IDX_F7D4BBEB7BD10DDB (relation_to_vehiclebrand), PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehiclestage (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price NUMERIC(10, 2) DEFAULT NULL, hp INT DEFAULT NULL, nm INT DEFAULT NULL, images LONGTEXT DEFAULT NULL, videos LONGTEXT DEFAULT NULL, ecu INT DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, active TINYINT NOT NULL, relation_to_vehicleengine INT DEFAULT NULL, INDEX IDX_D1C0C69C9C7E4BE3 (relation_to_vehicleengine), PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehicletype (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, slug VARCHAR(2048) DEFAULT NULL, image INT DEFAULT NULL, active TINYINT NOT NULL, sorting INT NOT NULL, PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu ADD CONSTRAINT FK_888235EF3D0AE6DC FOREIGN KEY (manufacturer) REFERENCES tx_chiptuningdatabase_domain_model_ecumanufacturer (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu ADD CONSTRAINT FK_888235EFB143A831 FOREIGN KEY (tool_manufacturer) REFERENCES tx_chiptuningdatabase_domain_model_toolmanufacturer (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm ADD CONSTRAINT FK_B9C5E9D75C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_domain_model_ecu (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm ADD CONSTRAINT FK_B9C5E9D71D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_domain_model_toolmethode (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecuoption_ecu_mm ADD CONSTRAINT FK_D5E34E885C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_domain_model_ecu (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecuoption_ecu_mm ADD CONSTRAINT FK_D5E34E881D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_domain_model_ecuoption (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmethode ADD CONSTRAINT FK_E3D33709B143A831 FOREIGN KEY (tool_manufacturer) REFERENCES tx_chiptuningdatabase_domain_model_toolmanufacturer (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox ADD CONSTRAINT FK_94FFA4A9F97439C6 FOREIGN KEY (gearbox_type) REFERENCES tx_chiptuningdatabase_gearboxtype (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox_ecu_mm ADD CONSTRAINT FK_BEF390425C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_gearbox (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox_ecu_mm ADD CONSTRAINT FK_BEF390421D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_domain_model_ecu (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD CONSTRAINT FK_FEEACADD26018B2 FOREIGN KEY (relation_to_vehicletype) REFERENCES tx_chiptuningdatabase_vehicletype (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD CONSTRAINT FK_256CA04BB016865A FOREIGN KEY (relation_to_vehiclemodel) REFERENCES tx_chiptuningdatabase_vehiclemodel (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm ADD CONSTRAINT FK_18D55ADA5C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_vehicleengine (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm ADD CONSTRAINT FK_18D55ADA1D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_domain_model_ecu (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclemodel ADD CONSTRAINT FK_C429272C4EC65043 FOREIGN KEY (relation_to_vehicleseries) REFERENCES tx_chiptuningdatabase_vehicleseries (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries ADD CONSTRAINT FK_F7D4BBEB7BD10DDB FOREIGN KEY (relation_to_vehiclebrand) REFERENCES tx_chiptuningdatabase_vehiclebrand (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage ADD CONSTRAINT FK_D1C0C69C9C7E4BE3 FOREIGN KEY (relation_to_vehicleengine) REFERENCES tx_chiptuningdatabase_vehicleengine (uid)');

        // Testdaten Fahrzeugdatenbank
        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehicletype (uid, name, description, slug, image, active, sorting) VALUES
            (1, 'PKW', 'Personenkraftwagen', 'pkw', NULL, 1, 10),
            (2, 'Transporter', 'Leichte Nutzfahrzeuge', 'transporter', NULL, 1, 20)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehiclebrand (uid, name, image, description, slug, active, sorting, relation_to_vehicletype) VALUES
            (1, 'Volkswagen', NULL, 'Volkswagen Fahrzeuge', 'volkswagen', 1, 10, 1),
            (2, 'BMW', NULL, 'BMW Fahrzeuge', 'bmw', 1, 20, 1)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehicleseries (uid, name, image, description, slug, active, sorting, relation_to_vehiclebrand) VALUES
            (1, 'Golf', NULL, 'Golf Baureihe', 'golf', 1, 10, 1),
            (2, '3er', NULL, 'BMW 3er Baureihe', '3er', 1, 20, 2)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehiclemodel (uid, name, image, description, slug, active, sorting, relation_to_vehicleseries) VALUES
            (1, 'Golf 7 GTI', NULL, '2.0 TSI', 'golf-7-gti', 1, 10, 1),
            (2, '320d F30', NULL, '2.0 Diesel', '320d-f30', 1, 20, 2)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_gearboxtype (uid, title, description, active) VALUES
            (1, 'DSG', 'Direktschaltgetriebe', 1),
            (2, 'Automatik', 'Wandlerautomatik', 1)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_domain_model_ecumanufacturer (uid, name, logo, description) VALUES
            (1, 'Bosch', NULL, 'Bosch ECU Hersteller'),
            (2, 'Siemens', NULL, 'Siemens ECU Hersteller')");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_domain_model_toolmanufacturer (uid, name, beschreibung, adresse, plz, stadt, land, telefon, email, logo, images) VALUES
            (1, 'Alientech', 'Tuning Tools', 'Via Torino 1', '20100', 'Mailand', 'Italien', '+39-001', 'info@alientech.example', NULL, NULL),
            (2, 'Magic Motorsport', 'Bench und OBD Tools', 'Via Roma 2', '20100', 'Mailand', 'Italien', '+39-002', 'support@magic.example', NULL, NULL)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_domain_model_toolmethode (uid, name, beschreibung, image, tool_manufacturer) VALUES
            (1, 'OBD', 'Programmierung via OBD', NULL, 1),
            (2, 'Bench', 'Programmierung auf dem Tisch', NULL, 2)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_domain_model_ecu (uid, name, type, image, manufacturer, tool_manufacturer) VALUES
            (1, 'Bosch MED17', 'MED17', NULL, 1, 1),
            (2, 'Bosch EDC17', 'EDC17', NULL, 1, 2)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_domain_model_ecuoption (uid, name, description, tooltip_text, price, icon, image, images, slug, is_main_option) VALUES
            (1, 'Vmax Off', 'Begrenzer entfernen', 'Entfernt die werkseitige Vmax-Begrenzung', 149.00, NULL, NULL, NULL, 'vmax-off', 0),
            (2, 'EGR Off', 'AGR Deaktivierung', 'Deaktiviert AGR-Funktion softwareseitig', 199.00, NULL, NULL, NULL, 'egr-off', 1)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehicleengine (uid, name, image, description, active, horsepower, torque, ecus, fuel, displacement, compression, bore_stroke, engine_code, vmax, acceleration_0_100, acceleration_0_200, acceleration_100_200, slug, sorting, relation_to_vehiclemodel) VALUES
            (1, '2.0 TSI GTI', NULL, 'Serienmotor GTI', 1, 245, 370, 1, 'Benzin', '1984 ccm', '9.6:1', '82.5 x 92.8', 'EA888', 250, 6.20, 24.50, 18.30, '2-0-tsi-gti', 10, 1),
            (2, '2.0d 320d', NULL, 'Serienmotor 320d', 1, 190, 400, 1, 'Diesel', '1995 ccm', '16.5:1', '84.0 x 90.0', 'B47', 240, 7.20, 28.90, 21.40, '2-0d-320d', 20, 2)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehiclestage (uid, name, description, price, hp, nm, images, videos, ecu, icon, active, relation_to_vehicleengine) VALUES
            (1, 'Stage 1', 'Softwareoptimierung ohne Hardware', 699.00, 300, 450, NULL, NULL, 1, NULL, 1, 1),
            (2, 'Stage 1', 'Softwareoptimierung ohne Hardware', 649.00, 230, 470, NULL, NULL, 2, NULL, 1, 2)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_gearbox (uid, title, description, icon, sorting, active, gearbox_type) VALUES
            (1, 'DQ381', '7-Gang DSG', NULL, 10, 1, 1),
            (2, 'ZF8HP', '8-Gang Automatik', NULL, 20, 1, 2)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm (uid_local, uid_foreign) VALUES
            (1, 1),
            (1, 2),
            (2, 2)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_ecuoption_ecu_mm (uid_local, uid_foreign) VALUES
            (1, 1),
            (2, 2)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm (uid_local, uid_foreign) VALUES
            (1, 1),
            (2, 2)");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_gearbox_ecu_mm (uid_local, uid_foreign) VALUES
            (1, 1),
            (2, 2)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP FOREIGN KEY FK_888235EF3D0AE6DC');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP FOREIGN KEY FK_888235EFB143A831');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm DROP FOREIGN KEY FK_B9C5E9D75C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm DROP FOREIGN KEY FK_B9C5E9D71D17607B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecuoption_ecu_mm DROP FOREIGN KEY FK_D5E34E885C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecuoption_ecu_mm DROP FOREIGN KEY FK_D5E34E881D17607B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmethode DROP FOREIGN KEY FK_E3D33709B143A831');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox DROP FOREIGN KEY FK_94FFA4A9F97439C6');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox_ecu_mm DROP FOREIGN KEY FK_BEF390425C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox_ecu_mm DROP FOREIGN KEY FK_BEF390421D17607B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP FOREIGN KEY FK_FEEACADD26018B2');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP FOREIGN KEY FK_256CA04BB016865A');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm DROP FOREIGN KEY FK_18D55ADA5C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm DROP FOREIGN KEY FK_18D55ADA1D17607B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclemodel DROP FOREIGN KEY FK_C429272C4EC65043');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries DROP FOREIGN KEY FK_F7D4BBEB7BD10DDB');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage DROP FOREIGN KEY FK_D1C0C69C9C7E4BE3');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_domain_model_ecu');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_domain_model_ecu_toolmethode_mm');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_ecuoption_ecu_mm');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_domain_model_ecumanufacturer');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_domain_model_ecuoption');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_domain_model_toolmanufacturer');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_domain_model_toolmethode');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_gearbox');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_gearbox_ecu_mm');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_gearboxtype');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehiclebrand');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehicleengine');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_domain_model_vehicleengine_ecu_mm');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehiclemodel');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehicleseries');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehiclestage');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehicletype');
    }
}
