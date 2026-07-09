<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260321143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend portal table with type flags and enabled modules';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal ADD portal_type VARCHAR(40) NOT NULL DEFAULT \'brand_suite\', ADD portal_enabled TINYINT(1) NOT NULL DEFAULT 1, ADD website_enabled TINYINT(1) NOT NULL DEFAULT 1, ADD customer_registration_enabled TINYINT(1) NOT NULL DEFAULT 1, ADD enabled_modules JSON NOT NULL');
        $this->addSql('UPDATE portal SET enabled_modules = :modules', [
            'modules' => json_encode(['dashboard', 'profile', 'uploads', 'vehicle_database', 'registration', 'public_website'], JSON_THROW_ON_ERROR),
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal DROP portal_type, DROP portal_enabled, DROP website_enabled, DROP customer_registration_enabled, DROP enabled_modules');
    }
}
