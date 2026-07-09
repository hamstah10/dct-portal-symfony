<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260307224500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create vehicle usage event table for Fahrzeugdatenbank tracking statistics.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vehicle_usage_event (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', event_type VARCHAR(120) NOT NULL, event_source VARCHAR(40) NOT NULL, route_name VARCHAR(190) DEFAULT NULL, request_uri VARCHAR(500) DEFAULT NULL, http_method VARCHAR(10) NOT NULL, ip_address VARCHAR(64) DEFAULT NULL, user_agent VARCHAR(500) DEFAULT NULL, referrer VARCHAR(500) DEFAULT NULL, session_id VARCHAR(190) DEFAULT NULL, vehicle_type_id INT DEFAULT NULL, brand_id INT DEFAULT NULL, series_id INT DEFAULT NULL, model_id INT DEFAULT NULL, engine_id INT DEFAULT NULL, stage_id INT DEFAULT NULL, payload JSON DEFAULT NULL, INDEX IDX_VEH_USAGE_CREATED_AT (created_at), INDEX IDX_VEH_USAGE_EVENT_TYPE (event_type), INDEX IDX_VEH_USAGE_ENGINE_ID (engine_id), INDEX IDX_VEH_USAGE_STAGE_ID (stage_id), INDEX IDX_VEH_USAGE_SESSION_ID (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vehicle_usage_event');
    }
}
