<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704131500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add application_name to vehicle_usage_event to track the originating portal/integration.';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vehicle_usage_event ADD application_name VARCHAR(150) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vehicle_usage_event DROP application_name');
    }
}
