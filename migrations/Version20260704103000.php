<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add superadmin operations calendar entries';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE superadmin_calendar_entry (id INT AUTO_INCREMENT NOT NULL, firma_id INT DEFAULT NULL, kunde_id INT DEFAULT NULL, customer_type VARCHAR(20) NOT NULL, new_customer_name VARCHAR(190) DEFAULT NULL, new_customer_email VARCHAR(190) DEFAULT NULL, reason VARCHAR(255) NOT NULL, starts_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ends_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_SUPERADMIN_CALENDAR_FIRMA (firma_id), INDEX IDX_SUPERADMIN_CALENDAR_KUNDE (kunde_id), INDEX IDX_SUPERADMIN_CALENDAR_STARTS_AT (starts_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE superadmin_calendar_entry ADD CONSTRAINT FK_SUPERADMIN_CALENDAR_FIRMA FOREIGN KEY (firma_id) REFERENCES firma (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE superadmin_calendar_entry ADD CONSTRAINT FK_SUPERADMIN_CALENDAR_KUNDE FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE superadmin_calendar_entry DROP FOREIGN KEY FK_SUPERADMIN_CALENDAR_FIRMA');
        $this->addSql('ALTER TABLE superadmin_calendar_entry DROP FOREIGN KEY FK_SUPERADMIN_CALENDAR_KUNDE');
        $this->addSql('DROP TABLE superadmin_calendar_entry');
    }
}
