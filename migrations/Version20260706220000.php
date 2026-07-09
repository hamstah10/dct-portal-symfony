<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260706220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vehicle_coverage_match and vehicle_platform_alias tables for ECU Intelligence step 3 (matching detected ECUs against the existing vehicle database).';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE vehicle_coverage_match (
                id INT AUTO_INCREMENT NOT NULL,
                detected_ecu_id INT NOT NULL,
                news_item_id INT DEFAULT NULL,
                provider_id INT DEFAULT NULL,
                vehicle_type VARCHAR(50) DEFAULT NULL,
                vehicle_brand VARCHAR(150) DEFAULT NULL,
                vehicle_series VARCHAR(150) DEFAULT NULL,
                vehicle_model VARCHAR(150) DEFAULT NULL,
                vehicle_engine VARCHAR(150) DEFAULT NULL,
                vehicle_engine_id INT DEFAULT NULL,
                vehicle_model_id INT DEFAULT NULL,
                vehicle_series_id INT DEFAULT NULL,
                vehicle_brand_id INT DEFAULT NULL,
                engine_code VARCHAR(255) DEFAULT NULL,
                ecu_name VARCHAR(100) DEFAULT NULL,
                ecu_family VARCHAR(50) DEFAULT NULL,
                mcu VARCHAR(50) DEFAULT NULL,
                fuel VARCHAR(50) DEFAULT NULL,
                hp INT DEFAULT NULL,
                kw INT DEFAULT NULL,
                nm INT DEFAULT NULL,
                year_from INT DEFAULT NULL,
                year_to INT DEFAULT NULL,
                match_score DOUBLE PRECISION NOT NULL,
                match_level VARCHAR(20) NOT NULL,
                match_reason LONGTEXT DEFAULT NULL,
                match_data JSON DEFAULT NULL,
                status VARCHAR(20) NOT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME DEFAULT NULL,
                INDEX IDX_VEHICLE_COVERAGE_MATCH_DETECTED_ECU (detected_ecu_id),
                INDEX IDX_VEHICLE_COVERAGE_MATCH_NEWS_ITEM (news_item_id),
                INDEX IDX_VEHICLE_COVERAGE_MATCH_PROVIDER (provider_id),
                INDEX IDX_VEHICLE_COVERAGE_MATCH_STATUS (status),
                INDEX IDX_VEHICLE_COVERAGE_MATCH_LEVEL (match_level),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE vehicle_platform_alias (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(150) NOT NULL,
                source_brand VARCHAR(150) DEFAULT NULL,
                source_model VARCHAR(150) DEFAULT NULL,
                alias_brand VARCHAR(150) NOT NULL,
                alias_model VARCHAR(150) DEFAULT NULL,
                score_weight DOUBLE PRECISION NOT NULL,
                is_active TINYINT(1) NOT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME DEFAULT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $this->addSql('ALTER TABLE vehicle_coverage_match ADD CONSTRAINT FK_VEHICLE_COVERAGE_MATCH_DETECTED_ECU FOREIGN KEY (detected_ecu_id) REFERENCES detected_ecu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicle_coverage_match ADD CONSTRAINT FK_VEHICLE_COVERAGE_MATCH_NEWS_ITEM FOREIGN KEY (news_item_id) REFERENCES tool_news_item (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE vehicle_coverage_match ADD CONSTRAINT FK_VEHICLE_COVERAGE_MATCH_PROVIDER FOREIGN KEY (provider_id) REFERENCES tool_provider (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vehicle_coverage_match DROP FOREIGN KEY FK_VEHICLE_COVERAGE_MATCH_DETECTED_ECU');
        $this->addSql('ALTER TABLE vehicle_coverage_match DROP FOREIGN KEY FK_VEHICLE_COVERAGE_MATCH_NEWS_ITEM');
        $this->addSql('ALTER TABLE vehicle_coverage_match DROP FOREIGN KEY FK_VEHICLE_COVERAGE_MATCH_PROVIDER');
        $this->addSql('DROP TABLE vehicle_coverage_match');
        $this->addSql('DROP TABLE vehicle_platform_alias');
    }
}
