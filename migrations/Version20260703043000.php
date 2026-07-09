<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260703043000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Auftrag: add AutoMod/WinOLS job fields (ulid, autoModEnabled, autoModStatus, winolsId, autoModSubmittedAt, autoModFailureReason)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE auftrag ADD COLUMN ulid VARCHAR(26) DEFAULT NULL');
        $this->addSql('ALTER TABLE auftrag ADD COLUMN auto_mod_enabled TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE auftrag ADD COLUMN auto_mod_status INT DEFAULT NULL');
        $this->addSql('ALTER TABLE auftrag ADD COLUMN winols_id VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE auftrag ADD COLUMN auto_mod_submitted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE auftrag ADD COLUMN auto_mod_failure_reason LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E128A358C288C859 ON auftrag (ulid)');
        $this->addSql('CREATE INDEX IDX_AUFTRAG_AUTOMOD_STATUS ON auftrag (auto_mod_enabled, auto_mod_status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_AUFTRAG_AUTOMOD_STATUS ON auftrag');
        $this->addSql('DROP INDEX UNIQ_E128A358C288C859 ON auftrag');
        $this->addSql('ALTER TABLE auftrag DROP COLUMN ulid');
        $this->addSql('ALTER TABLE auftrag DROP COLUMN auto_mod_enabled');
        $this->addSql('ALTER TABLE auftrag DROP COLUMN auto_mod_status');
        $this->addSql('ALTER TABLE auftrag DROP COLUMN winols_id');
        $this->addSql('ALTER TABLE auftrag DROP COLUMN auto_mod_submitted_at');
        $this->addSql('ALTER TABLE auftrag DROP COLUMN auto_mod_failure_reason');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
