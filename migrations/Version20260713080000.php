<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Gepinnter Fahrzeugtyp für den Superadmin-Konfigurator (Start direkt bei Marken).
 */
final class Version20260713080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tabelle superadmin_pinned_vehicle_type';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE superadmin_pinned_vehicle_type (
                id INT AUTO_INCREMENT NOT NULL,
                type_id INT NOT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE superadmin_pinned_vehicle_type');
    }
}
