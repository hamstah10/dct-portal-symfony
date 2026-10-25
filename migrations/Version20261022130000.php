<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional firma to superadmin_calendar_event (NULL = for all companies)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE superadmin_calendar_event ADD firma_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE superadmin_calendar_event ADD INDEX IDX_SUPERADMIN_CALENDAR_EVENT_FIRMA (firma_id)');
        $this->addSql('ALTER TABLE superadmin_calendar_event ADD CONSTRAINT FK_SUPERADMIN_CALENDAR_EVENT_FIRMA FOREIGN KEY (firma_id) REFERENCES firma (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE superadmin_calendar_event DROP FOREIGN KEY FK_SUPERADMIN_CALENDAR_EVENT_FIRMA');
        $this->addSql('ALTER TABLE superadmin_calendar_event DROP INDEX IDX_SUPERADMIN_CALENDAR_EVENT_FIRMA');
        $this->addSql('ALTER TABLE superadmin_calendar_event DROP firma_id');
    }
}
