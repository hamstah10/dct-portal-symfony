<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260731193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add embeddable chiptuning widget configurations.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE chiptuning_widget_config (id INT AUTO_INCREMENT NOT NULL, kunde_id INT NOT NULL, api_token_id INT NOT NULL, vehicle_data_pool INT DEFAULT NULL, vehicle_type INT DEFAULT NULL, embed_key VARCHAR(120) NOT NULL, active TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, subtitle VARCHAR(255) DEFAULT NULL, template_variant VARCHAR(50) NOT NULL, primary_color VARCHAR(20) NOT NULL, background_color VARCHAR(20) NOT NULL, text_color VARCHAR(20) NOT NULL, select_background_color VARCHAR(20) NOT NULL, select_open_background_color VARCHAR(20) NOT NULL, custom_css LONGTEXT DEFAULT NULL, allowed_domains LONGTEXT DEFAULT NULL, send_tracking_events TINYINT(1) NOT NULL, show_performance_chart TINYINT(1) NOT NULL, type_icon_color VARCHAR(10) NOT NULL, iframe_height INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_CHIPTUNING_WIDGET_EMBED_KEY (embed_key), INDEX IDX_CHIPTUNING_WIDGET_KUNDE (kunde_id), INDEX IDX_CHIPTUNING_WIDGET_API_TOKEN (api_token_id), INDEX IDX_CHIPTUNING_WIDGET_POOL (vehicle_data_pool), INDEX IDX_CHIPTUNING_WIDGET_TYPE (vehicle_type), INDEX IDX_CHIPTUNING_WIDGET_ACTIVE (active), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE chiptuning_widget_config ADD CONSTRAINT FK_CHIPTUNING_WIDGET_KUNDE FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chiptuning_widget_config ADD CONSTRAINT FK_CHIPTUNING_WIDGET_API_TOKEN FOREIGN KEY (api_token_id) REFERENCES api_token (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chiptuning_widget_config ADD CONSTRAINT FK_CHIPTUNING_WIDGET_POOL FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE chiptuning_widget_config ADD CONSTRAINT FK_CHIPTUNING_WIDGET_TYPE FOREIGN KEY (vehicle_type) REFERENCES tx_chiptuningdatabase_vehicletype (uid) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chiptuning_widget_config DROP FOREIGN KEY FK_CHIPTUNING_WIDGET_KUNDE');
        $this->addSql('ALTER TABLE chiptuning_widget_config DROP FOREIGN KEY FK_CHIPTUNING_WIDGET_API_TOKEN');
        $this->addSql('ALTER TABLE chiptuning_widget_config DROP FOREIGN KEY FK_CHIPTUNING_WIDGET_POOL');
        $this->addSql('ALTER TABLE chiptuning_widget_config DROP FOREIGN KEY FK_CHIPTUNING_WIDGET_TYPE');
        $this->addSql('DROP TABLE chiptuning_widget_config');
    }
}
