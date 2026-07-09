<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260403120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add global brand, ECU manufacturer, ECU, and ECU option master entities plus references from pool-specific records.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_brand_master (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_7C45707B989D9B62 (slug), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_ecu_manufacturer_master (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_91402C46989D9B62 (slug), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_ecu_master (uid INT AUTO_INCREMENT NOT NULL, manufacturer_master INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, control_unit_kind VARCHAR(10) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, INDEX IDX_2BEF42A097E4F688 (manufacturer_master), UNIQUE INDEX UNIQ_2BEF42A0989D9B62 (slug), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_ecu_option_master (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_663B922D989D9B62 (slug), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD master_brand INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecumanufacturer ADD master_manufacturer INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu ADD master_ecu INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption ADD master_option INT DEFAULT NULL');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_master ADD CONSTRAINT FK_2BEF42A097E4F688 FOREIGN KEY (manufacturer_master) REFERENCES tx_chiptuningdatabase_ecu_manufacturer_master (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand ADD CONSTRAINT FK_7FBEF0A7B66976EB FOREIGN KEY (master_brand) REFERENCES tx_chiptuningdatabase_brand_master (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecumanufacturer ADD CONSTRAINT FK_84BA334C936E8D48 FOREIGN KEY (master_manufacturer) REFERENCES tx_chiptuningdatabase_ecu_manufacturer_master (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu ADD CONSTRAINT FK_EEAF4B4BE68522AD FOREIGN KEY (master_ecu) REFERENCES tx_chiptuningdatabase_ecu_master (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption ADD CONSTRAINT FK_688A1E6F7FF2E1CF FOREIGN KEY (master_option) REFERENCES tx_chiptuningdatabase_ecu_option_master (uid) ON DELETE SET NULL');

        $this->addSql('CREATE INDEX IDX_7FBEF0A7B66976EB ON tx_chiptuningdatabase_vehiclebrand (master_brand)');
        $this->addSql('CREATE INDEX IDX_84BA334C936E8D48 ON tx_chiptuningdatabase_domain_model_ecumanufacturer (master_manufacturer)');
        $this->addSql('CREATE INDEX IDX_EEAF4B4BE68522AD ON tx_chiptuningdatabase_domain_model_ecu (master_ecu)');
        $this->addSql('CREATE INDEX IDX_688A1E6F7FF2E1CF ON tx_chiptuningdatabase_domain_model_ecuoption (master_option)');

        $this->addSql("INSERT INTO tx_chiptuningdatabase_brand_master (name, slug, image, description)
            SELECT grouped.name, grouped.slug, grouped.image, grouped.description
            FROM (
                SELECT
                    MIN(vb.name) AS name,
                    MAX(NULLIF(LOWER(TRIM(vb.slug)), '')) AS slug,
                    MAX(vb.image) AS image,
                    MAX(vb.description) AS description
                FROM tx_chiptuningdatabase_vehiclebrand vb
                GROUP BY COALESCE(NULLIF(LOWER(TRIM(vb.slug)), ''), CONCAT('name:', LOWER(TRIM(vb.name))))
            ) grouped");
        $this->addSql("UPDATE tx_chiptuningdatabase_vehiclebrand vb
            LEFT JOIN tx_chiptuningdatabase_brand_master bm
                ON (bm.slug IS NOT NULL AND bm.slug COLLATE utf8mb4_unicode_ci = NULLIF(LOWER(TRIM(vb.slug)), '') COLLATE utf8mb4_unicode_ci)
                OR (bm.slug IS NULL AND LOWER(TRIM(bm.name)) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(vb.name)) COLLATE utf8mb4_unicode_ci)
            SET vb.master_brand = bm.uid
            WHERE vb.master_brand IS NULL");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_ecu_manufacturer_master (name, slug, logo, description)
            SELECT grouped.name, grouped.slug, grouped.logo, grouped.description
            FROM (
                SELECT
                    em.name AS name,
                    MAX(NULLIF(LOWER(REPLACE(REPLACE(REPLACE(TRIM(em.name), '/', '-'), ' ', '-'), '--', '-')), '')) AS slug,
                    MAX(em.logo) AS logo,
                    MAX(em.description) AS description
                FROM tx_chiptuningdatabase_domain_model_ecumanufacturer em
                GROUP BY LOWER(TRIM(em.name)), em.name
            ) grouped");
        $this->addSql("UPDATE tx_chiptuningdatabase_domain_model_ecumanufacturer em
            INNER JOIN tx_chiptuningdatabase_ecu_manufacturer_master emm
                ON LOWER(TRIM(emm.name)) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(em.name)) COLLATE utf8mb4_unicode_ci
            SET em.master_manufacturer = emm.uid
            WHERE em.master_manufacturer IS NULL");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_ecu_option_master (name, slug, description)
            SELECT grouped.name, grouped.slug, grouped.description
            FROM (
                SELECT
                    MIN(eo.name) AS name,
                    MAX(NULLIF(LOWER(TRIM(eo.slug)), '')) AS slug,
                    MAX(eo.description) AS description
                FROM tx_chiptuningdatabase_domain_model_ecuoption eo
                GROUP BY COALESCE(NULLIF(LOWER(TRIM(eo.slug)), ''), CONCAT('name:', LOWER(TRIM(eo.name))))
            ) grouped");
        $this->addSql("UPDATE tx_chiptuningdatabase_domain_model_ecuoption eo
            LEFT JOIN tx_chiptuningdatabase_ecu_option_master eom
                ON (eom.slug IS NOT NULL AND eom.slug COLLATE utf8mb4_unicode_ci = NULLIF(LOWER(TRIM(eo.slug)), '') COLLATE utf8mb4_unicode_ci)
                OR (eom.slug IS NULL AND LOWER(TRIM(eom.name)) COLLATE utf8mb4_unicode_ci = LOWER(TRIM(eo.name)) COLLATE utf8mb4_unicode_ci)
            SET eo.master_option = eom.uid
            WHERE eo.master_option IS NULL");

        $this->addSql("INSERT INTO tx_chiptuningdatabase_ecu_master (manufacturer_master, name, slug, type, control_unit_kind, image)
            SELECT
                em.master_manufacturer AS manufacturer_master,
                e.name AS name,
                NULL AS slug,
                e.type AS type,
                e.control_unit_kind AS control_unit_kind,
                MAX(e.image) AS image
            FROM tx_chiptuningdatabase_domain_model_ecu e
            LEFT JOIN tx_chiptuningdatabase_domain_model_ecumanufacturer em ON em.uid = e.manufacturer
            GROUP BY em.master_manufacturer, e.name, e.type, e.control_unit_kind");
        $this->addSql("UPDATE tx_chiptuningdatabase_domain_model_ecu e
            LEFT JOIN tx_chiptuningdatabase_domain_model_ecumanufacturer em ON em.uid = e.manufacturer
            INNER JOIN tx_chiptuningdatabase_ecu_master ems
                ON ((ems.manufacturer_master IS NULL AND em.master_manufacturer IS NULL) OR ems.manufacturer_master = em.master_manufacturer)
                AND ems.name COLLATE utf8mb4_unicode_ci = e.name COLLATE utf8mb4_unicode_ci
                AND ((ems.type IS NULL AND e.type IS NULL) OR ems.type COLLATE utf8mb4_unicode_ci = e.type COLLATE utf8mb4_unicode_ci)
                AND ((ems.control_unit_kind IS NULL AND e.control_unit_kind IS NULL) OR ems.control_unit_kind COLLATE utf8mb4_unicode_ci = e.control_unit_kind COLLATE utf8mb4_unicode_ci)
            SET e.master_ecu = ems.uid
            WHERE e.master_ecu IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption DROP FOREIGN KEY FK_688A1E6F7FF2E1CF');
        $this->addSql('DROP INDEX IDX_688A1E6F7FF2E1CF ON tx_chiptuningdatabase_domain_model_ecuoption');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption DROP master_option');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP FOREIGN KEY FK_EEAF4B4BE68522AD');
        $this->addSql('DROP INDEX IDX_EEAF4B4BE68522AD ON tx_chiptuningdatabase_domain_model_ecu');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP master_ecu');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecumanufacturer DROP FOREIGN KEY FK_84BA334C936E8D48');
        $this->addSql('DROP INDEX IDX_84BA334C936E8D48 ON tx_chiptuningdatabase_domain_model_ecumanufacturer');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecumanufacturer DROP master_manufacturer');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP FOREIGN KEY FK_7FBEF0A7B66976EB');
        $this->addSql('DROP INDEX IDX_7FBEF0A7B66976EB ON tx_chiptuningdatabase_vehiclebrand');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand DROP master_brand');

        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_master DROP FOREIGN KEY FK_2BEF42A097E4F688');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_ecu_option_master');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_ecu_master');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_ecu_manufacturer_master');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_brand_master');
    }
}
