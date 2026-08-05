<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Dyno CRM entities for CockpitFux API.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dyno_vehicle (
            id VARCHAR(64) NOT NULL,
            name VARCHAR(255) NOT NULL,
            make VARCHAR(120) DEFAULT NULL,
            model VARCHAR(120) DEFAULT NULL,
            year INT DEFAULT NULL,
            stage VARCHAR(60) DEFAULT NULL,
            max_ps INT DEFAULT NULL,
            max_nm INT DEFAULT NULL,
            max_rpm INT DEFAULT NULL,
            idle_rpm INT DEFAULT NULL,
            engine_cc INT DEFAULT NULL,
            cylinders INT DEFAULT NULL,
            fuel_type VARCHAR(40) DEFAULT NULL,
            is_active TINYINT NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql('CREATE TABLE dyno_customer (
            id VARCHAR(36) NOT NULL,
            vehicle_id VARCHAR(64) DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(180) NOT NULL,
            phone VARCHAR(60) DEFAULT NULL,
            notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id),
            KEY IDX_DYNO_CUSTOMER_VEHICLE (vehicle_id),
            CONSTRAINT FK_DYNO_CUSTOMER_VEHICLE FOREIGN KEY (vehicle_id) REFERENCES dyno_vehicle (id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql('CREATE TABLE dyno_trip (
            id VARCHAR(36) NOT NULL,
            vehicle_id VARCHAR(64) DEFAULT NULL,
            vehicle_name VARCHAR(255) DEFAULT NULL,
            start_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            end_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            duration_seconds INT NOT NULL,
            distance_km DECIMAL(10,2) NOT NULL,
            max_speed_kmh DECIMAL(10,2) NOT NULL,
            avg_speed_kmh DECIMAL(10,2) NOT NULL,
            max_rpm INT DEFAULT NULL,
            max_nm INT DEFAULT NULL,
            max_ps INT DEFAULT NULL,
            drive_mode VARCHAR(60) DEFAULT NULL,
            data_points JSON DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id),
            KEY IDX_DYNO_TRIP_VEHICLE (vehicle_id),
            CONSTRAINT FK_DYNO_TRIP_VEHICLE FOREIGN KEY (vehicle_id) REFERENCES dyno_vehicle (id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql('CREATE TABLE dyno_sprint (
            id VARCHAR(36) NOT NULL,
            vehicle_id VARCHAR(64) DEFAULT NULL,
            vehicle_name VARCHAR(255) DEFAULT NULL,
            sprint_type VARCHAR(50) NOT NULL,
            time_seconds DECIMAL(8,2) NOT NULL,
            max_rpm INT DEFAULT NULL,
            max_ps INT DEFAULT NULL,
            max_nm INT DEFAULT NULL,
            drive_mode VARCHAR(60) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id),
            KEY IDX_DYNO_SPRINT_VEHICLE (vehicle_id),
            KEY IDX_DYNO_SPRINT_TYPE (sprint_type),
            CONSTRAINT FK_DYNO_SPRINT_VEHICLE FOREIGN KEY (vehicle_id) REFERENCES dyno_vehicle (id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql('CREATE TABLE dyno_dtc (
            id INT NOT NULL AUTO_INCREMENT,
            code VARCHAR(40) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            payload JSON DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE dyno_dtc');
        $this->addSql('DROP TABLE dyno_sprint');
        $this->addSql('DROP TABLE dyno_trip');
        $this->addSql('DROP TABLE dyno_customer');
        $this->addSql('DROP TABLE dyno_vehicle');
    }
}
