<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add superadmin_calendar_event table for general operations calendar appointments';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE superadmin_calendar_event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(190) NOT NULL, description LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, guests JSON NOT NULL, calendar_id VARCHAR(20) NOT NULL, color VARCHAR(20) DEFAULT NULL, all_day TINYINT(1) NOT NULL, starts_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ends_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_SUPERADMIN_CALENDAR_EVENT_STARTS (starts_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE superadmin_calendar_event');
    }
}
