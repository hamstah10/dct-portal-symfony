<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704001000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create full TFD Partner Wizard tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tfd_partner_wizard_client (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, client_id VARCHAR(80) NOT NULL, headline VARCHAR(180) NOT NULL, intro_text LONGTEXT NOT NULL, primary_color VARCHAR(20) NOT NULL, theme VARCHAR(20) NOT NULL, tool_required TINYINT(1) NOT NULL, default_vehicle_price INT NOT NULL, allowed_domains JSON NOT NULL, enabled_vehicle_categories JSON NOT NULL, enabled_services JSON NOT NULL, custom_labels JSON NOT NULL, privacy_notice LONGTEXT NOT NULL, tool_disclaimer LONGTEXT NOT NULL, notification_email VARCHAR(180) DEFAULT NULL, send_notification_email TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_TFD_PW_CLIENT_ID (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tfd_partner_wizard_tool (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, tool_key VARCHAR(80) NOT NULL, name VARCHAR(140) NOT NULL, description LONGTEXT DEFAULT NULL, recommended_for_vehicle_categories JSON NOT NULL, recommended_for_services JSON NOT NULL, level VARCHAR(30) NOT NULL, priority INT NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_TFD_PW_TOOL_CLIENT (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tfd_partner_wizard_package (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, package_key VARCHAR(80) NOT NULL, title VARCHAR(140) NOT NULL, description LONGTEXT NOT NULL, required_status VARCHAR(40) NOT NULL, min_vehicles_per_month INT NOT NULL, max_vehicles_per_month INT NOT NULL, required_experience JSON NOT NULL, required_goals JSON NOT NULL, recommended_vehicle_categories JSON NOT NULL, features JSON NOT NULL, cta_label VARCHAR(120) NOT NULL, priority INT NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_TFD_PW_PACKAGE_CLIENT (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tfd_partner_wizard_lead (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, name VARCHAR(120) NOT NULL, email VARCHAR(180) NOT NULL, company VARCHAR(180) DEFAULT NULL, phone VARCHAR(80) DEFAULT NULL, website VARCHAR(180) DEFAULT NULL, goal VARCHAR(80) DEFAULT NULL, experience VARCHAR(80) DEFAULT NULL, vehicle_categories JSON NOT NULL, main_vehicle_category VARCHAR(80) DEFAULT NULL, current_tool VARCHAR(120) DEFAULT NULL, desired_tool_level VARCHAR(80) DEFAULT NULL, services JSON NOT NULL, monthly_vehicle_count INT NOT NULL, recommendation_status VARCHAR(40) NOT NULL, recommended_package_key VARCHAR(120) DEFAULT NULL, recommended_package_title VARCHAR(180) DEFAULT NULL, recommended_package_text LONGTEXT DEFAULT NULL, recommended_tools JSON NOT NULL, tool_direction LONGTEXT DEFAULT NULL, next_step VARCHAR(120) DEFAULT NULL, estimated_monthly_revenue INT NOT NULL, payload JSON NOT NULL, status VARCHAR(40) NOT NULL, source VARCHAR(80) DEFAULT NULL, origin_domain VARCHAR(120) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_TFD_PW_LEAD_CLIENT (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool ADD CONSTRAINT FK_TFD_PW_TOOL_CLIENT FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_package ADD CONSTRAINT FK_TFD_PW_PACKAGE_CLIENT FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead ADD CONSTRAINT FK_TFD_PW_LEAD_CLIENT FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool DROP FOREIGN KEY FK_TFD_PW_TOOL_CLIENT');
        $this->addSql('ALTER TABLE tfd_partner_wizard_package DROP FOREIGN KEY FK_TFD_PW_PACKAGE_CLIENT');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead DROP FOREIGN KEY FK_TFD_PW_LEAD_CLIENT');
        $this->addSql('DROP TABLE tfd_partner_wizard_lead');
        $this->addSql('DROP TABLE tfd_partner_wizard_package');
        $this->addSql('DROP TABLE tfd_partner_wizard_tool');
        $this->addSql('DROP TABLE tfd_partner_wizard_client');
    }
}
