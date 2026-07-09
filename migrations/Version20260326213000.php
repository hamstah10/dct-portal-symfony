<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326213000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create superadmin portal price list tables and seed values from portal PriceList page';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE portal_price_list (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(120) NOT NULL, name VARCHAR(190) NOT NULL, description LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX uniq_portal_price_list_code (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE portal_price_list_item (id INT AUTO_INCREMENT NOT NULL, price_list_id INT NOT NULL, section VARCHAR(20) NOT NULL, category VARCHAR(30) NOT NULL, code VARCHAR(80) NOT NULL, name VARCHAR(190) NOT NULL, description LONGTEXT DEFAULT NULL, credits INT NOT NULL, included TINYINT(1) NOT NULL, sort_order INT NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_4741B6DAE6B6597 (price_list_id), UNIQUE INDEX uniq_portal_price_list_item_key (price_list_id, section, category, code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE portal_price_list_item ADD CONSTRAINT FK_4741B6DAE6B6597 FOREIGN KEY (price_list_id) REFERENCES portal_price_list (id) ON DELETE CASCADE');

        $this->addSql("INSERT INTO portal_price_list (code, name, description, active) VALUES ('portal_chiptuningfile_default', 'Portal Preisliste (chiptuningfile.de)', 'Initial aus templates/portal/.../PriceList.jsx', 1)");

        $this->addSql("INSERT INTO portal_price_list_item (price_list_id, section, category, code, name, description, credits, included, sort_order, active)
            VALUES
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'stage', 'stage1', 'Stage 1', NULL, 100, 0, 10, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'stage', 'stage2', 'Stage 2', NULL, 100, 0, 20, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'stage', 'eco', 'Eco', NULL, 100, 0, 30, 1),

            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'gearbox', 'gearbox_stage1', 'Stage 1', NULL, 120, 0, 40, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'gearbox', 'gearbox_stage2', 'Stage 2', NULL, 160, 0, 50, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'gearbox', 'gearbox_stage3', 'Stage 3', NULL, 200, 0, 60, 1),

            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'dpf', 'DPF-Off', 'Partikelfilter', 0, 1, 100, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'egr', 'EGR-Off', 'AGR Ventil', 0, 1, 110, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'adblue', 'AdBlue-Off', 'SCR System', 0, 1, 120, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'vmax', 'Vmax-Off', 'Geschwindigkeit', 0, 1, 130, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'dtc', 'DTC-Off', 'Fehlercode', 25, 0, 140, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'startstop', 'Start-Stop-Off', 'Start-Stop', 20, 0, 150, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'pops', 'Pops & Bangs', 'Auspuff Knallen', 45, 0, 160, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'launch', 'Launch Control', 'Rennstart', 55, 0, 170, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'swirl', 'Swirl Flaps-Off', 'Drallklappen', 30, 0, 180, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'cat', 'Kat-Off', 'Katalysator', 35, 0, 190, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'o2', 'O2-Off', 'Lambdasonde', 25, 0, 200, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'option', 'hotstart', 'Hot Start Fix', 'Heissstart', 30, 0, 210, 1),

            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'dpf', 'DPF-Off', 'Partikelfilter', 40, 0, 300, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'egr', 'EGR-Off', 'AGR Ventil', 40, 0, 310, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'adblue', 'AdBlue-Off', 'SCR System', 40, 0, 320, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'vmax', 'Vmax-Off', 'Geschwindigkeit', 40, 0, 330, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'dtc', 'DTC-Off', 'Fehlercode', 25, 0, 340, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'startstop', 'Start-Stop-Off', 'Start-Stop', 20, 0, 350, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'pops', 'Pops & Bangs', 'Auspuff Knallen', 45, 0, 360, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'launch', 'Launch Control', 'Rennstart', 55, 0, 370, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'swirl', 'Swirl Flaps-Off', 'Drallklappen', 30, 0, 380, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'cat', 'Kat-Off', 'Katalysator', 35, 0, 390, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'o2', 'O2-Off', 'Lambdasonde', 25, 0, 400, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'pkw', 'options_only', 'hotstart', 'Hot Start Fix', 'Heissstart', 30, 0, 410, 1),

            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'stage', 'eco', 'Eco', NULL, 120, 0, 500, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'stage', 'tuning', 'Tuning', NULL, 150, 0, 510, 1),

            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'option', 'dpf', 'DPF-Off', 'Partikelfilter', 0, 1, 600, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'option', 'egr', 'EGR-Off', 'AGR Ventil', 0, 1, 610, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'option', 'adblue', 'AdBlue-Off', 'SCR System', 0, 1, 620, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'option', 'vmax', 'Vmax-Off', 'Geschwindigkeit', 0, 1, 630, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'option', 'dtc', 'DTC-Off', 'Fehlercode', 35, 0, 640, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'option', 'nox', 'NOx-Off', 'NOx Sensor', 50, 0, 650, 1),

            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'options_only', 'dpf', 'DPF-Off', 'Partikelfilter', 55, 0, 700, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'options_only', 'egr', 'EGR-Off', 'AGR Ventil', 55, 0, 710, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'options_only', 'adblue', 'AdBlue-Off', 'SCR System', 55, 0, 720, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'options_only', 'vmax', 'Vmax-Off', 'Geschwindigkeit', 55, 0, 730, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'options_only', 'dtc', 'DTC-Off', 'Fehlercode', 35, 0, 740, 1),
            ((SELECT id FROM portal_price_list WHERE code = 'portal_chiptuningfile_default'), 'lkw', 'options_only', 'nox', 'NOx-Off', 'NOx Sensor', 50, 0, 750, 1)
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal_price_list_item DROP FOREIGN KEY FK_4741B6DAE6B6597');
        $this->addSql('DROP TABLE portal_price_list_item');
        $this->addSql('DROP TABLE portal_price_list');
    }
}
