<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260311160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add distance in meters to dyno sprints.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dyno_sprint ADD distance_m DECIMAL(10,2) NOT NULL DEFAULT 0.00');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dyno_sprint DROP distance_m');
    }
}
