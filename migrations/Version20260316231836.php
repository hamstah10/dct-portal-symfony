<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260316231836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE marketing_ad_size (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, width INT NOT NULL, height INT NOT NULL, category_id INT NOT NULL, INDEX IDX_E4E22D8012469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE marketing_category (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE marketing_ad_size ADD CONSTRAINT FK_E4E22D8012469DE2 FOREIGN KEY (category_id) REFERENCES marketing_category (id)');
        // Removed problematic drops - tables should be preserved
        $this->addSql('ALTER TABLE admin_page_access_user_group RENAME INDEX idx_admin_page_access_user_group_access TO IDX_BEB1C58BA70CEE24');
        $this->addSql('ALTER TABLE admin_page_access_user_group RENAME INDEX idx_admin_page_access_user_group_group TO IDX_BEB1C58B1ED93D47');
        $this->addSql('ALTER TABLE ai_project CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE ai_project_resource CHANGE last_indexed_at last_indexed_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE ai_project_resource RENAME INDEX idx_ai_project_resource_project TO IDX_985FF872166D1F9C');
        $this->addSql('ALTER TABLE ai_project_resource RENAME INDEX idx_ai_project_resource_media TO IDX_985FF872ABB37F3');
        $this->addSql('DROP INDEX IDX_API_TOKEN_ACTIVE ON api_token');
        $this->addSql('DROP INDEX IDX_API_TOKEN_GUELTIG_BIS ON api_token');
        $this->addSql('ALTER TABLE api_token CHANGE active active TINYINT NOT NULL');
        $this->addSql('ALTER TABLE api_token RENAME INDEX uniq_7ba2f5ebb3bc57da TO UNIQ_7BA2F5EB9FE292D4');
        $this->addSql('ALTER TABLE api_token RENAME INDEX idx_7ba2f5eb9395c3f3 TO IDX_7BA2F5EB9D4738BC');
        $this->addSql('ALTER TABLE dyno_customer CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE dyno_customer RENAME INDEX idx_dyno_customer_vehicle TO IDX_1363702B545317D1');
        $this->addSql('ALTER TABLE dyno_dtc CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX IDX_DYNO_SPRINT_TRIP ON dyno_sprint');
        $this->addSql('DROP INDEX IDX_DYNO_SPRINT_TYPE ON dyno_sprint');
        $this->addSql('ALTER TABLE dyno_sprint CHANGE created_at created_at DATETIME NOT NULL, CHANGE distance_m distance_m NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE dyno_sprint RENAME INDEX idx_dyno_sprint_vehicle TO IDX_97637F6F545317D1');
        $this->addSql('ALTER TABLE dyno_trip CHANGE start_time start_time DATETIME NOT NULL, CHANGE end_time end_time DATETIME NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE dyno_trip RENAME INDEX idx_dyno_trip_vehicle TO IDX_9BE3B6B7545317D1');
        $this->addSql('ALTER TABLE dyno_vehicle CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE email_konto RENAME INDEX idx_email_konto_firma TO IDX_2DF318A505AEC11');
        $this->addSql('ALTER TABLE email_konto_label RENAME INDEX idx_email_konto_label_account TO IDX_DA46675D5AD29393');
        $this->addSql('ALTER TABLE email_konto_label RENAME INDEX idx_email_konto_label_label TO IDX_DA46675D38DF3070');
        $this->addSql('ALTER TABLE kunde CHANGE credits credits INT NOT NULL, CHANGE brevo_synced_at brevo_synced_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE kunde_credit_transaction CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE kunde_credit_transaction RENAME INDEX idx_kct_kunde TO IDX_B4446F8E9D4738BC');
        $this->addSql('ALTER TABLE kunde_credit_transaction RENAME INDEX idx_kct_reversal_of TO IDX_B4446F8E29A0BB4E');
        $this->addSql('ALTER TABLE kunde_credit_transaction RENAME INDEX idx_kct_created_by TO IDX_B4446F8EB03A8386');
        $this->addSql('ALTER TABLE lexware_invoice CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE lexware_invoice RENAME INDEX uniq_70bea5b4b2c71d3b TO UNIQ_9C5E38D070843094');
        $this->addSql('ALTER TABLE lexware_invoice RENAME INDEX idx_70bea5b44bad5a4b TO IDX_9C5E38D09D4738BC');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine RENAME INDEX idx_vehicleengine_image_media_asset TO IDX_256CA04B9D2DE099');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine RENAME INDEX idx_vehicleengine_glb_media_asset TO IDX_256CA04B38F3F359');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option RENAME INDEX idx_profitcalc_option_vehicletype TO IDX_6F25D329FE436475');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option RENAME INDEX idx_profitcalc_option_icon_media TO IDX_6F25D329CF3FADD0');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype CHANGE sorting sorting INT NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype RENAME INDEX idx_profitcalc_vehicletype_calculator TO IDX_CA19E58C247990C2');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype RENAME INDEX idx_profitcalc_vehicletype_icon_media TO IDX_CA19E58CCF3FADD0');
        $this->addSql('DROP INDEX IDX_DTC_CODE_FEHLERCODE ON tx_tuningdtccodes_domain_model_dtccode');
        $this->addSql('ALTER TABLE tx_tuningdtccodes_domain_model_dtccode RENAME INDEX idx_dtc_code_category TO IDX_3504211864C19C1');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_category ADD CONSTRAINT FK_1305E78A3D8E604F FOREIGN KEY (parent) REFERENCES tx_tuningtoolshop_domain_model_category (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_category RENAME INDEX idx_tts_category_parent TO IDX_1305E78A3D8E604F');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_download ADD CONSTRAINT FK_6D537C3BD34A04AD FOREIGN KEY (product) REFERENCES tx_tuningtoolshop_domain_model_product (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_download RENAME INDEX idx_tts_download_product TO IDX_6D537C3BD34A04AD');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_order ADD CONSTRAINT FK_6851CEF37B61A1F6 FOREIGN KEY (payment_method) REFERENCES tx_tuningtoolshop_domain_model_paymentmethod (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_order ADD CONSTRAINT FK_6851CEF37503FF2F FOREIGN KEY (shipping_method) REFERENCES tx_tuningtoolshop_domain_model_shippingmethod (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_order RENAME INDEX idx_tts_order_payment TO IDX_6851CEF37B61A1F6');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_order RENAME INDEX idx_tts_order_shipping TO IDX_6851CEF37503FF2F');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product ADD CONSTRAINT FK_409906F03D0AE6DC FOREIGN KEY (manufacturer) REFERENCES tx_tuningtoolshop_domain_model_manufacturer (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product ADD CONSTRAINT FK_409906F08E81BA76 FOREIGN KEY (tax) REFERENCES tx_tuningtoolshop_domain_model_tax (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product RENAME INDEX idx_tts_product_manufacturer TO IDX_409906F03D0AE6DC');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product RENAME INDEX idx_tts_product_tax TO IDX_409906F08E81BA76');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_category_mm DROP sorting, DROP sorting_foreign');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_category_mm ADD CONSTRAINT FK_C3CCECC25C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_tuningtoolshop_domain_model_product (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_category_mm ADD CONSTRAINT FK_C3CCECC21D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_tuningtoolshop_domain_model_category (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_category_mm RENAME INDEX idx_tts_product_category_local TO IDX_C3CCECC25C4C8485');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_category_mm RENAME INDEX idx_tts_product_category_foreign TO IDX_C3CCECC21D17607B');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_tag_mm DROP sorting, DROP sorting_foreign');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_tag_mm ADD CONSTRAINT FK_803031395C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_tuningtoolshop_domain_model_product (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_tag_mm ADD CONSTRAINT FK_803031391D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_tuningtoolshop_domain_model_tag (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_tag_mm RENAME INDEX idx_tts_product_tag_local TO IDX_803031395C4C8485');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_tag_mm RENAME INDEX idx_tts_product_tag_foreign TO IDX_803031391D17607B');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_relatedproducts_mm DROP sorting, DROP sorting_foreign');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_relatedproducts_mm ADD CONSTRAINT FK_B062DFD15C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_tuningtoolshop_domain_model_product (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_relatedproducts_mm ADD CONSTRAINT FK_B062DFD11D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_tuningtoolshop_domain_model_product (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_relatedproducts_mm RENAME INDEX idx_tts_product_related_local TO IDX_B062DFD15C4C8485');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_relatedproducts_mm RENAME INDEX idx_tts_product_related_foreign TO IDX_B062DFD11D17607B');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_shippingmethod_mm DROP sorting, DROP sorting_foreign');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_shippingmethod_mm ADD CONSTRAINT FK_6A74C1305C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_tuningtoolshop_domain_model_product (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_shippingmethod_mm ADD CONSTRAINT FK_6A74C1301D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_tuningtoolshop_domain_model_shippingmethod (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_shippingmethod_mm RENAME INDEX idx_tts_product_ship_local TO IDX_6A74C1305C4C8485');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_shippingmethod_mm RENAME INDEX idx_tts_product_ship_foreign TO IDX_6A74C1301D17607B');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productdeliveryscope ADD CONSTRAINT FK_4555D950C3E28308 FOREIGN KEY (lieferumfang) REFERENCES tx_tuningtoolshop_domain_model_product (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productdeliveryscope RENAME INDEX idx_tts_delivery_product TO IDX_4555D950C3E28308');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productlink ADD CONSTRAINT FK_62188327D34A04AD FOREIGN KEY (product) REFERENCES tx_tuningtoolshop_domain_model_product (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productlink RENAME INDEX idx_tts_productlink_product TO IDX_62188327D34A04AD');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productoption ADD CONSTRAINT FK_7086CFE7D34A04AD FOREIGN KEY (product) REFERENCES tx_tuningtoolshop_domain_model_product (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productoption RENAME INDEX idx_tts_productoption_product TO IDX_7086CFE7D34A04AD');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productoptionvalue ADD CONSTRAINT FK_632AA36F38FA4114 FOREIGN KEY (product_option) REFERENCES tx_tuningtoolshop_domain_model_productoption (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productoptionvalue RENAME INDEX idx_tts_optionvalue_option TO IDX_632AA36F38FA4114');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_video ADD CONSTRAINT FK_E1BF8747D34A04AD FOREIGN KEY (product) REFERENCES tx_tuningtoolshop_domain_model_product (uid)');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_video RENAME INDEX idx_tts_video_product TO IDX_E1BF8747D34A04AD');
        $this->addSql('ALTER TABLE user CHANGE last_login_at last_login_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX idx_user_gruppe TO IDX_8D93D649E92667BF');
        $this->addSql('ALTER TABLE vin_decoder_cache CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE vin_decoder_cache RENAME INDEX uniq_1d9b0f3cb7597c1d TO UNIQ_3F28B4DBB1085141');
        $this->addSql('DROP INDEX IDX_WEBSITE_SNIFFER_ACTIVE ON website_sniffer');
        $this->addSql('DROP INDEX IDX_WEBSITE_SNIFFER_LAST_RUN_AT ON website_sniffer');
        $this->addSql('ALTER TABLE website_sniffer_page RENAME INDEX idx_website_sniffer_page_run TO IDX_BAEA162E84E3FEC4');
        $this->addSql('DROP INDEX IDX_WEBSITE_SNIFFER_RUN_RUN_AT ON website_sniffer_run');
        $this->addSql('DROP INDEX IDX_WEBSITE_SNIFFER_RUN_STATUS ON website_sniffer_run');
        $this->addSql('ALTER TABLE website_sniffer_run CHANGE processed_pages processed_pages INT NOT NULL, CHANGE total_pages total_pages INT NOT NULL');
        $this->addSql('ALTER TABLE website_sniffer_run RENAME INDEX idx_website_sniffer_run_sniffer TO IDX_61BCE0289ADEAEDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ai_chat_session (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, messages LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, project_id INT DEFAULT NULL, INDEX IDX_AI_CHAT_SESSION_PROJECT (project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_dalle_image (id INT AUTO_INCREMENT NOT NULL, prompt LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, revised_prompt LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, model VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, size VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, quality VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, style VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, response_format VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, image_path VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image_url LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, metadata LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, image_count INT DEFAULT 1 NOT NULL, aspect_ratio VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'square\' NOT NULL COLLATE `utf8mb4_unicode_ci`, image_paths LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image_urls LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, project_id INT DEFAULT NULL, INDEX IDX_AI_DALLE_IMAGE_PROJECT (project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_firecrawl_run (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(16) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, url TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, instruction LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, model VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, crawl_depth INT NOT NULL, crawl_limit INT NOT NULL, response LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, metadata LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, map_links LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, status VARCHAR(16) CHARACTER SET utf8mb4 DEFAULT \'success\' NOT NULL COLLATE `utf8mb4_unicode_ci`, progress_label VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, progress_current INT DEFAULT 4 NOT NULL, progress_max INT DEFAULT 4 NOT NULL, started_at INT DEFAULT NULL, finished_at INT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_project_agent_run (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, system_prompt LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, prompt LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, response LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, metadata LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, INDEX IDX_AI_PROJECT_AGENT_PROJECT (project_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_project_vector_document (id VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, project_id INT NOT NULL, resource_id INT DEFAULT NULL, content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, vector_data LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, metadata LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, INDEX IDX_AI_PROJECT_VECTOR_PROJECT (project_id), INDEX IDX_AI_PROJECT_VECTOR_RESOURCE (resource_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ai_vehicle_ocr_run (id INT AUTO_INCREMENT NOT NULL, source_type VARCHAR(16) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, original_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, mime_type VARCHAR(120) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, document_path VARCHAR(512) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, preview_path VARCHAR(512) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, model VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, extracted LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, raw_text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, raw_response LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, criteria LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, results LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, error LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at INT NOT NULL, updated_at INT NOT NULL, preview_paths LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE oeffnungszeit (id INT AUTO_INCREMENT NOT NULL, firma_id INT NOT NULL, wochentag INT NOT NULL, geoeffnet_von TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', geoeffnet_bis TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', geschlossen TINYINT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_oeffnungszeit_firma_tag (firma_id, wochentag), INDEX IDX_5B4C01C2505AEC11 (firma_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE oeffnungszeit_ausnahme (id INT AUTO_INCREMENT NOT NULL, firma_id INT NOT NULL, datum DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', geoeffnet_von TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', geoeffnet_bis TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', geschlossen TINYINT NOT NULL, hinweis VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_oeffnungszeit_ausnahme_firma_datum (firma_id, datum), INDEX IDX_D5EB3549505AEC11 (firma_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE urlaub (id INT AUTO_INCREMENT NOT NULL, firma_id INT NOT NULL, start_datum DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', end_datum DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', hinweis VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_urlaub_firma_ende (firma_id, end_datum), INDEX idx_urlaub_firma_start (firma_id, start_datum), INDEX IDX_A02EEA15505AEC11 (firma_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE ai_chat_session ADD CONSTRAINT `FK_AI_CHAT_SESSION_PROJECT` FOREIGN KEY (project_id) REFERENCES ai_project (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_dalle_image ADD CONSTRAINT `FK_AI_DALLE_IMAGE_PROJECT` FOREIGN KEY (project_id) REFERENCES ai_project (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_project_agent_run ADD CONSTRAINT `FK_AI_PROJECT_AGENT_PROJECT` FOREIGN KEY (project_id) REFERENCES ai_project (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_project_vector_document ADD CONSTRAINT `FK_AI_PROJECT_VECTOR_PROJECT` FOREIGN KEY (project_id) REFERENCES ai_project (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_project_vector_document ADD CONSTRAINT `FK_AI_PROJECT_VECTOR_RESOURCE` FOREIGN KEY (resource_id) REFERENCES ai_project_resource (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oeffnungszeit ADD CONSTRAINT `FK_OEFFNUNGSZEIT_FIRMA` FOREIGN KEY (firma_id) REFERENCES firma (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oeffnungszeit_ausnahme ADD CONSTRAINT `FK_OEFFNUNGSZEIT_AUSNAHME_FIRMA` FOREIGN KEY (firma_id) REFERENCES firma (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE urlaub ADD CONSTRAINT `FK_URLAUB_FIRMA` FOREIGN KEY (firma_id) REFERENCES firma (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE marketing_ad_size DROP FOREIGN KEY FK_E4E22D8012469DE2');
        $this->addSql('DROP TABLE marketing_ad_size');
        $this->addSql('DROP TABLE marketing_category');
        $this->addSql('ALTER TABLE admin_page_access_user_group RENAME INDEX idx_beb1c58ba70cee24 TO IDX_ADMIN_PAGE_ACCESS_USER_GROUP_ACCESS');
        $this->addSql('ALTER TABLE admin_page_access_user_group RENAME INDEX idx_beb1c58b1ed93d47 TO IDX_ADMIN_PAGE_ACCESS_USER_GROUP_GROUP');
        $this->addSql('ALTER TABLE ai_project CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE ai_project_resource CHANGE last_indexed_at last_indexed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE ai_project_resource RENAME INDEX idx_985ff872abb37f3 TO IDX_AI_PROJECT_RESOURCE_MEDIA');
        $this->addSql('ALTER TABLE ai_project_resource RENAME INDEX idx_985ff872166d1f9c TO IDX_AI_PROJECT_RESOURCE_PROJECT');
        $this->addSql('ALTER TABLE api_token CHANGE active active TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('CREATE INDEX IDX_API_TOKEN_ACTIVE ON api_token (active)');
        $this->addSql('CREATE INDEX IDX_API_TOKEN_GUELTIG_BIS ON api_token (gueltig_bis)');
        $this->addSql('ALTER TABLE api_token RENAME INDEX idx_7ba2f5eb9d4738bc TO IDX_7BA2F5EB9395C3F3');
        $this->addSql('ALTER TABLE api_token RENAME INDEX uniq_7ba2f5eb9fe292d4 TO UNIQ_7BA2F5EBB3BC57DA');
        $this->addSql('ALTER TABLE dyno_customer CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE dyno_customer RENAME INDEX idx_1363702b545317d1 TO IDX_DYNO_CUSTOMER_VEHICLE');
        $this->addSql('ALTER TABLE dyno_dtc CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE dyno_sprint CHANGE distance_m distance_m NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX IDX_DYNO_SPRINT_TRIP ON dyno_sprint (trip_id)');
        $this->addSql('CREATE INDEX IDX_DYNO_SPRINT_TYPE ON dyno_sprint (sprint_type)');
        $this->addSql('ALTER TABLE dyno_sprint RENAME INDEX idx_97637f6f545317d1 TO IDX_DYNO_SPRINT_VEHICLE');
        $this->addSql('ALTER TABLE dyno_trip CHANGE start_time start_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE end_time end_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE dyno_trip RENAME INDEX idx_9be3b6b7545317d1 TO IDX_DYNO_TRIP_VEHICLE');
        $this->addSql('ALTER TABLE dyno_vehicle CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE email_konto RENAME INDEX idx_2df318a505aec11 TO IDX_EMAIL_KONTO_FIRMA');
        $this->addSql('ALTER TABLE email_konto_label RENAME INDEX idx_da46675d5ad29393 TO IDX_EMAIL_KONTO_LABEL_ACCOUNT');
        $this->addSql('ALTER TABLE email_konto_label RENAME INDEX idx_da46675d38df3070 TO IDX_EMAIL_KONTO_LABEL_LABEL');
        $this->addSql('ALTER TABLE kunde CHANGE credits credits INT DEFAULT 0 NOT NULL, CHANGE brevo_synced_at brevo_synced_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE kunde_credit_transaction CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE kunde_credit_transaction RENAME INDEX idx_b4446f8eb03a8386 TO IDX_KCT_CREATED_BY');
        $this->addSql('ALTER TABLE kunde_credit_transaction RENAME INDEX idx_b4446f8e9d4738bc TO IDX_KCT_KUNDE');
        $this->addSql('ALTER TABLE kunde_credit_transaction RENAME INDEX idx_b4446f8e29a0bb4e TO IDX_KCT_REVERSAL_OF');
        $this->addSql('ALTER TABLE lexware_invoice CHANGE created_at created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE lexware_invoice RENAME INDEX idx_9c5e38d09d4738bc TO IDX_70BEA5B44BAD5A4B');
        $this->addSql('ALTER TABLE lexware_invoice RENAME INDEX uniq_9c5e38d070843094 TO UNIQ_70BEA5B4B2C71D3B');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine RENAME INDEX idx_256ca04b38f3f359 TO IDX_VEHICLEENGINE_GLB_MEDIA_ASSET');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine RENAME INDEX idx_256ca04b9d2de099 TO IDX_VEHICLEENGINE_IMAGE_MEDIA_ASSET');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option RENAME INDEX idx_6f25d329cf3fadd0 TO IDX_PROFITCALC_OPTION_ICON_MEDIA');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_option RENAME INDEX idx_6f25d329fe436475 TO IDX_PROFITCALC_OPTION_VEHICLETYPE');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype CHANGE sorting sorting INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype RENAME INDEX idx_ca19e58c247990c2 TO IDX_PROFITCALC_VEHICLETYPE_CALCULATOR');
        $this->addSql('ALTER TABLE tx_chiptuningprofitcalculator_domain_model_vehicletype RENAME INDEX idx_ca19e58ccf3fadd0 TO IDX_PROFITCALC_VEHICLETYPE_ICON_MEDIA');
        $this->addSql('CREATE INDEX IDX_DTC_CODE_FEHLERCODE ON tx_tuningdtccodes_domain_model_dtccode (fehlercode)');
        $this->addSql('ALTER TABLE tx_tuningdtccodes_domain_model_dtccode RENAME INDEX idx_3504211864c19c1 TO IDX_DTC_CODE_CATEGORY');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_category DROP FOREIGN KEY FK_1305E78A3D8E604F');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_category RENAME INDEX idx_1305e78a3d8e604f TO IDX_TTS_CATEGORY_PARENT');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_download DROP FOREIGN KEY FK_6D537C3BD34A04AD');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_download RENAME INDEX idx_6d537c3bd34a04ad TO IDX_TTS_DOWNLOAD_PRODUCT');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_order DROP FOREIGN KEY FK_6851CEF37B61A1F6');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_order DROP FOREIGN KEY FK_6851CEF37503FF2F');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_order RENAME INDEX idx_6851cef37b61a1f6 TO IDX_TTS_ORDER_PAYMENT');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_order RENAME INDEX idx_6851cef37503ff2f TO IDX_TTS_ORDER_SHIPPING');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product DROP FOREIGN KEY FK_409906F03D0AE6DC');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product DROP FOREIGN KEY FK_409906F08E81BA76');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product RENAME INDEX idx_409906f03d0ae6dc TO IDX_TTS_PRODUCT_MANUFACTURER');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product RENAME INDEX idx_409906f08e81ba76 TO IDX_TTS_PRODUCT_TAX');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productdeliveryscope DROP FOREIGN KEY FK_4555D950C3E28308');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productdeliveryscope RENAME INDEX idx_4555d950c3e28308 TO IDX_TTS_DELIVERY_PRODUCT');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productlink DROP FOREIGN KEY FK_62188327D34A04AD');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productlink RENAME INDEX idx_62188327d34a04ad TO IDX_TTS_PRODUCTLINK_PRODUCT');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productoption DROP FOREIGN KEY FK_7086CFE7D34A04AD');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productoption RENAME INDEX idx_7086cfe7d34a04ad TO IDX_TTS_PRODUCTOPTION_PRODUCT');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productoptionvalue DROP FOREIGN KEY FK_632AA36F38FA4114');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_productoptionvalue RENAME INDEX idx_632aa36f38fa4114 TO IDX_TTS_OPTIONVALUE_OPTION');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_video DROP FOREIGN KEY FK_E1BF8747D34A04AD');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_video RENAME INDEX idx_e1bf8747d34a04ad TO IDX_TTS_VIDEO_PRODUCT');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_category_mm DROP FOREIGN KEY FK_C3CCECC25C4C8485');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_category_mm DROP FOREIGN KEY FK_C3CCECC21D17607B');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_category_mm ADD sorting INT NOT NULL, ADD sorting_foreign INT NOT NULL');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_category_mm RENAME INDEX idx_c3ccecc21d17607b TO IDX_TTS_PRODUCT_CATEGORY_FOREIGN');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_category_mm RENAME INDEX idx_c3ccecc25c4c8485 TO IDX_TTS_PRODUCT_CATEGORY_LOCAL');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_relatedproducts_mm DROP FOREIGN KEY FK_B062DFD15C4C8485');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_relatedproducts_mm DROP FOREIGN KEY FK_B062DFD11D17607B');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_relatedproducts_mm ADD sorting INT NOT NULL, ADD sorting_foreign INT NOT NULL');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_relatedproducts_mm RENAME INDEX idx_b062dfd11d17607b TO IDX_TTS_PRODUCT_RELATED_FOREIGN');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_relatedproducts_mm RENAME INDEX idx_b062dfd15c4c8485 TO IDX_TTS_PRODUCT_RELATED_LOCAL');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_shippingmethod_mm DROP FOREIGN KEY FK_6A74C1305C4C8485');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_shippingmethod_mm DROP FOREIGN KEY FK_6A74C1301D17607B');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_shippingmethod_mm ADD sorting INT NOT NULL, ADD sorting_foreign INT NOT NULL');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_shippingmethod_mm RENAME INDEX idx_6a74c1301d17607b TO IDX_TTS_PRODUCT_SHIP_FOREIGN');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_shippingmethod_mm RENAME INDEX idx_6a74c1305c4c8485 TO IDX_TTS_PRODUCT_SHIP_LOCAL');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_tag_mm DROP FOREIGN KEY FK_803031395C4C8485');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_tag_mm DROP FOREIGN KEY FK_803031391D17607B');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_tag_mm ADD sorting INT NOT NULL, ADD sorting_foreign INT NOT NULL');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_tag_mm RENAME INDEX idx_803031391d17607b TO IDX_TTS_PRODUCT_TAG_FOREIGN');
        $this->addSql('ALTER TABLE tx_tuningtoolshop_product_tag_mm RENAME INDEX idx_803031395c4c8485 TO IDX_TTS_PRODUCT_TAG_LOCAL');
        $this->addSql('ALTER TABLE user CHANGE last_login_at last_login_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user RENAME INDEX idx_8d93d649e92667bf TO IDX_USER_GRUPPE');
        $this->addSql('ALTER TABLE vin_decoder_cache CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE vin_decoder_cache RENAME INDEX uniq_3f28b4dbb1085141 TO UNIQ_1D9B0F3CB7597C1D');
        $this->addSql('CREATE INDEX IDX_WEBSITE_SNIFFER_ACTIVE ON website_sniffer (active)');
        $this->addSql('CREATE INDEX IDX_WEBSITE_SNIFFER_LAST_RUN_AT ON website_sniffer (last_run_at)');
        $this->addSql('ALTER TABLE website_sniffer_page RENAME INDEX idx_baea162e84e3fec4 TO IDX_WEBSITE_SNIFFER_PAGE_RUN');
        $this->addSql('ALTER TABLE website_sniffer_run CHANGE processed_pages processed_pages INT DEFAULT 0 NOT NULL, CHANGE total_pages total_pages INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE INDEX IDX_WEBSITE_SNIFFER_RUN_RUN_AT ON website_sniffer_run (run_at)');
        $this->addSql('CREATE INDEX IDX_WEBSITE_SNIFFER_RUN_STATUS ON website_sniffer_run (status)');
        $this->addSql('ALTER TABLE website_sniffer_run RENAME INDEX idx_61bce0289adeaedb TO IDX_WEBSITE_SNIFFER_RUN_SNIFFER');
    }
}
