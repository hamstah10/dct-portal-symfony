<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Partner Wizard: Terminbuchung (Client-Einstellungen, Termin am Lead)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE tfd_partner_wizard_client
            ADD appointment_enabled TINYINT DEFAULT 0 NOT NULL,
            ADD appointment_required TINYINT DEFAULT 0 NOT NULL,
            ADD appointment_title VARCHAR(120) DEFAULT 'Partner-Beratung' NOT NULL,
            ADD appointment_calendar_id VARCHAR(20) DEFAULT 'support' NOT NULL,
            ADD appointment_duration_minutes INT DEFAULT 30 NOT NULL,
            ADD appointment_buffer_minutes INT DEFAULT 0 NOT NULL,
            ADD appointment_min_notice_hours INT DEFAULT 24 NOT NULL,
            ADD appointment_days_ahead INT DEFAULT 21 NOT NULL,
            ADD appointment_weekdays JSON DEFAULT NULL,
            ADD appointment_day_start VARCHAR(5) DEFAULT '09:00' NOT NULL,
            ADD appointment_day_end VARCHAR(5) DEFAULT '17:00' NOT NULL,
            ADD appointment_timezone VARCHAR(60) DEFAULT 'Europe/Berlin' NOT NULL,
            ADD appointment_location VARCHAR(255) DEFAULT NULL");
        $this->addSql("UPDATE tfd_partner_wizard_client SET appointment_weekdays = '[1,2,3,4,5]'");
        $this->addSql('ALTER TABLE tfd_partner_wizard_client MODIFY appointment_weekdays JSON NOT NULL');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead ADD appointment_starts_at DATETIME DEFAULT NULL, ADD appointment_ends_at DATETIME DEFAULT NULL, ADD appointment_event_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_client DROP appointment_enabled, DROP appointment_required, DROP appointment_title, DROP appointment_calendar_id, DROP appointment_duration_minutes, DROP appointment_buffer_minutes, DROP appointment_min_notice_hours, DROP appointment_days_ahead, DROP appointment_weekdays, DROP appointment_day_start, DROP appointment_day_end, DROP appointment_timezone, DROP appointment_location');
        $this->addSql('ALTER TABLE tfd_partner_wizard_lead DROP appointment_starts_at, DROP appointment_ends_at, DROP appointment_event_id');
    }
}
