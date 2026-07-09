<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add detected_ecu and ecu_detection_pattern tables for ECU Intelligence step 2 (analysis/detection of ECUs, MCUs, methods and vehicle hints from tool news items).';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE detected_ecu (
                id INT AUTO_INCREMENT NOT NULL,
                news_item_id INT NOT NULL,
                provider_id INT DEFAULT NULL,
                manufacturer VARCHAR(100) DEFAULT NULL,
                ecu_family VARCHAR(50) DEFAULT NULL,
                ecu_name VARCHAR(100) DEFAULT NULL,
                mcu VARCHAR(50) DEFAULT NULL,
                read_method VARCHAR(50) DEFAULT NULL,
                protocol VARCHAR(50) DEFAULT NULL,
                vehicle_hint VARCHAR(150) DEFAULT NULL,
                confidence DOUBLE PRECISION NOT NULL,
                raw_text LONGTEXT DEFAULT NULL,
                match_data JSON DEFAULT NULL,
                status VARCHAR(20) NOT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME DEFAULT NULL,
                INDEX IDX_DETECTED_ECU_NEWS_ITEM (news_item_id),
                INDEX IDX_DETECTED_ECU_PROVIDER (provider_id),
                INDEX IDX_DETECTED_ECU_STATUS (status),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE ecu_detection_pattern (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(150) NOT NULL,
                type VARCHAR(20) NOT NULL,
                pattern VARCHAR(255) NOT NULL,
                is_regex TINYINT(1) NOT NULL,
                normalized_value VARCHAR(150) DEFAULT NULL,
                score_weight DOUBLE PRECISION NOT NULL,
                is_active TINYINT(1) NOT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME DEFAULT NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $this->addSql('ALTER TABLE detected_ecu ADD CONSTRAINT FK_DETECTED_ECU_NEWS_ITEM FOREIGN KEY (news_item_id) REFERENCES tool_news_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE detected_ecu ADD CONSTRAINT FK_DETECTED_ECU_PROVIDER FOREIGN KEY (provider_id) REFERENCES tool_provider (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE detected_ecu DROP FOREIGN KEY FK_DETECTED_ECU_NEWS_ITEM');
        $this->addSql('ALTER TABLE detected_ecu DROP FOREIGN KEY FK_DETECTED_ECU_PROVIDER');
        $this->addSql('DROP TABLE detected_ecu');
        $this->addSql('DROP TABLE ecu_detection_pattern');
    }
}
