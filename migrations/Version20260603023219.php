<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260603023219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_einbau_hinweis (uid INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, active TINYINT DEFAULT 1 NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, ecu_option_master_id INT DEFAULT NULL, INDEX IDX_C72F61B14C151D2D (ecu_option_master_id), PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_einbau_hinweis_engine_mm (einbau_hinweis_id INT NOT NULL, vehicle_engine_id INT NOT NULL, INDEX IDX_B460E4A81E65B47B (einbau_hinweis_id), INDEX IDX_B460E4A81033D958 (vehicle_engine_id), PRIMARY KEY (einbau_hinweis_id, vehicle_engine_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_einbau_hinweis_model_mm (einbau_hinweis_id INT NOT NULL, vehicle_model_id INT NOT NULL, INDEX IDX_3C439CA51E65B47B (einbau_hinweis_id), INDEX IDX_3C439CA5A467B873 (vehicle_model_id), PRIMARY KEY (einbau_hinweis_id, vehicle_model_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_einbau_hinweis_media_mm (einbau_hinweis_id INT NOT NULL, media_asset_id INT NOT NULL, INDEX IDX_AFA9F6371E65B47B (einbau_hinweis_id), INDEX IDX_AFA9F637ABB37F3 (media_asset_id), PRIMARY KEY (einbau_hinweis_id, media_asset_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis ADD CONSTRAINT FK_C72F61B14C151D2D FOREIGN KEY (ecu_option_master_id) REFERENCES tx_chiptuningdatabase_ecu_option_master (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_engine_mm ADD CONSTRAINT FK_B460E4A81E65B47B FOREIGN KEY (einbau_hinweis_id) REFERENCES tx_chiptuningdatabase_einbau_hinweis (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_engine_mm ADD CONSTRAINT FK_B460E4A81033D958 FOREIGN KEY (vehicle_engine_id) REFERENCES tx_chiptuningdatabase_vehicleengine (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_model_mm ADD CONSTRAINT FK_3C439CA51E65B47B FOREIGN KEY (einbau_hinweis_id) REFERENCES tx_chiptuningdatabase_einbau_hinweis (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_model_mm ADD CONSTRAINT FK_3C439CA5A467B873 FOREIGN KEY (vehicle_model_id) REFERENCES tx_chiptuningdatabase_vehiclemodel (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_media_mm ADD CONSTRAINT FK_AFA9F6371E65B47B FOREIGN KEY (einbau_hinweis_id) REFERENCES tx_chiptuningdatabase_einbau_hinweis (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_media_mm ADD CONSTRAINT FK_AFA9F637ABB37F3 FOREIGN KEY (media_asset_id) REFERENCES media_asset (id)');
        $this->addSql('ALTER TABLE ai_project_vector_document DROP FOREIGN KEY `FK_AI_PROJECT_VECTOR_PROJECT`');
        $this->addSql('ALTER TABLE ai_project_vector_document DROP FOREIGN KEY `FK_AI_PROJECT_VECTOR_RESOURCE`');
        $this->addSql('DROP TABLE ai_chat_session');
        $this->addSql('DROP TABLE ai_dalle_image');
        $this->addSql('DROP TABLE ai_firecrawl_run');
        $this->addSql('DROP TABLE ai_image_analysis');
        $this->addSql('DROP TABLE ai_pdf_analysis');
        $this->addSql('DROP TABLE ai_project_agent_run');
        $this->addSql('DROP TABLE ai_project_vector_document');
        $this->addSql('DROP TABLE ai_vehicle_ocr_run');
        $this->addSql('ALTER TABLE api_token RENAME INDEX idx_7ba2f5ebe2a20a48 TO IDX_7BA2F5EB3FBE3CFB');
        $this->addSql('ALTER TABLE crm_lead CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE showcase_password_expires_at showcase_password_expires_at DATETIME DEFAULT NULL, CHANGE showcase_password_generated_at showcase_password_generated_at DATETIME DEFAULT NULL, CHANGE newsletter newsletter TINYINT NOT NULL');
        $this->addSql('ALTER TABLE kunde CHANGE has_dyno has_dyno TINYINT NOT NULL');
        $this->addSql('ALTER TABLE portal CHANGE created_at created_at DATETIME NOT NULL, CHANGE portal_type portal_type VARCHAR(40) NOT NULL, CHANGE portal_enabled portal_enabled TINYINT NOT NULL, CHANGE website_enabled website_enabled TINYINT NOT NULL, CHANGE customer_registration_enabled customer_registration_enabled TINYINT NOT NULL, CHANGE menu_background_color menu_background_color VARCHAR(7) NOT NULL, CHANGE content_background_color content_background_color VARCHAR(7) NOT NULL, CHANGE custom_design_enabled custom_design_enabled TINYINT NOT NULL, CHANGE theme_preset theme_preset VARCHAR(40) NOT NULL');
        $this->addSql('ALTER TABLE portal RENAME INDEX uniq_3c1d4d3d20cd3f71 TO UNIQ_BAE93F0199EEAB2');
        $this->addSql('ALTER TABLE portal RENAME INDEX uniq_3c1d4d3d239bcbef TO UNIQ_BAE93F045D1D450');
        $this->addSql('ALTER TABLE portal RENAME INDEX uniq_3c1d4d3dd4b08d00 TO UNIQ_BAE93F0CD22FA89');
        $this->addSql('ALTER TABLE portal RENAME INDEX idx_3c1d4d3d8f02bf9d TO IDX_BAE93F0505AEC11');
        $this->addSql('ALTER TABLE portal RENAME INDEX idx_3c1c9c3a1c6a2a0a TO IDX_BAE93F0A6D7BF5A');
        $this->addSql('ALTER TABLE portal_zahlungsart RENAME INDEX idx_d6b45d0361233d7c TO IDX_9B931752B887E1DD');
        $this->addSql('ALTER TABLE portal_zahlungsart RENAME INDEX idx_d6b45d03e02a7284 TO IDX_9B931752F09E3E8C');
        $this->addSql('DROP INDEX UNIQ_95B8BFAF9C6228A ON portal_password_reset_token');
        $this->addSql('ALTER TABLE portal_password_reset_token CHANGE requested_at requested_at DATETIME NOT NULL, CHANGE expires_at expires_at DATETIME NOT NULL, CHANGE used_at used_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE portal_password_reset_token RENAME INDEX idx_95b8bfafc5e27a72 TO IDX_3D65A09A9D4738BC');
        $this->addSql('ALTER TABLE portal_price_list_item RENAME INDEX idx_4741b6dae6b6597 TO IDX_5EF9089E5688DED7');
        $this->addSql('DROP INDEX IDX_388CEB28346AF503 ON portal_upload_job');
        $this->addSql('DROP INDEX IDX_388CEB28BF7AF263 ON portal_upload_job');
        $this->addSql('ALTER TABLE portal_upload_job CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE processed_at processed_at DATETIME DEFAULT NULL, CHANGE returned_at returned_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE portal_upload_job RENAME INDEX idx_388ceb28c5e27a72 TO IDX_7006A63D9D4738BC');
        $this->addSql('ALTER TABLE project_idea_card CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE target_date target_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE project_todo_card CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE target_date target_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE scraper_import_log DROP FOREIGN KEY `FK_SCRAPER_IMPORT_POOL`');
        $this->addSql('ALTER TABLE scraper_import_log CHANGE source source VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE scraper_import_log ADD CONSTRAINT FK_E5D5CE2EE3612736 FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE scraper_import_log RENAME INDEX idx_scraper_import_pool TO IDX_E5D5CE2EE3612736');
        $this->addSql('ALTER TABLE scraper_run CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX IDX_BRAND_MASTER_ACTIVE ON tx_chiptuningdatabase_brand_master');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master CHANGE active active TINYINT NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master RENAME INDEX uniq_7c45707b989d9b62 TO UNIQ_9285C96A989D9B62');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master RENAME INDEX idx_brand_master_image_media_asset TO IDX_9285C96A9D2DE099');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master RENAME INDEX idx_7c45707ba7a0eb7e TO IDX_9285C96AFE436475');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu RENAME INDEX idx_eeaf4b4bd9dd56d0 TO IDX_888235EFE3612736');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu RENAME INDEX idx_eeaf4b4be68522ad TO IDX_888235EFDE9596B4');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu RENAME INDEX idx_ecu_vehicle_engine TO IDX_888235EF41761B5E');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecumanufacturer RENAME INDEX idx_84ba334c936e8d48 TO IDX_614BE670A0D299C3');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption RENAME INDEX idx_688a1e6fd9dd56d0 TO IDX_7C23AA99E3612736');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption RENAME INDEX idx_688a1e6f7ff2e1cf TO IDX_7C23AA99AB66A79E');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_manufacturer_master RENAME INDEX uniq_91402c46989d9b62 TO UNIQ_D122E587989D9B62');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_master RENAME INDEX uniq_2bef42a0989d9b62 TO UNIQ_83EB23F8989D9B62');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_master RENAME INDEX idx_2bef42a097e4f688 TO IDX_83EB23F830B5ED26');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_hint CHANGE scraped_at scraped_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master RENAME INDEX uniq_663b922d989d9b62 TO UNIQ_10CB0D91989D9B62');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master RENAME INDEX idx_ecu_option_master_icon_media TO IDX_10CB0D91CF3FADD0');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox RENAME INDEX idx_f4e3ee0fd9dd56d0 TO IDX_94FFA4A9E3612736');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context RENAME INDEX uniq_43d4ae4931a6d0bc TO UNIQ_50235344EAF41CDA');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context RENAME INDEX idx_43d4ae467585e27 TO IDX_5023534416E11EA9');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm DROP FOREIGN KEY `FK_58A30681442812D3`');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm DROP FOREIGN KEY `FK_58A30681C067CF9D`');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm ADD CONSTRAINT FK_140D1D6C5C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_vehicle_data_context (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm ADD CONSTRAINT FK_140D1D6C1D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm RENAME INDEX idx_58a30681442812d3 TO IDX_140D1D6C5C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm RENAME INDEX idx_58a30681c067cf9d TO IDX_140D1D6C1D17607B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool RENAME INDEX uniq_a77a5ed5d7a605b7 TO UNIQ_88EEA522F7533879');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm DROP FOREIGN KEY `FK_6760EE8B442812D3`');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm DROP FOREIGN KEY `FK_6760EE8BC067CF9D`');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm ADD CONSTRAINT FK_AFAC080D5C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm ADD CONSTRAINT FK_AFAC080D1D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_vehicletype (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm RENAME INDEX idx_6760ee8b442812d3 TO IDX_AFAC080D5C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm RENAME INDEX idx_6760ee8bc067cf9d TO IDX_AFAC080D1D17607B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings CHANGE active active TINYINT NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings RENAME INDEX idx_404a3b7cd9dd56d0 TO IDX_DF5299F0E3612736');
        $this->addSql('DROP INDEX IDX_VEHICLEBRAND_SCRAPER_SOURCE ON tx_chiptuningdatabase_vehiclebrand');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand RENAME INDEX idx_7fbef0a7d9dd56d0 TO IDX_FEEACADE3612736');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand RENAME INDEX idx_7fbef0a7b66976eb TO IDX_FEEACAD28179589');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine RENAME INDEX idx_f4ed206ad9dd56d0 TO IDX_256CA04BE3612736');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclemodel RENAME INDEX idx_38068742d9dd56d0 TO IDX_C429272CE3612736');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries RENAME INDEX idx_1f174b4fd9dd56d0 TO IDX_F7D4BBEBE3612736');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage RENAME INDEX idx_1c1b6d8fd9dd56d0 TO IDX_D1C0C69CE3612736');
        $this->addSql('ALTER TABLE website_widget_config CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE select_background_color select_background_color VARCHAR(20) NOT NULL, CHANGE select_open_background_color select_open_background_color VARCHAR(20) NOT NULL, CHANGE type_icon_color type_icon_color VARCHAR(10) NOT NULL, CHANGE recipient_email_mode recipient_email_mode VARCHAR(20) NOT NULL, CHANGE show_performance_chart show_performance_chart TINYINT NOT NULL');
        $this->addSql('ALTER TABLE website_widget_config RENAME INDEX uniq_7d23b311a34eafe TO UNIQ_E25FE4B750E1AC8');
        $this->addSql('ALTER TABLE website_widget_config RENAME INDEX idx_7d23b3119d4738bc TO IDX_E25FE4B9D4738BC');
        $this->addSql('ALTER TABLE website_widget_config RENAME INDEX idx_7d23b3117ba2f5eb TO IDX_E25FE4B92E52D36');
        $this->addSql('ALTER TABLE website_widget_config RENAME INDEX idx_7d23b311c067cf9d TO IDX_E25FE4BE3612736');
        $this->addSql('ALTER TABLE website_widget_config RENAME INDEX idx_website_widget_vehicle_type TO IDX_E25FE4BFE436475');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ai_chat_session (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, messages LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, INDEX IDX_PROJECT_ID (project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_dalle_image (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, prompt LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, revised_prompt LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, model VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, size VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, quality VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, style VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, response_format VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, image_count INT DEFAULT 1, aspect_ratio VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'square\' COLLATE `utf8mb4_unicode_ci`, image_path VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image_url VARCHAR(2048) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image_paths JSON DEFAULT NULL, image_urls JSON DEFAULT NULL, metadata JSON DEFAULT NULL, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, INDEX IDX_CREATED_AT (created_at), INDEX IDX_PROJECT_ID (project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_firecrawl_run (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, url LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, instruction LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, model VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, crawl_depth INT DEFAULT 0, crawl_limit INT DEFAULT 0, response LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, metadata JSON DEFAULT NULL, map_links JSON DEFAULT NULL, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, status VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, progress_label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, progress_current INT DEFAULT 0, progress_max INT DEFAULT 4, started_at INT DEFAULT NULL, finished_at INT DEFAULT NULL, created_at INT NOT NULL, updated_at INT NOT NULL, INDEX IDX_CREATED_AT (created_at), INDEX IDX_STATUS (status), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_image_analysis (id INT AUTO_INCREMENT NOT NULL, image_path VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, model VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, prompt LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, response LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, INDEX IDX_CREATED_AT (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_pdf_analysis (id INT AUTO_INCREMENT NOT NULL, file_path VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, model VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, prompt LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, response LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, INDEX IDX_CREATED_AT (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_project_agent_run (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, system_prompt LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, prompt LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, response LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, metadata LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, INDEX IDX_AI_PROJECT_AGENT_PROJECT (project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_project_vector_document (id VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, project_id INT NOT NULL, resource_id INT DEFAULT NULL, content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, vector_data LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, metadata LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, INDEX IDX_AI_PROJECT_VECTOR_PROJECT (project_id), INDEX IDX_AI_PROJECT_VECTOR_RESOURCE (resource_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_vehicle_ocr_run (id INT AUTO_INCREMENT NOT NULL, source_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, original_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, mime_type VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, document_path VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, preview_path VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, preview_paths JSON DEFAULT NULL, model VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, extracted JSON DEFAULT NULL, raw_text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, raw_response LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, criteria JSON DEFAULT NULL, results JSON DEFAULT NULL, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, INDEX IDX_CREATED_AT (created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE ai_project_vector_document ADD CONSTRAINT `FK_AI_PROJECT_VECTOR_PROJECT` FOREIGN KEY (project_id) REFERENCES ai_project (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_project_vector_document ADD CONSTRAINT `FK_AI_PROJECT_VECTOR_RESOURCE` FOREIGN KEY (resource_id) REFERENCES ai_project_resource (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis DROP FOREIGN KEY FK_C72F61B14C151D2D');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_engine_mm DROP FOREIGN KEY FK_B460E4A81E65B47B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_engine_mm DROP FOREIGN KEY FK_B460E4A81033D958');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_model_mm DROP FOREIGN KEY FK_3C439CA51E65B47B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_model_mm DROP FOREIGN KEY FK_3C439CA5A467B873');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_media_mm DROP FOREIGN KEY FK_AFA9F6371E65B47B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_media_mm DROP FOREIGN KEY FK_AFA9F637ABB37F3');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_einbau_hinweis');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_einbau_hinweis_engine_mm');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_einbau_hinweis_model_mm');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_einbau_hinweis_media_mm');
        $this->addSql('ALTER TABLE api_token RENAME INDEX idx_7ba2f5eb3fbe3cfb TO IDX_7BA2F5EBE2A20A48');
        $this->addSql('ALTER TABLE crm_lead CHANGE newsletter newsletter TINYINT DEFAULT 0 NOT NULL, CHANGE showcase_password_expires_at showcase_password_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE showcase_password_generated_at showcase_password_generated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE kunde CHANGE has_dyno has_dyno TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE portal CHANGE portal_type portal_type VARCHAR(40) DEFAULT \'brand_suite\' NOT NULL, CHANGE menu_background_color menu_background_color VARCHAR(7) DEFAULT \'#111111\' NOT NULL, CHANGE content_background_color content_background_color VARCHAR(7) DEFAULT \'#050505\' NOT NULL, CHANGE custom_design_enabled custom_design_enabled TINYINT DEFAULT 0 NOT NULL, CHANGE theme_preset theme_preset VARCHAR(40) DEFAULT \'original\' NOT NULL, CHANGE portal_enabled portal_enabled TINYINT DEFAULT 1 NOT NULL, CHANGE website_enabled website_enabled TINYINT DEFAULT 1 NOT NULL, CHANGE customer_registration_enabled customer_registration_enabled TINYINT DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE portal RENAME INDEX idx_bae93f0a6d7bf5a TO IDX_3C1C9C3A1C6A2A0A');
        $this->addSql('ALTER TABLE portal RENAME INDEX idx_bae93f0505aec11 TO IDX_3C1D4D3D8F02BF9D');
        $this->addSql('ALTER TABLE portal RENAME INDEX uniq_bae93f0199eeab2 TO UNIQ_3C1D4D3D20CD3F71');
        $this->addSql('ALTER TABLE portal RENAME INDEX uniq_bae93f045d1d450 TO UNIQ_3C1D4D3D239BCBEF');
        $this->addSql('ALTER TABLE portal RENAME INDEX uniq_bae93f0cd22fa89 TO UNIQ_3C1D4D3DD4B08D00');
        $this->addSql('ALTER TABLE portal_password_reset_token CHANGE requested_at requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE expires_at expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE used_at used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_95B8BFAF9C6228A ON portal_password_reset_token (token_hash)');
        $this->addSql('ALTER TABLE portal_password_reset_token RENAME INDEX idx_3d65a09a9d4738bc TO IDX_95B8BFAFC5E27A72');
        $this->addSql('ALTER TABLE portal_price_list_item RENAME INDEX idx_5ef9089e5688ded7 TO IDX_4741B6DAE6B6597');
        $this->addSql('ALTER TABLE portal_upload_job CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE processed_at processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE returned_at returned_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_388CEB28346AF503 ON portal_upload_job (brand_key)');
        $this->addSql('CREATE INDEX IDX_388CEB28BF7AF263 ON portal_upload_job (status)');
        $this->addSql('ALTER TABLE portal_upload_job RENAME INDEX idx_7006a63d9d4738bc TO IDX_388CEB28C5E27A72');
        $this->addSql('ALTER TABLE portal_zahlungsart RENAME INDEX idx_9b931752b887e1dd TO IDX_D6B45D0361233D7C');
        $this->addSql('ALTER TABLE portal_zahlungsart RENAME INDEX idx_9b931752f09e3e8c TO IDX_D6B45D03E02A7284');
        $this->addSql('ALTER TABLE project_idea_card CHANGE target_date target_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE project_todo_card CHANGE target_date target_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE scraper_import_log DROP FOREIGN KEY FK_E5D5CE2EE3612736');
        $this->addSql('ALTER TABLE scraper_import_log CHANGE source source VARCHAR(32) DEFAULT \'root\' NOT NULL');
        $this->addSql('ALTER TABLE scraper_import_log ADD CONSTRAINT `FK_SCRAPER_IMPORT_POOL` FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE scraper_import_log RENAME INDEX idx_e5d5ce2ee3612736 TO IDX_SCRAPER_IMPORT_POOL');
        $this->addSql('ALTER TABLE scraper_run CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master CHANGE active active TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('CREATE INDEX IDX_BRAND_MASTER_ACTIVE ON tx_chiptuningdatabase_brand_master (active)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master RENAME INDEX idx_9285c96afe436475 TO IDX_7C45707BA7A0EB7E');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master RENAME INDEX idx_9285c96a9d2de099 TO IDX_BRAND_MASTER_IMAGE_MEDIA_ASSET');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master RENAME INDEX uniq_9285c96a989d9b62 TO UNIQ_7C45707B989D9B62');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu RENAME INDEX idx_888235ef41761b5e TO IDX_ECU_VEHICLE_ENGINE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu RENAME INDEX idx_888235efe3612736 TO IDX_EEAF4B4BD9DD56D0');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu RENAME INDEX idx_888235efde9596b4 TO IDX_EEAF4B4BE68522AD');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecumanufacturer RENAME INDEX idx_614be670a0d299c3 TO IDX_84BA334C936E8D48');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption RENAME INDEX idx_7c23aa99ab66a79e TO IDX_688A1E6F7FF2E1CF');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption RENAME INDEX idx_7c23aa99e3612736 TO IDX_688A1E6FD9DD56D0');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_manufacturer_master RENAME INDEX uniq_d122e587989d9b62 TO UNIQ_91402C46989D9B62');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_master RENAME INDEX idx_83eb23f830b5ed26 TO IDX_2BEF42A097E4F688');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_master RENAME INDEX uniq_83eb23f8989d9b62 TO UNIQ_2BEF42A0989D9B62');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_hint CHANGE scraped_at scraped_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master RENAME INDEX idx_10cb0d91cf3fadd0 TO IDX_ECU_OPTION_MASTER_ICON_MEDIA');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_ecu_option_master RENAME INDEX uniq_10cb0d91989d9b62 TO UNIQ_663B922D989D9B62');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox RENAME INDEX idx_94ffa4a9e3612736 TO IDX_F4E3EE0FD9DD56D0');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context RENAME INDEX idx_5023534416e11ea9 TO IDX_43D4AE467585E27');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context RENAME INDEX uniq_50235344eaf41cda TO UNIQ_43D4AE4931A6D0BC');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm DROP FOREIGN KEY FK_140D1D6C5C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm DROP FOREIGN KEY FK_140D1D6C1D17607B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm ADD CONSTRAINT `FK_58A30681442812D3` FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_vehicle_data_context (uid) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm ADD CONSTRAINT `FK_58A30681C067CF9D` FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm RENAME INDEX idx_140d1d6c5c4c8485 TO IDX_58A30681442812D3');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_context_pool_mm RENAME INDEX idx_140d1d6c1d17607b TO IDX_58A30681C067CF9D');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool RENAME INDEX uniq_88eea522f7533879 TO UNIQ_A77A5ED5D7A605B7');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm DROP FOREIGN KEY FK_AFAC080D5C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm DROP FOREIGN KEY FK_AFAC080D1D17607B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm ADD CONSTRAINT `FK_6760EE8B442812D3` FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm ADD CONSTRAINT `FK_6760EE8BC067CF9D` FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm RENAME INDEX idx_afac080d5c4c8485 TO IDX_6760EE8B442812D3');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_data_pool_type_mm RENAME INDEX idx_afac080d1d17607b TO IDX_6760EE8BC067CF9D');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings CHANGE active active TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicle_frontend_settings RENAME INDEX idx_df5299f0e3612736 TO IDX_404A3B7CD9DD56D0');
        $this->addSql('CREATE INDEX IDX_VEHICLEBRAND_SCRAPER_SOURCE ON tx_chiptuningdatabase_vehiclebrand (scraper_source)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand RENAME INDEX idx_feeacad28179589 TO IDX_7FBEF0A7B66976EB');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclebrand RENAME INDEX idx_feeacade3612736 TO IDX_7FBEF0A7D9DD56D0');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine RENAME INDEX idx_256ca04be3612736 TO IDX_F4ED206AD9DD56D0');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclemodel RENAME INDEX idx_c429272ce3612736 TO IDX_38068742D9DD56D0');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleseries RENAME INDEX idx_f7d4bbebe3612736 TO IDX_1F174B4FD9DD56D0');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehiclestage RENAME INDEX idx_d1c0c69ce3612736 TO IDX_1C1B6D8FD9DD56D0');
        $this->addSql('ALTER TABLE website_widget_config CHANGE recipient_email_mode recipient_email_mode VARCHAR(20) DEFAULT \'customer\' NOT NULL, CHANGE select_background_color select_background_color VARCHAR(20) DEFAULT \'#ffffff\' NOT NULL, CHANGE select_open_background_color select_open_background_color VARCHAR(20) DEFAULT \'#f3f4f6\' NOT NULL, CHANGE show_performance_chart show_performance_chart TINYINT DEFAULT 1 NOT NULL, CHANGE type_icon_color type_icon_color VARCHAR(10) DEFAULT \'white\' NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE website_widget_config RENAME INDEX idx_e25fe4b92e52d36 TO IDX_7D23B3117BA2F5EB');
        $this->addSql('ALTER TABLE website_widget_config RENAME INDEX idx_e25fe4b9d4738bc TO IDX_7D23B3119D4738BC');
        $this->addSql('ALTER TABLE website_widget_config RENAME INDEX idx_e25fe4be3612736 TO IDX_7D23B311C067CF9D');
        $this->addSql('ALTER TABLE website_widget_config RENAME INDEX idx_e25fe4bfe436475 TO IDX_WEBSITE_WIDGET_VEHICLE_TYPE');
        $this->addSql('ALTER TABLE website_widget_config RENAME INDEX uniq_e25fe4b750e1ac8 TO UNIQ_7D23B311A34EAFE');
    }
}
