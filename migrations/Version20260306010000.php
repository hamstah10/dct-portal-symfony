<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add GLB model upload and media display mode to vehicle engine.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE tx_chiptuningdatabase_vehicleengine ADD glb_model VARCHAR(255) DEFAULT NULL, ADD media_display_mode VARCHAR(20) NOT NULL DEFAULT 'image'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicleengine DROP glb_model, DROP media_display_mode');
    }
}
