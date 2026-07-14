<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Key-Value-Einstellungen für das Superadmin-Portal.
 */
final class Version20260714110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tabelle superadmin_setting';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE superadmin_setting (
                id INT AUTO_INCREMENT NOT NULL,
                setting_key VARCHAR(100) NOT NULL,
                setting_value JSON DEFAULT NULL,
                UNIQUE INDEX UNIQ_SUPERADMIN_SETTING_KEY (setting_key),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE superadmin_setting');
    }
}
