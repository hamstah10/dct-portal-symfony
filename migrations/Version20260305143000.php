<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds frontend settings for vehicle database rendering controls.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_vehicle_frontend_settings (uid INT AUTO_INCREMENT NOT NULL, show_only_stage1 TINYINT(1) NOT NULL, show_prices TINYINT(1) NOT NULL, template_variant VARCHAR(50) NOT NULL, PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO tx_chiptuningdatabase_vehicle_frontend_settings (show_only_stage1, show_prices, template_variant) VALUES (0, 1, 'performance')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tx_chiptuningdatabase_vehicle_frontend_settings');
    }
}
