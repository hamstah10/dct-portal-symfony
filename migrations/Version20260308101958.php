<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308101958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE support_ticket_attachment (id INT AUTO_INCREMENT NOT NULL, original_filename VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, mime_type VARCHAR(50) NOT NULL, filesize INT NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, ticket_id INT NOT NULL, INDEX IDX_D2EBF699700047D2 (ticket_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE support_ticket_attachment ADD CONSTRAINT FK_D2EBF699700047D2 FOREIGN KEY (ticket_id) REFERENCES support_ticket (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX IDX_AUFTRAG_CREATED_AT ON auftrag');
        $this->addSql('ALTER TABLE auftrag RENAME INDEX idx_auftrag_kunde TO IDX_E128A3589D4738BC');
        $this->addSql('ALTER TABLE credit_package RENAME INDEX idx_credit_package_category TO IDX_E4EC00BA12469DE2');
        $this->addSql('ALTER TABLE kunde CHANGE gesperrt gesperrt TINYINT NOT NULL');
        $this->addSql('DROP INDEX IDX_70D929D0D38A89 ON media_asset');
        $this->addSql('DROP INDEX IDX_70D929DCA24DAD6 ON media_asset');
        $this->addSql('DROP INDEX IDX_70D929DCAFD4D3A5 ON media_asset');
        $this->addSql('ALTER TABLE media_asset CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE media_asset RENAME INDEX idx_media_asset_folder TO IDX_1DB69EED162CB942');
        $this->addSql('DROP INDEX IDX_MEDIA_FOLDER_ACTIVE ON media_folder');
        $this->addSql('DROP INDEX IDX_MEDIA_FOLDER_SORTING ON media_folder');
        $this->addSql('ALTER TABLE media_folder CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE media_folder RENAME INDEX idx_media_folder_parent TO IDX_50DB9313727ACA70');
        $this->addSql('DROP INDEX IDX_SUPPORT_TICKET_STATUS ON support_ticket');
        $this->addSql('DROP INDEX IDX_SUPPORT_TICKET_UPDATED_AT ON support_ticket');
        $this->addSql('ALTER TABLE support_ticket CHANGE message_count message_count INT NOT NULL');
        $this->addSql('ALTER TABLE support_ticket RENAME INDEX idx_support_ticket_firma TO IDX_1F5A4D53505AEC11');
        $this->addSql('ALTER TABLE support_ticket RENAME INDEX idx_support_ticket_kunde TO IDX_1F5A4D539D4738BC');
        $this->addSql('DROP INDEX IDX_SUPPORT_TICKET_MESSAGE_CREATED_AT ON support_ticket_message');
        $this->addSql('ALTER TABLE support_ticket_message CHANGE sender_name sender_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE support_ticket_message RENAME INDEX idx_support_ticket_message_ticket TO IDX_73251A5C700047D2');
        $this->addSql('ALTER TABLE support_ticket_message RENAME INDEX idx_support_ticket_message_kunde TO IDX_73251A5C9D4738BC');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption RENAME INDEX idx_3dfd385b2d711e27 TO IDX_7C23AA99CF3FADD0');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm RENAME INDEX idx_646d9e955c4c8485 TO IDX_6633F095C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm RENAME INDEX idx_646d9e951d17607b TO IDX_6633F091D17607B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox RENAME INDEX idx_94ffa4a943dc12a TO IDX_94FFA4A9CF3FADD0');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings CHANGE layout_switcher_enabled layout_switcher_enabled TINYINT NOT NULL, CHANGE performance_glb_autorotate performance_glb_autorotate TINYINT NOT NULL, CHANGE performance_show_prices performance_show_prices TINYINT NOT NULL, CHANGE performance_show_logo performance_show_logo TINYINT NOT NULL, CHANGE performance_show_only_stage1 performance_show_only_stage1 TINYINT NOT NULL, CHANGE minimal_glb_autorotate minimal_glb_autorotate TINYINT NOT NULL, CHANGE minimal_show_prices minimal_show_prices TINYINT NOT NULL, CHANGE minimal_show_logo minimal_show_logo TINYINT NOT NULL, CHANGE minimal_show_only_stage1 minimal_show_only_stage1 TINYINT NOT NULL, CHANGE contrast_glb_autorotate contrast_glb_autorotate TINYINT NOT NULL, CHANGE contrast_show_prices contrast_show_prices TINYINT NOT NULL, CHANGE contrast_show_logo contrast_show_logo TINYINT NOT NULL, CHANGE contrast_show_only_stage1 contrast_show_only_stage1 TINYINT NOT NULL, CHANGE layout3_glb_autorotate layout3_glb_autorotate TINYINT NOT NULL, CHANGE layout3_show_prices layout3_show_prices TINYINT NOT NULL, CHANGE layout3_show_logo layout3_show_logo TINYINT NOT NULL, CHANGE layout3_show_only_stage1 layout3_show_only_stage1 TINYINT NOT NULL, CHANGE layout4_glb_autorotate layout4_glb_autorotate TINYINT NOT NULL, CHANGE layout4_show_prices layout4_show_prices TINYINT NOT NULL, CHANGE layout4_show_logo layout4_show_logo TINYINT NOT NULL, CHANGE layout4_show_only_stage1 layout4_show_only_stage1 TINYINT NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand CHANGE show_in_top_menu show_in_top_menu TINYINT NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine CHANGE media_display_mode media_display_mode VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage RENAME INDEX idx_d1c0c69c2d711e27 TO IDX_D1C0C69CCF3FADD0');
        $this->addSql('DROP INDEX IDX_VEH_USAGE_CREATED_AT ON vehicle_usage_event');
        $this->addSql('DROP INDEX IDX_VEH_USAGE_ENGINE_ID ON vehicle_usage_event');
        $this->addSql('DROP INDEX IDX_VEH_USAGE_EVENT_TYPE ON vehicle_usage_event');
        $this->addSql('DROP INDEX IDX_VEH_USAGE_SESSION_ID ON vehicle_usage_event');
        $this->addSql('DROP INDEX IDX_VEH_USAGE_STAGE_ID ON vehicle_usage_event');
        $this->addSql('ALTER TABLE vehicle_usage_event CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE support_ticket_attachment DROP FOREIGN KEY FK_D2EBF699700047D2');
        $this->addSql('DROP TABLE support_ticket_attachment');
        $this->addSql('CREATE INDEX IDX_AUFTRAG_CREATED_AT ON auftrag (created_at)');
        $this->addSql('ALTER TABLE auftrag RENAME INDEX idx_e128a3589d4738bc TO IDX_AUFTRAG_KUNDE');
        $this->addSql('ALTER TABLE credit_package RENAME INDEX idx_e4ec00ba12469de2 TO IDX_CREDIT_PACKAGE_CATEGORY');
        $this->addSql('ALTER TABLE kunde CHANGE gesperrt gesperrt TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE media_asset CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_70D929D0D38A89 ON media_asset (sorting)');
        $this->addSql('CREATE INDEX IDX_70D929DCA24DAD6 ON media_asset (active)');
        $this->addSql('CREATE INDEX IDX_70D929DCAFD4D3A5 ON media_asset (media_type)');
        $this->addSql('ALTER TABLE media_asset RENAME INDEX idx_1db69eed162cb942 TO IDX_MEDIA_ASSET_FOLDER');
        $this->addSql('ALTER TABLE media_folder CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_MEDIA_FOLDER_ACTIVE ON media_folder (active)');
        $this->addSql('CREATE INDEX IDX_MEDIA_FOLDER_SORTING ON media_folder (sorting)');
        $this->addSql('ALTER TABLE media_folder RENAME INDEX idx_50db9313727aca70 TO IDX_MEDIA_FOLDER_PARENT');
        $this->addSql('ALTER TABLE support_ticket CHANGE message_count message_count INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX IDX_SUPPORT_TICKET_STATUS ON support_ticket (status)');
        $this->addSql('CREATE INDEX IDX_SUPPORT_TICKET_UPDATED_AT ON support_ticket (updated_at)');
        $this->addSql('ALTER TABLE support_ticket RENAME INDEX idx_1f5a4d53505aec11 TO IDX_SUPPORT_TICKET_FIRMA');
        $this->addSql('ALTER TABLE support_ticket RENAME INDEX idx_1f5a4d539d4738bc TO IDX_SUPPORT_TICKET_KUNDE');
        $this->addSql('ALTER TABLE support_ticket_message CHANGE sender_name sender_name VARCHAR(120) NOT NULL');
        $this->addSql('CREATE INDEX IDX_SUPPORT_TICKET_MESSAGE_CREATED_AT ON support_ticket_message (created_at)');
        $this->addSql('ALTER TABLE support_ticket_message RENAME INDEX idx_73251a5c9d4738bc TO IDX_SUPPORT_TICKET_MESSAGE_KUNDE');
        $this->addSql('ALTER TABLE support_ticket_message RENAME INDEX idx_73251a5c700047d2 TO IDX_SUPPORT_TICKET_MESSAGE_TICKET');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption RENAME INDEX idx_7c23aa99cf3fadd0 TO IDX_3DFD385B2D711E27');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox RENAME INDEX idx_94ffa4a9cf3fadd0 TO IDX_94FFA4A943DC12A');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm RENAME INDEX idx_6633f091d17607b TO IDX_646D9E951D17607B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm RENAME INDEX idx_6633f095c4c8485 TO IDX_646D9E955C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings CHANGE layout_switcher_enabled layout_switcher_enabled TINYINT DEFAULT 0 NOT NULL, CHANGE performance_glb_autorotate performance_glb_autorotate TINYINT DEFAULT 0 NOT NULL, CHANGE performance_show_prices performance_show_prices TINYINT DEFAULT 1 NOT NULL, CHANGE performance_show_logo performance_show_logo TINYINT DEFAULT 1 NOT NULL, CHANGE performance_show_only_stage1 performance_show_only_stage1 TINYINT DEFAULT 0 NOT NULL, CHANGE minimal_glb_autorotate minimal_glb_autorotate TINYINT DEFAULT 0 NOT NULL, CHANGE minimal_show_prices minimal_show_prices TINYINT DEFAULT 1 NOT NULL, CHANGE minimal_show_logo minimal_show_logo TINYINT DEFAULT 1 NOT NULL, CHANGE minimal_show_only_stage1 minimal_show_only_stage1 TINYINT DEFAULT 0 NOT NULL, CHANGE contrast_glb_autorotate contrast_glb_autorotate TINYINT DEFAULT 0 NOT NULL, CHANGE contrast_show_prices contrast_show_prices TINYINT DEFAULT 1 NOT NULL, CHANGE contrast_show_logo contrast_show_logo TINYINT DEFAULT 1 NOT NULL, CHANGE contrast_show_only_stage1 contrast_show_only_stage1 TINYINT DEFAULT 0 NOT NULL, CHANGE layout3_glb_autorotate layout3_glb_autorotate TINYINT DEFAULT 0 NOT NULL, CHANGE layout3_show_prices layout3_show_prices TINYINT DEFAULT 1 NOT NULL, CHANGE layout3_show_logo layout3_show_logo TINYINT DEFAULT 1 NOT NULL, CHANGE layout3_show_only_stage1 layout3_show_only_stage1 TINYINT DEFAULT 0 NOT NULL, CHANGE layout4_glb_autorotate layout4_glb_autorotate TINYINT DEFAULT 0 NOT NULL, CHANGE layout4_show_prices layout4_show_prices TINYINT DEFAULT 1 NOT NULL, CHANGE layout4_show_logo layout4_show_logo TINYINT DEFAULT 1 NOT NULL, CHANGE layout4_show_only_stage1 layout4_show_only_stage1 TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand CHANGE show_in_top_menu show_in_top_menu TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine CHANGE media_display_mode media_display_mode VARCHAR(20) DEFAULT \'image\' NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage RENAME INDEX idx_d1c0c69ccf3fadd0 TO IDX_D1C0C69C2D711E27');
        $this->addSql('ALTER TABLE vehicle_usage_event CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_VEH_USAGE_CREATED_AT ON vehicle_usage_event (created_at)');
        $this->addSql('CREATE INDEX IDX_VEH_USAGE_ENGINE_ID ON vehicle_usage_event (engine_id)');
        $this->addSql('CREATE INDEX IDX_VEH_USAGE_EVENT_TYPE ON vehicle_usage_event (event_type)');
        $this->addSql('CREATE INDEX IDX_VEH_USAGE_SESSION_ID ON vehicle_usage_event (session_id)');
        $this->addSql('CREATE INDEX IDX_VEH_USAGE_STAGE_ID ON vehicle_usage_event (stage_id)');
    }
}
