<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311185000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow storing trip_id on sprints without enforcing FK.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dyno_sprint DROP FOREIGN KEY FK_DYNO_SPRINT_TRIP');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dyno_sprint ADD CONSTRAINT FK_DYNO_SPRINT_TRIP FOREIGN KEY (trip_id) REFERENCES dyno_trip (id) ON DELETE SET NULL');
    }
}
