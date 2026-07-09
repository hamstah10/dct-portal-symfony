<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311182000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link dyno sprints to trips.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dyno_sprint ADD trip_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE dyno_sprint ADD INDEX IDX_DYNO_SPRINT_TRIP (trip_id)');
        $this->addSql('ALTER TABLE dyno_sprint ADD CONSTRAINT FK_DYNO_SPRINT_TRIP FOREIGN KEY (trip_id) REFERENCES dyno_trip (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dyno_sprint DROP FOREIGN KEY FK_DYNO_SPRINT_TRIP');
        $this->addSql('ALTER TABLE dyno_sprint DROP INDEX IDX_DYNO_SPRINT_TRIP');
        $this->addSql('ALTER TABLE dyno_sprint DROP trip_id');
    }
}
