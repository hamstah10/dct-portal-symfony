<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709035852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create TFD Partner Wizard tables (client, tool, package, combination, lead + join tables)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tfd_partner_wizard_client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, client_id VARCHAR(80) NOT NULL, headline VARCHAR(180) NOT NULL, intro_text LONGTEXT NOT NULL, primary_color VARCHAR(20) NOT NULL, theme VARCHAR(20) NOT NULL, tool_required TINYINT NOT NULL, default_vehicle_price INT NOT NULL, allowed_domains JSON NOT NULL, enabled_services JSON NOT NULL, custom_labels JSON NOT NULL, privacy_notice LONGTEXT NOT NULL, tool_disclaimer LONGTEXT NOT NULL, notification_email VARCHAR(180) DEFAULT NULL, send_notification_email TINYINT NOT NULL, active TINYINT NOT NULL, UNIQUE INDEX UNIQ_EAB2ABD319EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_client_vehicletype (client_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_D939AF8A19EB6921 (client_id), INDEX IDX_D939AF8ADA3FD1FC (vehicle_type_id), PRIMARY KEY (client_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_client_toolmanufacturer (client_id INT NOT NULL, tool_manufacturer_id INT NOT NULL, INDEX IDX_1563B4B219EB6921 (client_id), INDEX IDX_1563B4B2CAE36CED (tool_manufacturer_id), PRIMARY KEY (client_id, tool_manufacturer_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_combination (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(140) NOT NULL, services JSON NOT NULL, priority INT NOT NULL, active TINYINT NOT NULL, client_id INT NOT NULL, INDEX IDX_64C4034319EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_combination_vehicletype (combination_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_AB9858997D949DCC (combination_id), INDEX IDX_AB985899DA3FD1FC (vehicle_type_id), PRIMARY KEY (combination_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_combination_tool (combination_id INT NOT NULL, tool_id INT NOT NULL, INDEX IDX_E70753827D949DCC (combination_id), INDEX IDX_E70753828F7B22CC (tool_id), PRIMARY KEY (combination_id, tool_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_lead (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, email VARCHAR(180) NOT NULL, company VARCHAR(180) DEFAULT NULL, phone VARCHAR(80) DEFAULT NULL, website VARCHAR(180) DEFAULT NULL, goal VARCHAR(80) DEFAULT NULL, experience VARCHAR(80) DEFAULT NULL, current_tool VARCHAR(120) DEFAULT NULL, desired_tool_level VARCHAR(80) DEFAULT NULL, services JSON NOT NULL, monthly_vehicle_count INT NOT NULL, recommendation_status VARCHAR(40) NOT NULL, recommended_package_key VARCHAR(120) DEFAULT NULL, recommended_package_title VARCHAR(180) DEFAULT NULL, recommended_package_text LONGTEXT DEFAULT NULL, recommended_tools JSON NOT NULL, tool_direction LONGTEXT DEFAULT NULL, next_step VARCHAR(120) DEFAULT NULL, estimated_monthly_revenue INT NOT NULL, payload JSON NOT NULL, status VARCHAR(40) NOT NULL, source VARCHAR(80) DEFAULT NULL, origin_domain VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, client_id INT DEFAULT NULL, main_vehicle_type_id INT DEFAULT NULL, INDEX IDX_BC2FCFDD19EB6921 (client_id), INDEX IDX_BC2FCFDD4CB87539 (main_vehicle_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_lead_vehicletype (lead_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_E518A28B55458D (lead_id), INDEX IDX_E518A28BDA3FD1FC (vehicle_type_id), PRIMARY KEY (lead_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_package (id INT AUTO_INCREMENT NOT NULL, package_key VARCHAR(80) NOT NULL, title VARCHAR(140) NOT NULL, description LONGTEXT NOT NULL, required_status VARCHAR(40) NOT NULL, min_vehicles_per_month INT NOT NULL, max_vehicles_per_month INT NOT NULL, required_experience JSON NOT NULL, required_goals JSON NOT NULL, features JSON NOT NULL, cta_label VARCHAR(120) NOT NULL, priority INT NOT NULL, active TINYINT NOT NULL, client_id INT NOT NULL, INDEX IDX_DA9EB72F19EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_package_vehicletype (package_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_4702799FF44CABFF (package_id), INDEX IDX_4702799FDA3FD1FC (vehicle_type_id), PRIMARY KEY (package_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_tool (id INT AUTO_INCREMENT NOT NULL, image_url VARCHAR(500) DEFAULT NULL, tool_key VARCHAR(80) NOT NULL, name VARCHAR(140) NOT NULL, description LONGTEXT DEFAULT NULL, recommended_for_services JSON NOT NULL, level VARCHAR(30) NOT NULL, priority INT NOT NULL, active TINYINT NOT NULL, client_id INT NOT NULL, shop_product_uid INT DEFAULT NULL, INDEX IDX_B44D90C719EB6921 (client_id), INDEX IDX_B44D90C761C3A409 (shop_product_uid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_tool_vehicletype (tool_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_86BBF8A18F7B22CC (tool_id), INDEX IDX_86BBF8A1DA3FD1FC (vehicle_type_id), PRIMARY KEY (tool_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_vehicletype ADD CONSTRAINT FK_D939AF8A19EB6921 FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_vehicletype ADD CONSTRAINT FK_D939AF8ADA3FD1FC FOREIGN KEY (vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_toolmanufacturer ADD CONSTRAINT FK_1563B4B219EB6921 FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_toolmanufacturer ADD CONSTRAINT FK_1563B4B2CAE36CED FOREIGN KEY (tool_manufacturer_id) REFERENCES tx_chiptuningdatabase_domain_model_toolmanufacturer (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination ADD CONSTRAINT FK_64C4034319EB6921 FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_vehicletype ADD CONSTRAINT FK_AB9858997D949DCC FOREIGN KEY (combination_id) REFERENCES tfd_partner_wizard_combination (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_vehicletype ADD CONSTRAINT FK_AB985899DA3FD1FC FOREIGN KEY (vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_tool ADD CONSTRAINT FK_E70753827D949DCC FOREIGN KEY (combination_id) REFERENCES tfd_partner_wizard_combination (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_tool ADD CONSTRAINT FK_E70753828F7B22CC FOREIGN KEY (tool_id) REFERENCES tfd_partner_wizard_tool (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead ADD CONSTRAINT FK_BC2FCFDD19EB6921 FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead ADD CONSTRAINT FK_BC2FCFDD4CB87539 FOREIGN KEY (main_vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead_vehicletype ADD CONSTRAINT FK_E518A28B55458D FOREIGN KEY (lead_id) REFERENCES tfd_partner_wizard_lead (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead_vehicletype ADD CONSTRAINT FK_E518A28BDA3FD1FC FOREIGN KEY (vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_package ADD CONSTRAINT FK_DA9EB72F19EB6921 FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_package_vehicletype ADD CONSTRAINT FK_4702799FF44CABFF FOREIGN KEY (package_id) REFERENCES tfd_partner_wizard_package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_package_vehicletype ADD CONSTRAINT FK_4702799FDA3FD1FC FOREIGN KEY (vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool ADD CONSTRAINT FK_B44D90C719EB6921 FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool ADD CONSTRAINT FK_B44D90C761C3A409 FOREIGN KEY (shop_product_uid) REFERENCES tx_tuningtoolshop_domain_model_product (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool_vehicletype ADD CONSTRAINT FK_86BBF8A18F7B22CC FOREIGN KEY (tool_id) REFERENCES tfd_partner_wizard_tool (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool_vehicletype ADD CONSTRAINT FK_86BBF8A1DA3FD1FC FOREIGN KEY (vehicle_type_id) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_vehicletype DROP FOREIGN KEY FK_D939AF8A19EB6921');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_vehicletype DROP FOREIGN KEY FK_D939AF8ADA3FD1FC');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_toolmanufacturer DROP FOREIGN KEY FK_1563B4B219EB6921');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_toolmanufacturer DROP FOREIGN KEY FK_1563B4B2CAE36CED');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination DROP FOREIGN KEY FK_64C4034319EB6921');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_vehicletype DROP FOREIGN KEY FK_AB9858997D949DCC');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_vehicletype DROP FOREIGN KEY FK_AB985899DA3FD1FC');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_tool DROP FOREIGN KEY FK_E70753827D949DCC');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_tool DROP FOREIGN KEY FK_E70753828F7B22CC');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead DROP FOREIGN KEY FK_BC2FCFDD19EB6921');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead DROP FOREIGN KEY FK_BC2FCFDD4CB87539');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead_vehicletype DROP FOREIGN KEY FK_E518A28B55458D');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead_vehicletype DROP FOREIGN KEY FK_E518A28BDA3FD1FC');
        $this->addSql('ALTER TABLE tfd_partner_wizard_package DROP FOREIGN KEY FK_DA9EB72F19EB6921');
        $this->addSql('ALTER TABLE tfd_partner_wizard_package_vehicletype DROP FOREIGN KEY FK_4702799FF44CABFF');
        $this->addSql('ALTER TABLE tfd_partner_wizard_package_vehicletype DROP FOREIGN KEY FK_4702799FDA3FD1FC');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool DROP FOREIGN KEY FK_B44D90C719EB6921');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool DROP FOREIGN KEY FK_B44D90C761C3A409');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool_vehicletype DROP FOREIGN KEY FK_86BBF8A18F7B22CC');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool_vehicletype DROP FOREIGN KEY FK_86BBF8A1DA3FD1FC');
        $this->addSql('DROP TABLE tfd_partner_wizard_client');
        $this->addSql('DROP TABLE tfd_partner_wizard_client_vehicletype');
        $this->addSql('DROP TABLE tfd_partner_wizard_client_toolmanufacturer');
        $this->addSql('DROP TABLE tfd_partner_wizard_combination');
        $this->addSql('DROP TABLE tfd_partner_wizard_combination_vehicletype');
        $this->addSql('DROP TABLE tfd_partner_wizard_combination_tool');
        $this->addSql('DROP TABLE tfd_partner_wizard_lead');
        $this->addSql('DROP TABLE tfd_partner_wizard_lead_vehicletype');
        $this->addSql('DROP TABLE tfd_partner_wizard_package');
        $this->addSql('DROP TABLE tfd_partner_wizard_package_vehicletype');
        $this->addSql('DROP TABLE tfd_partner_wizard_tool');
        $this->addSql('DROP TABLE tfd_partner_wizard_tool_vehicletype');
    }
}
