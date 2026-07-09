<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260504120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add website widget configurations for customer vehicle database embeds';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE website_widget_config (id INT AUTO_INCREMENT NOT NULL, kunde_id INT NOT NULL, api_token_id INT NOT NULL, embed_key VARCHAR(120) NOT NULL, active TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, subtitle VARCHAR(255) DEFAULT NULL, button_text VARCHAR(120) NOT NULL, success_title VARCHAR(120) NOT NULL, success_message LONGTEXT DEFAULT NULL, template_variant VARCHAR(50) NOT NULL, primary_color VARCHAR(20) NOT NULL, background_color VARCHAR(20) NOT NULL, text_color VARCHAR(20) NOT NULL, custom_css LONGTEXT DEFAULT NULL, show_phone TINYINT(1) NOT NULL, show_email TINYINT(1) NOT NULL, show_fin_vin TINYINT(1) NOT NULL, show_license_plate TINYINT(1) NOT NULL, show_mileage TINYINT(1) NOT NULL, phone_required TINYINT(1) NOT NULL, email_required TINYINT(1) NOT NULL, privacy_required TINYINT(1) NOT NULL, privacy_url VARCHAR(2048) DEFAULT NULL, privacy_text_html LONGTEXT DEFAULT NULL, redirect_after_submit VARCHAR(2048) DEFAULT NULL, allowed_domains LONGTEXT DEFAULT NULL, send_tracking_events TINYINT(1) NOT NULL, iframe_height INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7D23B3119D4738BC (kunde_id), INDEX IDX_7D23B3117BA2F5EB (api_token_id), INDEX IDX_WEBSITE_WIDGET_ACTIVE (active), UNIQUE INDEX UNIQ_7D23B311A34EAFE (embed_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE website_widget_config ADD CONSTRAINT FK_7D23B3119D4738BC FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE website_widget_config ADD CONSTRAINT FK_7D23B3117BA2F5EB FOREIGN KEY (api_token_id) REFERENCES api_token (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_widget_config DROP FOREIGN KEY FK_7D23B3119D4738BC');
        $this->addSql('ALTER TABLE website_widget_config DROP FOREIGN KEY FK_7D23B3117BA2F5EB');
        $this->addSql('DROP TABLE website_widget_config');
    }
}
