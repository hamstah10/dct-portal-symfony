<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Stage-Infos/Leistungen je Getriebemodell (DSG-Optionsmatrix).
 */
final class Version20260713140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tabelle tfd_gearbox_stage_option';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE tfd_gearbox_stage_option (
                id INT AUTO_INCREMENT NOT NULL,
                gearbox_name VARCHAR(100) NOT NULL,
                category VARCHAR(150) NOT NULL,
                category_description LONGTEXT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                name_en VARCHAR(255) NOT NULL,
                fahrbarkeit VARCHAR(100) DEFAULT NULL,
                stage1 VARCHAR(100) DEFAULT NULL,
                stage2 VARCHAR(100) DEFAULT NULL,
                stage2_plus VARCHAR(100) DEFAULT NULL,
                stage3 VARCHAR(100) DEFAULT NULL,
                stage4 VARCHAR(100) DEFAULT NULL,
                sorting INT NOT NULL DEFAULT 0,
                active TINYINT(1) NOT NULL DEFAULT 1,
                INDEX IDX_GEARBOX_STAGE_OPTION_GEARBOX_NAME (gearbox_name),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tfd_gearbox_stage_option');
    }
}
